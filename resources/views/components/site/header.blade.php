@props(['overHero' => false])

@use('App\Models\MenuItem')

@php
    // Üst öğe aktif mi? (kendi adresi veya alt öğelerinden biri)
    $isActive = fn (array $item) => ($item['url'] && MenuItem::isActiveUrl($item['url']))
        || collect($item['children'])->contains(fn ($c) => $c['url'] && MenuItem::isActiveUrl($c['url']));
@endphp

{{--
    overHero: sayfa tam ekran görselle başlıyorsa header, scroll edilene kadar
    şeffaf ve açık renk metinlidir (temadan bağımsız).
--}}
<header x-data="siteHeader"
        @keydown.escape.window="closeMenu()"
        class="site-header"
        @if ($overHero) data-over-hero @endif
        :data-scrolled="scrolled"
        :data-hidden="hidden"
        :data-menu="menuOpen">
    <div class="container-gab flex h-20 items-center justify-between gap-6 lg:h-24">
        <x-site.logo class="relative z-10" />

        {{-- Masaüstü menü --}}
        <nav aria-label="Ana menü" class="hidden lg:block">
            <ul class="flex items-center gap-7 xl:gap-9">
                @foreach ($navigation as $item)
                    @php
                        $active = $isActive($item);
                    @endphp
                    <li class="relative"
                        @if ($item['children'])
                            x-data="{ open: false }"
                            @mouseenter="open = true"
                            @mouseleave="open = false"
                            @click.outside="open = false"
                            @keydown.escape.stop="open = false; $refs.trigger.focus()"
                            @focusout="if (!$el.contains($event.relatedTarget)) open = false"
                        @endif>
                        @if ($item['url'])
                            <a href="{{ MenuItem::hrefFor($item['url']) }}"
                               @if ($active) aria-current="page" @endif
                               @if ($item['new_tab']) target="_blank" rel="noopener" @endif
                               class="group relative inline-flex items-center gap-1.5 py-2 text-[0.8125rem] font-medium uppercase tracking-[0.14em]">
                                {{ $item['label'] }}
                                <span @class([
                                    'absolute inset-x-0 -bottom-0.5 h-px origin-left bg-current transition-transform duration-500 ease-[var(--ease-architect)]',
                                    'scale-x-100' => $active,
                                    'scale-x-0 group-hover:scale-x-100' => ! $active,
                                ])></span>
                            </a>
                        @else
                            {{-- Bağlantısız üst öğe: açılır menü düğmesi --}}
                            {{-- Fare tıklaması (detail ≥ 1) hover ile açılmış menüyü kapatmaz; klavye (Enter/Space, detail = 0) aç/kapa yapar --}}
                            <button type="button" x-ref="trigger"
                                    @click="open = $event.detail === 0 ? !open : true"
                                    :aria-expanded="open.toString()"
                                    aria-haspopup="true"
                                    class="group relative inline-flex items-center gap-1.5 py-2 text-[0.8125rem] font-medium uppercase tracking-[0.14em]">
                                {{ $item['label'] }}
                                <svg class="size-3 transition-transform duration-300" :class="open && 'rotate-180'" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.25" aria-hidden="true"><path d="m2.5 4.5 3.5 3.5 3.5-3.5" /></svg>
                                <span @class([
                                    'absolute inset-x-0 -bottom-0.5 h-px origin-left bg-current transition-transform duration-500 ease-[var(--ease-architect)]',
                                    'scale-x-100' => $active,
                                    'scale-x-0 group-hover:scale-x-100' => ! $active,
                                ])></span>
                            </button>
                        @endif

                        @if ($item['children'])
                            <div x-show="open" x-cloak
                                 x-transition:enter="transition duration-200 ease-out"
                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                 x-transition:leave="transition duration-150"
                                 x-transition:leave-end="opacity-0"
                                 class="absolute left-1/2 top-full z-20 -translate-x-1/2 pt-4">
                                <ul class="min-w-56 border border-line bg-bg py-3 text-fg shadow-[0_24px_48px_-24px_rgba(0,0,0,0.45)]">
                                    @foreach ($item['children'] as $child)
                                        <li>
                                            <a href="{{ MenuItem::hrefFor($child['url']) }}"
                                               @if (MenuItem::isActiveUrl($child['url'])) aria-current="page" @endif
                                               @if ($child['new_tab']) target="_blank" rel="noopener" @endif
                                               class="flex items-center justify-between gap-6 px-5 py-2.5 text-[0.9375rem] transition-colors hover:text-accent aria-[current=page]:text-accent">
                                                {{ $child['label'] }}
                                                <svg class="size-3.5 opacity-50" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.25" aria-hidden="true"><path d="M1 8h13M9.5 3.5 14 8l-4.5 4.5" /></svg>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="relative z-10 flex items-center gap-3">
            <x-site.theme-toggle />

            <button type="button"
                    x-ref="menuButton"
                    @click="menuOpen ? closeMenu() : openMenu()"
                    :aria-expanded="menuOpen.toString()"
                    aria-controls="mobile-menu"
                    class="inline-flex size-10 items-center justify-center lg:hidden">
                <span class="sr-only" x-text="menuOpen ? 'Menüyü kapat' : 'Menüyü aç'">Menüyü aç</span>
                <span class="relative block h-3 w-6" aria-hidden="true">
                    <span class="absolute left-0 top-0 h-px w-6 bg-current transition-transform duration-300"
                          :class="menuOpen && 'translate-y-1.5 rotate-45'"></span>
                    <span class="absolute bottom-0 left-0 h-px w-6 bg-current transition-transform duration-300"
                          :class="menuOpen && '-translate-y-1.5 -rotate-45'"></span>
                </span>
            </button>
        </div>
    </div>

    {{-- Mobil menü (tam ekran) --}}
    <div id="mobile-menu"
         x-show="menuOpen"
         x-cloak
         x-transition:enter="transition-opacity duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:leave="transition-opacity duration-200"
         x-transition:leave-end="opacity-0"
         @keydown.tab="
            const f = [...$el.querySelectorAll('a')], first = f[0], last = f[f.length - 1];
            if ($event.shiftKey && document.activeElement === first) { $event.preventDefault(); $refs.menuButton.focus(); }
            else if (!$event.shiftKey && document.activeElement === last) { $event.preventDefault(); $refs.menuButton.focus(); }
         "
         class="fixed inset-0 -z-0 flex flex-col overflow-y-auto bg-bg pt-24 text-fg lg:hidden"
         role="dialog" aria-modal="true" aria-label="Menü">
        <nav aria-label="Mobil menü" class="container-gab flex-1">
            <ul class="border-t border-line">
                @foreach ($navigation as $item)
                    <li class="border-b border-line">
                        @if ($item['url'])
                            <a href="{{ MenuItem::hrefFor($item['url']) }}"
                               @if ($loop->first) x-ref="menuFirst" @endif
                               @if (MenuItem::isActiveUrl($item['url'])) aria-current="page" @endif
                               class="flex items-baseline justify-between py-4 font-serif text-[2rem] leading-none">
                                {{ $item['label'] }}
                                <span class="eyebrow">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            </a>
                        @else
                            <p class="flex items-baseline justify-between pt-4 pb-2 font-serif text-[2rem] leading-none">
                                {{ $item['label'] }}
                                <span class="eyebrow">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            </p>
                        @endif
                        @if ($item['children'])
                            <ul class="flex flex-wrap gap-x-6 gap-y-1 pb-4">
                                @foreach ($item['children'] as $child)
                                    <li><a href="{{ MenuItem::hrefFor($child['url']) }}" class="inline-flex items-center gap-2 py-1.5 text-[1rem] text-muted aria-[current=page]:text-fg" @if (MenuItem::isActiveUrl($child['url'])) aria-current="page" @endif>
                                        <span class="h-px w-3 bg-line" aria-hidden="true"></span>{{ $child['label'] }}
                                    </a></li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
        </nav>
        <div class="container-gab py-8 text-sm text-muted">
            <p class="whitespace-pre-line">{{ settings('contact.address') }}</p>
            <ul class="mt-4 flex gap-6">
                @foreach (settings('social.links', []) as $social)
                    <li><a href="{{ $social['url'] }}" target="_blank" rel="noopener" class="text-fg">{{ $social['label'] }}</a></li>
                @endforeach
            </ul>
        </div>
    </div>
</header>
