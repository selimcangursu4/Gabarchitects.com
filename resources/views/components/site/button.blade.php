@props([
    'href',
    'variant' => 'solid', // solid | outline | link
    'arrow' => true,
])

@php
    $base = 'group inline-flex items-center gap-3 text-[0.8125rem] font-medium uppercase tracking-[0.14em] transition-colors duration-300';
    $variants = [
        'solid' => 'h-13 px-7 bg-fg text-bg hover:bg-accent hover:text-[#111111]',
        'outline' => 'h-13 px-7 border border-current/40 hover:border-current',
        'link' => 'measure-line pb-2',
    ];
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $base.' '.$variants[$variant]]) }}>
    <span>{{ $slot }}</span>
    @if ($arrow)
        <svg class="size-4 transition-transform duration-500 ease-[var(--ease-architect)] group-hover:translate-x-1" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.25" aria-hidden="true">
            <path d="M1 8h13M9.5 3.5 14 8l-4.5 4.5" />
        </svg>
    @endif
</a>
