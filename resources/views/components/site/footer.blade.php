@php
    $phone = settings('contact.phone');
    $email = settings('contact.email');
@endphp

<footer class="border-t border-line bg-surface">
    <div class="container-gab pt-20 pb-10 lg:pt-28">
        <div class="grid-gab gap-y-14">
            <div class="col-span-4 lg:col-span-5">
                <x-site.logo />
                <p class="mt-6 max-w-sm text-muted">{{ settings('site.tagline') }}. Mimarlık, iç mimarlık, proje geliştirme ve inşaat hizmetleri.</p>
            </div>

            <nav aria-label="Alt menü" class="col-span-2 lg:col-span-2">
                <h2 class="eyebrow">Menü</h2>
                <ul class="mt-6 space-y-3 text-[0.9375rem]">
                    {{-- Bağlantısız üst öğeler (Kurumsal, Portföylerimiz) yerine alt öğeleri listelenir --}}
                    @foreach (collect($navigation)->flatMap(fn ($i) => $i['url'] ? [$i] : $i['children']) as $item)
                        <li><a href="{{ \App\Models\MenuItem::hrefFor($item['url']) }}" class="transition-colors hover:text-accent">{{ $item['label'] }}</a></li>
                    @endforeach
                </ul>
            </nav>

            <div class="col-span-2 lg:col-span-2">
                <h2 class="eyebrow">Hizmetler</h2>
                <ul class="mt-6 space-y-3 text-[0.9375rem]">
                    @foreach ($footerServices as $service)
                        <li><a href="{{ url('/hizmetler/'.$service['slug']) }}" class="transition-colors hover:text-accent">{{ $service['title'] }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div class="col-span-4 lg:col-span-3">
                <h2 class="eyebrow">İletişim</h2>
                <address class="mt-6 space-y-3 text-[0.9375rem] not-italic">
                    <p class="whitespace-pre-line text-muted">{{ settings('contact.address') }}</p>
                    @if ($phone)
                        <p><a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}" class="hover:text-accent">{{ $phone }}</a></p>
                    @endif
                    @if ($email)
                        <p><a href="mailto:{{ $email }}" class="hover:text-accent">{{ $email }}</a></p>
                    @endif
                    @if (settings('contact.map_url'))
                        <p><a href="{{ settings('contact.map_url') }}" target="_blank" rel="noopener" class="underline decoration-line underline-offset-4 hover:decoration-accent">Haritada aç</a></p>
                    @endif
                </address>
                <ul class="mt-6 flex gap-6 text-[0.9375rem]">
                    @foreach (settings('social.links', []) as $social)
                        <li><a href="{{ $social['url'] }}" target="_blank" rel="noopener" class="transition-colors hover:text-accent">{{ $social['label'] }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="mt-20 flex flex-col gap-4 border-t border-line pt-8 text-[0.8125rem] text-subtle lg:flex-row lg:items-center lg:justify-between">
            <p>© {{ now()->year }} {{ settings('site.brand') }}. Tüm hakları saklıdır.</p>
            <ul class="flex flex-wrap gap-x-6 gap-y-2">
                @foreach ($legalNavigation as $item)
                    <li><a href="{{ \App\Models\MenuItem::hrefFor($item['url']) }}" class="hover:text-fg">{{ $item['label'] }}</a></li>
                @endforeach
                <li><a href="#main" class="hover:text-fg">Başa dön ↑</a></li>
            </ul>
        </div>
    </div>
</footer>
