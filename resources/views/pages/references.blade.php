<x-layouts.site title="Referanslar" :description="(settings('site.brand') ?: config('gab.brand')).' — mimarlık, iç mimarlık ve proje geliştirme alanlarındaki referanslarımız.'">
    <x-site.page-header
        title="Referanslar"
        eyebrow="Kurumsal"
        intro="Mimarlık, iç mimarlık ve proje geliştirme alanlarında gerçekleştirdiğimiz seçkin projelerden bazı örnekler."
        :breadcrumbs="[['label' => 'Kurumsal'], ['label' => 'Referanslar']]" />

    {{-- Talep 8: aktif referansların tamamı --}}
    <section class="section-y">
        <div class="container-gab">
            <ul class="grid border-t border-line sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($references as $reference)
                    <li class="flex min-h-48 flex-col justify-between border-b border-line py-8 sm:odd:pr-8 sm:even:pl-8 sm:even:border-l lg:px-8 lg:[&:nth-child(3n+1)]:pl-0 lg:[&:nth-child(3n+1)]:border-l-0 lg:[&:nth-child(3n+2)]:border-l lg:[&:nth-child(3n)]:border-l" data-reveal>
                        <div>
                            <span class="eyebrow tabular-nums">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            @if ($reference->logo)
                                <div class="mt-6 h-14 w-40"><x-site.picture :media="$reference->logo" :alt="$reference->name" sizes="160px" class="!object-contain grayscale" /></div>
                            @endif
                            <h2 class="mt-6 font-serif text-h3">{{ $reference->name }}</h2>
                            @if ($reference->service)
                                <p class="mt-2 text-[0.875rem] text-muted">{{ $reference->service }}</p>
                            @endif
                            @if ($reference->description)
                                <p class="mt-4 text-[0.9375rem] text-muted">{{ $reference->description }}</p>
                            @endif
                        </div>
                        @if ($reference->project)
                            <a href="{{ $reference->project->url() }}" class="mt-6 inline-flex items-center gap-2 text-[0.8125rem] uppercase tracking-[0.14em] hover:text-accent">Projeyi incele →</a>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    <x-site.cta-band />
</x-layouts.site>
