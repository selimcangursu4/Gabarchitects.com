@props(['number', 'label'])

{{-- Mimari pafta etiketi: "01 — Hakkımızda" --}}
<p {{ $attributes->merge(['class' => 'eyebrow flex items-center gap-4']) }}>
    <span class="tabular-nums text-fg">{{ str_pad($number, 2, '0', STR_PAD_LEFT) }}</span>
    <span class="h-px w-10 bg-line" aria-hidden="true"></span>
    <span>{{ $label }}</span>
</p>
