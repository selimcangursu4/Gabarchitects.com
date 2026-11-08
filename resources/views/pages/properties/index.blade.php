{{-- Filtrelenmiş sonuçlar dizine alınmaz (yinelenen/ince içerik); kanonik: filtresiz adres --}}
<x-layouts.site :title="$title" :description="$title.' — '.(settings('site.brand') ?: config('gab.brand')).' gayrimenkul portföyü.'" :noindex="$activeFilterCount > 0">
    <x-site.page-header
        :title="$title"
        eyebrow="Portföylerimiz"
        intro="İstanbul, Bodrum ve İzmir’in prestijli lokasyonlarındaki seçkin portföylerimizi filtreleyerek inceleyin."
        :breadcrumbs="array_filter([
            ['label' => 'Portföylerimiz', 'url' => route('properties.index')],
            $listingType ? ['label' => $listingType->label()] : null,
        ])">
        {{-- Satılık / Kiralık / Tümü sekmeleri (talep 3) --}}
        <nav aria-label="Portföy türü" class="mt-12 flex flex-wrap gap-2 lg:mt-16">
            @foreach ([['Tümü', route('properties.index'), ! $listingType], ['Satılık', route('properties.sale'), $listingType?->value === 'sale'], ['Kiralık', route('properties.rent'), $listingType?->value === 'rent']] as [$tabLabel, $tabUrl, $tabActive])
                <a href="{{ $tabUrl }}" @if ($tabActive) aria-current="page" @endif
                   @class(['inline-flex h-11 items-center border px-5 text-[0.8125rem] font-medium uppercase tracking-[0.14em] transition-colors',
                           'border-fg bg-fg text-bg' => $tabActive, 'border-line hover:border-fg' => ! $tabActive])>{{ $tabLabel }}</a>
            @endforeach
        </nav>
    </x-site.page-header>

    <section class="section-y !pt-12 lg:!pt-16" x-data="{ filtersOpen: false }">
        <div class="container-gab">
            <div class="grid-gab gap-y-10">
                {{-- Filtre: masaüstünde yapışkan yan panel, mobilde açılır --}}
                <aside class="col-span-4 lg:col-span-3" aria-label="Filtreler">
                    <button type="button" @click="filtersOpen = !filtersOpen" :aria-expanded="filtersOpen.toString()" aria-controls="property-filters"
                            class="flex h-12 w-full items-center justify-between border border-line px-4 text-[0.8125rem] font-medium uppercase tracking-[0.14em] lg:hidden">
                        Filtrele @if ($activeFilterCount) ({{ $activeFilterCount }}) @endif
                        <svg class="size-4 transition-transform" :class="filtersOpen && 'rotate-180'" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.25" aria-hidden="true"><path d="m2.5 4.5 3.5 3.5 3.5-3.5" /></svg>
                    </button>
                    <div id="property-filters" class="mt-4 hidden lg:sticky lg:top-28 lg:mt-0 lg:block" :class="filtersOpen && '!block'">
                        <x-site.property-filter :filters="$filters" :locations="$locations" variant="panel" :fixed-type="$fixedType" />
                    </div>
                </aside>

                <div class="col-span-4 lg:col-span-9">
                    <div class="flex items-center justify-between border-b border-line pb-5">
                        <p class="text-[0.9375rem]" role="status">
                            <span class="font-serif text-[1.5rem]">{{ $properties->total() }}</span>
                            <span class="text-muted">ilan bulundu</span>
                        </p>
                        @if ($activeFilterCount)
                            <a href="{{ $fixedType ? url()->current() : route('properties.index') }}" class="text-[0.8125rem] text-muted underline decoration-line underline-offset-4 hover:text-fg">Filtreleri temizle</a>
                        @endif
                    </div>

                    @if ($properties->isEmpty())
                        <div class="py-24 text-center">
                            <p class="font-serif text-h3">Bu kriterlere uygun ilan bulunamadı.</p>
                            <p class="mx-auto mt-4 max-w-md text-muted">Filtreleri genişletebilir ya da aradığınız gayrimenkulü bize iletebilirsiniz; portföylerimize uygun seçenekler geldiğinde sizinle iletişime geçelim.</p>
                            <div class="mt-10 flex justify-center gap-4">
                                <x-site.button href="{{ route('properties.index') }}" variant="outline">Tüm portföy</x-site.button>
                                <x-site.button href="{{ route('contact') }}">Talep bırakın</x-site.button>
                            </div>
                        </div>
                    @else
                        {{-- Başlık sırası h1 → h2 → h3 (kart başlıkları h3) --}}
                        <h2 class="sr-only">İlanlar</h2>
                        <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:gap-6 xl:grid-cols-3">
                            @foreach ($properties as $property)
                                <x-site.property-card :property="$property" sizes="(min-width: 1280px) 24vw, (min-width: 640px) 45vw, 100vw" />
                            @endforeach
                        </div>
                        {{ $properties->links() }}
                    @endif
                </div>
            </div>
        </div>
    </section>
</x-layouts.site>
