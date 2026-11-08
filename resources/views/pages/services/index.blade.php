<x-layouts.site title="Hizmetlerimiz" description="Proje geliştirme, mimarlık, iç mimarlık, yatırım danışmanlığı, inşaat ve taahhüt hizmetleri.">
    <x-site.page-header
        title="Hizmetlerimiz"
        eyebrow="Hizmetler"
        :intro="$intro"
        :breadcrumbs="[['label' => 'Hizmetler']]" />

    <section class="section-y">
        <div class="container-gab">
            <ol class="border-t border-line">
                @foreach ($services as $service)
                    <li class="border-b border-line" data-reveal>
                        <a href="{{ route('services.show', $service) }}" class="group grid grid-cols-4 items-center gap-x-6 gap-y-5 py-8 lg:grid-cols-12 lg:py-10">
                            <span class="eyebrow col-span-1 tabular-nums lg:col-span-1">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <h2 class="col-span-3 font-serif text-h2 transition-transform duration-500 ease-[var(--ease-architect)] group-hover:translate-x-2 lg:col-span-5">{{ $service->title }}</h2>
                            <p class="col-span-4 col-start-1 text-muted lg:col-span-4 lg:col-start-7">{{ $service->excerpt }}</p>
                            <div class="relative col-span-4 aspect-[16/9] overflow-hidden bg-surface lg:col-span-2 lg:aspect-[4/3]">
                                <x-site.picture :media="$service->cover" alt="" sizes="(min-width: 1024px) 16vw, 100vw" class="img-zoom" />
                            </div>
                        </a>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <x-site.cta-band />
</x-layouts.site>
