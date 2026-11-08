@props([
    'code',
    'title',
    'message',
    'actions' => true,   // 503 (bakım) sayfasında bağlantı göstermemek için false
])

{{--
    Hata sayfası düzeni (403, 419, 429, 500, 503).
    Bilerek bağımsız: veritabanı, menü, ayar OKUMAZ — veritabanı çökmüşken de açılır.
    Sistem ayrıntısı (hata mesajı, dosya yolu, sürüm) asla gösterilmez (brief §10).
--}}
<!DOCTYPE html>
<html lang="tr" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title }} — {{ config('gab.brand') }}</title>
    <script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
        (function () {
            var d = document.documentElement, t = null;
            try { t = localStorage.getItem('gab-theme'); } catch (e) {}
            d.dataset.theme = (t === 'light' || t === 'dark') ? t : 'dark';
        })();
    </script>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-bg text-fg">
    <header class="container-gab flex h-24 items-center">
        <x-site.logo />
    </header>

    <main id="main" class="plan-lines flex min-h-[calc(100svh-6rem)] items-center pb-20">
        <div class="container-gab relative">
            <div class="grid-gab gap-y-10">
                <p class="col-span-4 font-serif text-[clamp(7rem,22vw,20rem)] leading-[0.8] text-accent lg:col-span-6" aria-hidden="true">{{ $code }}</p>
                <div class="col-span-4 lg:col-span-5 lg:col-start-8 lg:self-end">
                    <p class="eyebrow">Hata {{ $code }}</p>
                    <h1 class="mt-8 text-h2">{{ $title }}</h1>
                    <p class="mt-6 text-muted">{{ $message }}</p>
                    {{ $slot }}
                    @if ($actions)
                        <div class="mt-10 flex flex-wrap gap-4">
                            <x-site.button href="{{ url('/') }}">Ana sayfa</x-site.button>
                            <x-site.button href="{{ url('/iletisim') }}" variant="outline">İletişim</x-site.button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>
</body>
</html>
