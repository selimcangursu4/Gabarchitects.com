<?php

use App\Http\Middleware\EnsureModuleEnabled;
use App\Http\Middleware\HandleRedirects;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
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
