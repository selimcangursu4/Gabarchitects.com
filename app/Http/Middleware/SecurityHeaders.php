<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Güvenlik başlıkları (brief §10, TD-054).
 *
 * - Tüm yanıtlar: nosniff, çerçeveleme yalnızca aynı site, referrer, izin politikası, COOP.
 * - HTTPS + production: HSTS (1 yıl).
 * - Genel site: Content-Security-Policy (satır içi script yalnızca nonce ile).
 *   Alpine.js ifadeleri için 'unsafe-eval' gerekir; satır içi style özniteliği (--reveal-delay) için 'unsafe-inline' (yalnızca style).
 * - Panel (/admin, /livewire): CSP yok (Filament/Livewire satır içi script kullanır), arama motorlarına kapalı.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $isPanel = $request->is('admin', 'admin/*', 'livewire*', 'filament/*');

        if (! $isPanel) {
            Vite::useCspNonce();   // @vite etiketleri ve layout'taki satır içi script aynı nonce'u kullanır
        }

        $response = $next($request);
        $headers = $response->headers;

        $headers->set('X-Content-Type-Options', 'nosniff');
        $headers->set('X-Frame-Options', 'SAMEORIGIN');
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');
        $headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        // PHP sürümünü sızdırmasın: başlığı PHP SAPI ekler (Symfony başlıklarında görünmez)
        $headers->remove('X-Powered-By');
        if (! headers_sent()) {
            header_remove('X-Powered-By');
        }

        if ($request->isSecure() && app()->isProduction()) {
            // includeSubDomains yok: alt alan adlarında (ör. e-posta paneli) HTTPS garanti değil
            $headers->set('Strict-Transport-Security', 'max-age=31536000');
        }

        if ($isPanel) {
            $headers->set('X-Robots-Tag', 'noindex, nofollow');
        } elseif (! $headers->has('Content-Security-Policy')) {
            $headers->set('Content-Security-Policy', $this->contentSecurityPolicy());
        }

        return $response;
    }

    private function contentSecurityPolicy(): string
    {
        // Geliştirmede `npm run dev` açıksa Vite sunucusuna izin ver
        $dev = Vite::isRunningHot() ? ' '.rtrim((string) file_get_contents(public_path('hot'))) : '';
        $devSocket = $dev ? ' '.preg_replace('#^http#', 'ws', trim($dev)) : '';

        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'nonce-".Vite::cspNonce()."' 'unsafe-eval'".$dev,
            "style-src 'self' 'unsafe-inline'".$dev,
            "img-src 'self' data:",
            "font-src 'self'".$dev,
            "connect-src 'self'".$dev.$devSocket,
            "frame-src 'none'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
        ]);
    }
}
