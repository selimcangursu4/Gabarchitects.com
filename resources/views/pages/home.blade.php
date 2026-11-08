@use('App\Support\StructuredData')
<x-layouts.site
    :over-hero="true"
    :title="$page->meta_title"
    :description="$page->meta_description"
    :image="$page->cover"
    :schema="[StructuredData::organization(), StructuredData::website()]">

    @php
        // Sayfaya özel alanlar (CMS: pages.content JSON)
        $home = $page->content;
        $heroImage = $page->cover;
        $heroFormat = $heroImage?->hasFormat('avif') ? 'avif' : 'webp';
    @endphp

    @if ($heroImage)
        <x-slot:head>
            <link rel="preload" as="image" type="image/{{ $heroFormat }}" fetchpriority="high"
                  imagesrcset="{{ $heroImage->srcset($heroFormat) }}"
                  imagesizes="100vw">
        </x-slot:head>
    @endif

    {{-- ============================================================
         1 · HERO
         ============================================================ --}}
    <section class="relative isolate flex min-h-[100svh] items-end overflow-hidden bg-[#111111] text-on-image" aria-labelledby="hero-title">
        <div class="absolute inset-0 -z-10">
            {{-- Konum: dikey görsellerde yatay ekranda yapıların bulunduğu alt-orta bölge görünsün --}}
            <x-site.picture :media="$heroImage" :alt="$home['hero']['image_caption']" eager class="hero-image object-[50%_68%]" />
            {{-- Hafif genel karartma (kullanıcı talebi) + okunabilirlik için üst (header) ve alt (başlık) gradyanları --}}
            <div class="absolute inset-0 bg-[#111111]/30"></div>
            <div class="absolute inset-0 bg-[linear-gradient(to_bottom,rgba(17,17,17,0.55)_0%,rgba(17,17,17,0)_22%,rgba(17,17,17,0)_45%,rgba(17,17,17,0.82)_100%)]"></div>
            <div class="absolute inset-0 bg-[linear-gradient(to_right,rgba(17,17,17,0.6)_0%,rgba(17,17,17,0.25)_40%,rgba(17,17,17,0)_65%)]"></div>
        </div>

        {{-- Talep 1: solda marka metni, sağda portföy filtresi --}}
        <div class="container-gab w-full pb-12 pt-32 lg:pb-16 lg:pt-40">
            <div class="grid-gab items-end gap-y-12">
                <div @class(['col-span-4', 'lg:col-span-6 xl:col-span-7' => $realEstate, 'lg:col-span-10' => ! $realEstate])>
                    {{-- Üst etiket kullanıcı talebiyle kaldırıldı (2026-11-08); panelden girilirse yeniden görünür --}}
                    @if (filled($home['hero']['eyebrow'] ?? null))
                        <p class="eyebrow text-on-image/80" data-reveal>{{ $home['hero']['eyebrow'] }}</p>
                    @endif
                    {{-- LCP öğesi: reveal animasyonu yok (JS beklemeden ilk boyamada görünür — Core Web Vitals) --}}
                    <h1 id="hero-title" @class(['max-w-[12ch] text-display', 'mt-6' => filled($home['hero']['eyebrow'] ?? null)])>
                        {{ $home['hero']['title'] }}
                    </h1>
                    <div class="mt-10 max-w-md border-t border-on-image/25 pt-8 lg:mt-12" data-reveal style="--reveal-delay: 240ms">
                        <p class="text-lead text-on-image/85">{{ $home['hero']['text'] }}</p>
                        <div class="mt-8 flex flex-wrap items-center gap-x-8 gap-y-4">
                            <x-site.button href="#projeler" variant="link" class="text-on-image">Projeleri keşfet</x-site.button>
                            <x-site.button href="#iletisim-formu" variant="link" class="text-on-image">Bir proje konuşalım</x-site.button>
                        </div>
                    </div>
                    <p class="mt-10 hidden items-center gap-3 text-[0.75rem] text-on-image/70 lg:flex">
                        <span class="h-px w-6 bg-on-image/50" aria-hidden="true"></span>
                        Görsel: {{ $home['hero']['image_caption'] }}
                    </p>
                </div>

                @if ($realEstate)
                    <div class="col-span-4 lg:col-span-6 xl:col-span-5" data-reveal style="--reveal-delay: 320ms">
                        <x-site.property-filter :locations="$locations" variant="hero" />
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- ============================================================
         PORTFÖYÜMÜZ — öne çıkan son 9 ilan (talep 2)
         ============================================================ --}}
    @if ($realEstate && $featuredProperties->isNotEmpty())
        <section id="portfoy" class="section-y" aria-labelledby="portfolio-title">
            <div class="container-gab">
                <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between" data-reveal>
                    <div>
                        <p class="eyebrow flex items-center gap-4"><span class="h-px w-10 bg-line" aria-hidden="true"></span>Öne çıkanlar</p>
                        <h2 id="portfolio-title" class="mt-8 text-h1">Portföylerimiz</h2>
                    </div>
                    <div class="flex flex-wrap gap-x-8 gap-y-3">
                        <x-site.button href="{{ route('properties.sale') }}" variant="link">Satılık</x-site.button>
                        <x-site.button href="{{ route('properties.rent') }}" variant="link">Kiralık</x-site.button>
                        <x-site.button href="{{ route('properties.index') }}" variant="link">Tüm portföy</x-site.button>
                    </div>
                </div>

                <div class="mt-14 grid gap-5 sm:grid-cols-2 lg:mt-20 lg:gap-6 xl:grid-cols-3">
                    @foreach ($featuredProperties as $property)
                        <x-site.property-card :property="$property" data-reveal style="--reveal-delay: {{ ($loop->index % 3) * 80 }}ms" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ============================================================
         2 · MARKA CÜMLESİ
         ============================================================ --}}
    <section class="plan-lines border-t border-line section-y" aria-label="Yaklaşımımız">
        <div class="container-gab relative">
            <div class="grid-gab">
                <p class="eyebrow col-span-4 lg:col-span-3" data-reveal>{{ settings('site.brand') ?: config('gab.brand') }}</p>
                <blockquote class="col-span-4 mt-8 lg:col-span-9 lg:mt-0" data-reveal style="--reveal-delay: 100ms">
                    <p class="text-statement font-serif">
                        <span class="text-accent">“</span>{{ $home['statement'] }}<span class="text-accent">”</span>
                    </p>
                </blockquote>
            </div>
        </div>
    </section>

    {{-- ============================================================
         3 · KISA TANITIM
         ============================================================ --}}
    <section class="border-t border-line section-y" aria-labelledby="intro-title">
        <div class="container-gab">
            <div class="grid-gab gap-y-10">
                <div class="col-span-4 lg:col-span-5" data-reveal>
                    <x-site.section-label number="1" label="Hakkımızda" />
                    <h2 id="intro-title" class="mt-8 text-h2">{{ $home['intro']['title'] }}</h2>
                </div>

                <div class="col-span-4 lg:col-span-6 lg:col-start-7" data-reveal style="--reveal-delay: 120ms">
                    <p class="text-lead text-muted">{{ $home['intro']['text'] }}</p>

                    <dl class="mt-12 grid grid-cols-2 border-t border-line">
                        <div class="border-b border-r border-line py-6 pr-4">
                            <dt class="eyebrow">Merkez</dt>
                            <dd class="mt-2 font-serif text-h3">İstanbul, Etiler</dd>
                        </div>
                        <div class="border-b border-line py-6 pl-6">
                            <dt class="eyebrow">Hizmet alanı</dt>
                            <dd class="mt-2 font-serif text-h3">Türkiye geneli</dd>
                        </div>
                        <div class="border-b border-r border-line py-6 pr-4">
                            <dt class="eyebrow">Portföy</dt>
                            <dd class="mt-2 font-serif text-h3" data-stat="projects">{{ $projectCount }} proje</dd>
                        </div>
                        <div class="border-b border-line py-6 pl-6">
                            <dt class="eyebrow">Hizmet</dt>
                            <dd class="mt-2 font-serif text-h3" data-stat="services">{{ $serviceCount }} disiplin</dd>
                        </div>
                    </dl>

                    <ul class="mt-10 flex flex-wrap gap-x-6 gap-y-2 text-[0.875rem] text-muted" aria-label="Değerlerimiz">
                        @foreach ($home['intro']['values'] as $value)
                            <li class="flex items-center gap-2"><span class="size-1 rounded-full bg-accent" aria-hidden="true"></span>{{ $value }}</li>
                        @endforeach
                    </ul>

                    <x-site.button href="{{ route('about') }}" variant="link" class="mt-12">Hakkımızda</x-site.button>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
         4 · ÖNE ÇIKAN PROJELER (asimetrik grid)
         ============================================================ --}}
    <section id="projeler" class="border-t border-line section-y" aria-labelledby="projects-title">
        <div class="container-gab">
            <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between" data-reveal>
                <div>
                    <x-site.section-label number="2" label="Seçili projeler" />
                    <h2 id="projects-title" class="mt-8 text-h1">Öne çıkan<br class="hidden sm:block"> projeler</h2>
                </div>
                <x-site.button href="{{ route('projects.index') }}" variant="link">Tüm projeler ({{ $projectCount }})</x-site.button>
            </div>

            @php
                // Asimetrik yerleşim: 8 + 4 / 5 + 7 (masaüstü)
                $layout = [
                    ['class' => 'lg:col-span-8', 'aspect' => 'aspect-[4/3] lg:aspect-[16/10]', 'sizes' => '(min-width: 1024px) 66vw, 100vw'],
                    ['class' => 'lg:col-span-4 lg:mt-48', 'aspect' => 'aspect-[4/5]', 'sizes' => '(min-width: 1024px) 33vw, 100vw'],
                    ['class' => 'lg:col-span-5', 'aspect' => 'aspect-[4/5]', 'sizes' => '(min-width: 1024px) 42vw, 100vw'],
                    ['class' => 'lg:col-span-7 lg:mt-32', 'aspect' => 'aspect-[4/3]', 'sizes' => '(min-width: 1024px) 58vw, 100vw'],
                ];
            @endphp

            <div class="grid-gab mt-16 gap-y-16 lg:mt-24 lg:gap-y-24">
                @foreach ($featured as $project)
                    @php
                        $l = $layout[$loop->index % count($layout)];
                    @endphp
                    <x-site.project-card
                        :project="$project"
                        :index="$loop->iteration"
                        :aspect="$l['aspect']"
                        :sizes="$l['sizes']"
                        class="col-span-4 {{ $l['class'] }}"
                        data-reveal />
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================================
         5 · HİZMET ALANLARI
         ============================================================ --}}
    <section class="border-t border-line bg-surface section-y" aria-labelledby="services-title">
        <div class="container-gab">
            <div class="grid-gab gap-y-8">
                <div class="col-span-4 self-start lg:sticky lg:top-32 lg:col-span-4" data-reveal>
                    <x-site.section-label number="3" label="Hizmetler" />
                    <h2 id="services-title" class="mt-8 text-h2">Tek çatı altında, uçtan uca.</h2>
                    <p class="mt-6 max-w-sm text-muted">{{ $home['services_intro'] }}</p>
                    <x-site.button href="{{ route('services.index') }}" variant="link" class="mt-10">Tüm hizmetler</x-site.button>
                </div>

                <ol class="col-span-4 border-t border-line lg:col-span-7 lg:col-start-6">
                    @foreach ($services as $service)
                        <li class="border-b border-line" data-reveal>
                            <a href="{{ route('services.show', $service) }}" class="group grid grid-cols-[2.5rem_1fr_auto] items-center gap-x-4 py-6 lg:grid-cols-[3.5rem_1fr_7rem] lg:py-7">
                                <span class="eyebrow tabular-nums">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <span>
                                    <span class="block font-serif text-h3 transition-transform duration-500 ease-[var(--ease-architect)] group-hover:translate-x-2">{{ $service->title }}</span>
                                    <span class="mt-2 hidden max-w-xl text-[0.9375rem] leading-relaxed text-muted sm:block">{{ $service->excerpt }}</span>
                                </span>
                                {{-- Hover'da açılan görsel önizleme (yalnızca masaüstü) --}}
                                <span class="relative hidden aspect-[4/3] w-28 overflow-hidden lg:block" aria-hidden="true">
                                    @if ($service->cover)
                                        <span class="absolute inset-0 transition-[clip-path] duration-700 ease-[var(--ease-architect)] [clip-path:inset(0_0_0_100%)] group-hover:[clip-path:inset(0_0_0_0)] group-focus-visible:[clip-path:inset(0_0_0_0)]">
                                            <x-site.picture :media="$service->cover" alt="" sizes="112px" />
                                        </span>
                                    @endif
                                    <svg class="absolute bottom-0 right-0 size-5 text-muted transition-opacity duration-300 group-hover:opacity-0" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.25"><path d="M1 8h13M9.5 3.5 14 8l-4.5 4.5" /></svg>
                                </span>
                                <svg class="size-5 text-muted lg:hidden" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.25" aria-hidden="true"><path d="M1 8h13M9.5 3.5 14 8l-4.5 4.5" /></svg>
                            </a>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </section>

    {{-- ============================================================
         6 · MİMARİ YAKLAŞIM
         ============================================================ --}}
    <section class="border-t border-line section-y" aria-labelledby="approach-title">
        <div class="container-gab">
            <div class="grid-gab items-end gap-y-12">
                @if ($approachProject)
                    <figure class="col-span-4 lg:col-span-6" data-reveal>
                        <div class="aspect-[4/3] overflow-hidden bg-surface">
                            <x-site.picture :media="$approachProject->cover" :alt="$approachProject->title.' — '.$approachProject->subtitle" sizes="(min-width: 1024px) 50vw, 100vw" />
                        </div>
                        <figcaption class="mt-4 flex items-center justify-between text-[0.8125rem] text-subtle">
                            <span>{{ $approachProject->title }}</span>
                            <span>{{ $approachProject->location }}</span>
                        </figcaption>
                    </figure>
                @endif

                <div class="col-span-4 lg:col-span-5 lg:col-start-8 lg:pb-20" data-reveal style="--reveal-delay: 120ms">
                    <x-site.section-label number="4" label="Mimari yaklaşım" />
                    <h2 id="approach-title" class="mt-8 text-h2">{{ $home['approach']['title'] }}</h2>
                    <p class="mt-8 text-lead">{{ $home['approach']['text'] }}</p>
                    <p class="mt-6 text-muted">{{ $home['approach']['text_secondary'] }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
         7 · REFERANSLAR
         ============================================================ --}}
    <section class="border-t border-line section-y" aria-labelledby="references-title">
        <div class="container-gab">
            <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between" data-reveal>
                <div>
                    <x-site.section-label number="5" label="Referanslar" />
                    <h2 id="references-title" class="mt-8 max-w-[16ch] text-h2">Birlikte değer ürettiğimiz kurumlar ve projeler</h2>
                </div>
                <x-site.button href="{{ route('references') }}" variant="link">Tüm referanslar</x-site.button>
            </div>

            <ul class="mt-16 grid border-t border-line sm:grid-cols-2 lg:mt-20 lg:grid-cols-4">
                @foreach ($references as $reference)
                    <li class="border-b border-line py-6 sm:pr-6 sm:[&:nth-child(2n)]:pl-6 lg:px-6 lg:[&:nth-child(4n+1)]:pl-0 lg:[&:not(:nth-child(4n))]:border-r" data-reveal style="--reveal-delay: {{ ($loop->index % 4) * 60 }}ms">
                        <p class="font-serif text-[1.375rem] leading-tight">{{ $reference->name }}</p>
                        @if ($reference->service)
                            <p class="mt-2 text-[0.8125rem] text-subtle">{{ $reference->service }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- ============================================================
         8 · PROJE GELİŞTİRME SÜRECİ
         ============================================================ --}}
    <section class="border-t border-line bg-surface section-y" aria-labelledby="process-title">
        <div class="container-gab">
            <div class="grid-gab gap-y-8" data-reveal>
                <div class="col-span-4 lg:col-span-5">
                    <x-site.section-label number="6" label="Süreç" />
                    <h2 id="process-title" class="mt-8 text-h2">{{ $home['process']['title'] }}</h2>
                </div>
                <p class="col-span-4 text-lead text-muted lg:col-span-5 lg:col-start-8 lg:self-end">{{ $home['process']['text'] }}</p>
            </div>

            <ol class="mt-16 grid gap-px overflow-hidden border border-line bg-line sm:grid-cols-2 lg:mt-24 lg:grid-cols-6">
                @foreach ($home['process']['steps'] as $step)
                    <li class="flex min-h-44 flex-col justify-between bg-surface p-6 lg:min-h-64" data-reveal style="--reveal-delay: {{ $loop->index * 70 }}ms">
                        <span class="font-serif text-[3rem] leading-none text-accent tabular-nums">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="mt-8 text-[1rem] font-medium leading-snug">{{ $step }}</span>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- ============================================================
         9 · CTA
         ============================================================ --}}
    {{-- Talep 9: "Bir proje konuşalım" formu → panelde Web Formları --}}
    <section class="border-t border-line section-y" aria-labelledby="cta-title">
        <div class="container-gab">
            <div class="grid-gab gap-y-14">
                <div class="col-span-4 lg:col-span-5" data-reveal>
                    <h2 id="cta-title" class="text-display">{{ $home['cta']['title'] }}</h2>
                    <p class="mt-10 max-w-sm text-lead text-muted">{{ $home['cta']['text'] }}</p>
                    <address class="mt-10 space-y-2 text-[0.9375rem] not-italic text-muted">
                        <p class="whitespace-pre-line">{{ settings('contact.address') }}</p>
                        @if (settings('contact.phone'))
                            <p><a href="tel:{{ preg_replace('/[^\d+]/', '', settings('contact.phone')) }}" class="text-fg hover:text-accent">{{ settings('contact.phone') }}</a></p>
                        @endif
                        @if (settings('contact.email'))
                            <p><a href="mailto:{{ settings('contact.email') }}" class="text-fg hover:text-accent">{{ settings('contact.email') }}</a></p>
                        @endif
                    </address>
                </div>
                <div class="col-span-4 lg:col-span-6 lg:col-start-7" data-reveal style="--reveal-delay: 120ms">
                    <x-site.contact-form source="home_cta" />
                </div>
            </div>
        </div>
    </section>
</x-layouts.site>
