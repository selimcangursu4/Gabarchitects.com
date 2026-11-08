@props([
    'items' => [],   // Anasayfa hariç: [['label' => 'Projeler', 'url' => '/projeler'], ['label' => 'Sapanca Villa']]
    'onImage' => false,   // görsel üzerindeyse renkler temadan bağımsız
])

@php
    $link = $onImage ? 'hover:text-on-image' : 'hover:text-fg';
    $current = $onImage ? 'text-on-image' : 'text-fg';
@endphp

{{-- Görünür konum menüsü + aynı veriden BreadcrumbList JSON-LD (layout <head> içinde basar) --}}
<nav aria-label="Konum" {{ $attributes->merge(['class' => 'eyebrow']) }}>
    <ol class="flex flex-wrap items-center gap-2">
        <li><a href="{{ route('home') }}" class="{{ $link }}">Anasayfa</a></li>
        @foreach ($items as $crumb)
            <li aria-hidden="true">/</li>
            <li>
                @if (! empty($crumb['url']) && ! $loop->last)
                    <a href="{{ $crumb['url'] }}" class="{{ $link }}">{{ $crumb['label'] }}</a>
                @else
                    <span @if ($loop->last) aria-current="page" class="{{ $current }}" @endif>{{ $crumb['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>

@if ($items)
    @push('structured-data')
        {{ \App\Support\StructuredData::script(\App\Support\StructuredData::breadcrumbs($items)) }}
    @endpush
@endif
