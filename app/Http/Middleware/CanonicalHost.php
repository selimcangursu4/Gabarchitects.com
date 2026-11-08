<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tek adres (TD-062): APP_URL'deki alan adı esas alınır.
 * - www.alan-adi → https://alan-adi (301)
 * - http://alan-adi → https://alan-adi (301, APP_URL https ise)
 * Başka host'lara (ör. sunucu sağlık kontrolü, geçici adresler) dokunmaz; bu yüzden yönlendirme döngüsü oluşmaz.
 * Yalnızca GET/HEAD yönlendirilir (form gövdesi kaybolmasın).
 */
class CanonicalHost
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('app.canonical_redirect') || ! $request->isMethodSafe()) {
            return $next($request);
        }

        $target = parse_url((string) config('app.url'));
        $host = strtolower($target['host'] ?? '');

        if ($host === '' || str_starts_with($host, 'www.')) {
            return $next($request);
        }

        $scheme = ($target['scheme'] ?? 'https') === 'https' ? 'https' : 'http';
        $requestHost = strtolower($request->getHost());

        $isWww = $requestHost === 'www.'.$host;
        $needsHttps = $requestHost === $host && $scheme === 'https' && ! $request->isSecure();

        if ($isWww || $needsHttps) {
            return redirect()->away($scheme.'://'.$host.$request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
