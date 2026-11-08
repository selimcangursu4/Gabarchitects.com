<?php

use App\Http\Middleware\CanonicalHost;
use App\Http\Middleware\EnsureModuleEnabled;
use App\Http\Middleware\HandleRedirects;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // www / http → APP_URL (TD-062). Proxy güveni bundan önce çalışır (isSecure X-Forwarded-Proto'yu görür).
        // Eski adres yönlendirmelerinden önce: tek adımda doğru alan adına gidilir.
        $middleware->append(CanonicalHost::class);
        // Global: eşleşmeyen route'lar (404) için de çalışmalı.
        $middleware->append(HandleRedirects::class);
        // En dışta: bakım (503) ve hata sayfaları (404/500) dahil her yanıtta güvenlik başlıkları + CSP nonce
        $middleware->prepend(SecurityHeaders::class);

        // Yalnızca TRUSTED_PROXIES tanımlıysa (Railway gibi HTTPS'i proxy'de sonlandıran ortamlar): "*" veya virgüllü IP'ler.
        // Turhost'ta (doğrudan Apache) TANIMLANMAZ: aksi halde ziyaretçi X-Forwarded-For ile IP taklit edip
        // form / giriş deneme sınırlarını atlatabilir. Not: config yüklenmeden çalışır → gerçek ortam değişkeni olmalı.
        if ($proxies = env('TRUSTED_PROXIES')) {
            $middleware->trustProxies(at: $proxies === '*' ? '*' : array_map('trim', explode(',', $proxies)));
        }

        $middleware->alias([
            'module' => EnsureModuleEnabled::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();

// Turhost paylaşımlı hosting (Senaryo B, TD-063): uygulama ~/gab, web kökü ~/public_html.
// ~/gab/.public_path dosyası web kökünün yolunu tutar (ör. "../public_html"); config yüklenmeden okunur ki
// görsel diski (public_path('media')), Vite manifest'i ve artisan komutları aynı klasörü kullansın.
// Dosya yoksa (lokal, Railway, testler) Laravel'in varsayılan public/ klasörü kullanılır.
if (is_file($marker = dirname(__DIR__).'/.public_path')) {
    $configured = trim((string) file_get_contents($marker));
    $publicPath = $configured === '' ? false : realpath(
        preg_match('#^(/|[A-Za-z]:[\\\\/])#', $configured) ? $configured : dirname(__DIR__).'/'.$configured
    );

    if ($publicPath === false || ! is_file($publicPath.'/index.php')) {
        throw new RuntimeException('.public_path geçersiz: web kökü bulunamadı.');
    }

    $app->usePublicPath($publicPath);
}

return $app;
