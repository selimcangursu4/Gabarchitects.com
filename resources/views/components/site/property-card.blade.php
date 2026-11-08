@props([
    'property',   // App\Models\Property (cover eager loaded)
    'sizes' => '(min-width: 1280px) 30vw, (min-width: 768px) 45vw, 100vw',
])

@php
    $price = $property->formattedPrice();
@endphp

{{-- E-ticaret ürün kartı mantığında ilan kartı (talep 2) --}}
<article {{ $attributes->merge(['class' => 'group relative flex flex-col border border-line bg-surface transition-colors duration-300 hover:border-fg/30']) }}>
    <div class="relative aspect-[4/3] overflow-hidden bg-bg">
        <x-site.picture :media="$property->cover" :alt="$property->title" :sizes="$sizes" class="img-zoom" />

        <div class="absolute left-3 top-3 flex flex-wrap gap-2">
            <span class="bg-fg px-2.5 py-1 text-[0.6875rem] font-medium uppercase tracking-[0.12em] text-bg">{{ $property->listing_type->label() }}</span>
            @if ($property->listing_status->value !== 'available')
                <span class="bg-accent px-2.5 py-1 text-[0.6875rem] font-medium uppercase tracking-[0.12em] text-[#111111]">{{ $property->listing_status->label() }}</span>
            @endif
            @if ($property->is_sample)
                <span class="border border-on-image/60 bg-[#111111]/70 px-2.5 py-1 text-[0.6875rem] font-medium uppercase tracking-[0.12em] text-on-image">Örnek</span>
            @endif
        </div>
    </div>

    <div class="flex flex-1 flex-col p-5 lg:p-6">
        <p class="eyebrow">
            {{ $property->propertyTypeLabel() }}@if ($property->district || $property->city) · {{ collect([$property->district, $property->city])->filter()->implode(', ') }}@endif
        </p>

        <h3 class="mt-3 font-serif text-[1.5rem] leading-[1.15]">
            {{-- Kartın tamamı tıklanabilir (stretched link) --}}
            <a href="{{ $property->url() }}" class="after:absolute after:inset-0 focus-visible:outline-none group-focus-within:underline">{{ $property->title }}</a>
        </h3>

        <dl class="mt-5 mb-6 flex flex-wrap gap-x-5 gap-y-2 text-[0.875rem] text-muted">
            @if ($property->area_m2)
                <div class="flex items-center gap-2">
                    <dt class="sr-only">Alan</dt>
                    <svg class="size-4" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.1" aria-hidden="true"><path d="M2 2h12v12H2zM2 6h4V2" /></svg>
                    <dd>{{ number_format($property->area_m2, 0, ',', '.') }} m²</dd>
                </div>
            @endif
            @if ($property->rooms)
                <div class="flex items-center gap-2">
                    <dt class="sr-only">Oda</dt>
                    <svg class="size-4" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.1" aria-hidden="true"><path d="M2 13V6.5L8 2l6 4.5V13H2zM6.5 13V9h3v4" /></svg>
                    <dd>{{ $property->rooms }}</dd>
                </div>
            @endif
            @if ($property->bathrooms)
                <div class="flex items-center gap-2">
                    <dt class="sr-only">Banyo</dt>
                    <svg class="size-4" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.1" aria-hidden="true"><path d="M2 8h12v2a3 3 0 0 1-3 3H5a3 3 0 0 1-3-3V8zM4 8V3.5A1.5 1.5 0 0 1 7 3.5" /></svg>
                    <dd>{{ $property->bathrooms }} banyo</dd>
                </div>
            @endif
        </dl>

        <div class="mt-auto flex items-end justify-between gap-4 border-t border-line pt-5">
            <p class="font-serif text-[1.625rem] leading-none tracking-[-0.01em]">
                {{ $price ?? 'Fiyat için arayın' }}
            </p>
            <span class="flex size-10 shrink-0 items-center justify-center border border-line transition-colors duration-300 group-hover:border-fg group-hover:bg-fg group-hover:text-bg" aria-hidden="true">
                <svg class="size-4" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.25"><path d="M1 8h13M9.5 3.5 14 8l-4.5 4.5" /></svg>
            </span>
        </div>
    </div>
</article>
