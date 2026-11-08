@props([
    'media',              // App\Models\Media|null
    'alt' => null,        // boşsa medyanın alt metni kullanılır
    'sizes' => '100vw',
    'eager' => false,     // LCP görseli için true
])

@php
    $alt = $alt ?? ($media?->alt ?? '');
@endphp

@if ($media && $media->hasFormat('webp'))
    <picture>
        @if ($media->hasFormat('avif'))
            <source type="image/avif" srcset="{{ $media->srcset('avif') }}" sizes="{{ $sizes }}">
        @endif
        <img src="{{ $media->url() }}"
             srcset="{{ $media->srcset('webp') }}"
             sizes="{{ $sizes }}"
             width="{{ $media->width }}"
             height="{{ $media->height }}"
             alt="{{ $alt }}"
             @if ($alt === '') role="presentation" @endif
             decoding="async"
             @if ($eager) fetchpriority="high" @else loading="lazy" @endif
             {{ $attributes->merge(['class' => 'block h-full w-full object-cover']) }}>
    </picture>
@else
    {{-- Görsel yok: boş alan yerine nötr yüzey (CLS oluşmaz) --}}
    <div {{ $attributes->merge(['class' => 'h-full w-full bg-line/40']) }} role="img" aria-label="{{ $alt ?: 'Görsel hazırlanıyor' }}"></div>
@endif
