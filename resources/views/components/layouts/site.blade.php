@props([
    'title' => null,
    'description' => null,
    'overHero' => false,
    'image' => null,       // App\Models\Media|null — paylaşım görseli (yoksa ana sayfa kapağı)
    'type' => 'website',   // og:type: website | article
    'noindex' => false,    // ör. filtrelenmiş liste, ÖRNEK ilan
    'schema' => [],        // ek JSON-LD dizileri (App\Support\StructuredData)
])

@use('App\Support\StructuredData')

@php
    $brand = settings('site.brand') ?: config('gab.brand');
    $pageTitle = $title ? "{$title} — {$brand}" : $brand;
    $description = \Illuminate\Support\Str::limit(trim(strip_tags((string) ($description ?: settings('site.description')))), 300);

    // Kanonik adres: sorgu parametreleri atılır, yalnızca sayfalama korunur (?page=2)
    $page = (int) request()->query('page', 1);
    $canonical = url()->current().($page > 1 ? '?page='.$page : '');

    // Yalnızca production'da dizine açık (config/seo.php)
    $robots = ($noindex || ! config('seo.indexable')) ? 'noindex, follow' : 'index, follow, max-image-preview:large';

    $share = $image?->shareImage() ?? $defaultShareImage;
@endphp

<!DOCTYPE html>
<html lang="tr" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
    @if ($description)
        <meta name="description" content="{{ $description }}">
    @endif
    <meta name="robots" content="{{ $robots }}">
    <link rel="canonical" href="{{ $canonical }}">
    {{-- Tek dil (TD-003): hreflang yalnızca Türkçe sürümü ve varsayılanı gösterir --}}
    <link rel="alternate" hreflang="tr" href="{{ $canonical }}">
    <link rel="alternate" hreflang="x-default" href="{{ $canonical }}">
    <meta name="theme-color" content="#111111" media="(prefers-color-scheme: dark)">
    <meta name="theme-color" content="#f5f4f0" media="(prefers-color-scheme: light)">

    {{-- Open Graph / Twitter --}}
    <meta property="og:site_name" content="{{ $brand }}">
    <meta property="og:locale" content="tr_TR">
    <meta property="og:type" content="{{ $type }}">
    <meta property="og:title" content="{{ $title ?: $brand }}">
    @if ($description)
        <meta property="og:description" content="{{ $description }}">
    @endif
    <meta property="og:url" content="{{ $canonical }}">
    @if ($share)
        <meta property="og:image" content="{{ url($share['url']) }}">
        <meta property="og:image:type" content="image/webp">
        <meta property="og:image:width" content="{{ $share['width'] }}">
        @if ($share['height'])
            <meta property="og:image:height" content="{{ $share['height'] }}">
        @endif
        @if ($share['alt'])
            <meta property="og:image:alt" content="{{ $share['alt'] }}">
        @endif
    @endif
    <meta name="twitter:card" content="{{ $share ? 'summary_large_image' : 'summary' }}">
    @if (config('seo.twitter_site'))
        <meta name="twitter:site" content="{{ config('seo.twitter_site') }}">
    @endif

    {{-- Tema: render öncesi uygulanır (FOUC yok). Varsayılan: dark (TD-010). --}}
    <script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
        (function () {
            var d = document.documentElement, t = null;
            try { t = localStorage.getItem('gab-theme'); } catch (e) {}
            d.dataset.theme = (t === 'light' || t === 'dark') ? t : 'dark';
            d.classList.add('js');
        })();
    </script>

    {{ $head ?? '' }}

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Yapısal veri: sayfanın kendi şeması + breadcrumb (x-site.breadcrumbs push eder) --}}
    @foreach ($schema as $graph)
        {{ StructuredData::script($graph) }}
    @endforeach
    @stack('structured-data')
</head>
<body class="min-h-screen bg-bg text-fg">
    <a href="#main"
       class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:bg-fg focus:px-4 focus:py-2 focus:text-bg">
        İçeriğe geç
    </a>

    <x-site.header :over-hero="$overHero" />

    <main id="main">
        {{ $slot }}
    </main>

    <x-site.footer />

    <x-site.cookie-notice />
</body>
</html>
