@props([
    'contact',            // ['whatsapp', 'mobile_tel', 'mobile', 'tel', 'phone'] (null = numara girilmemiş)
    'compact' => false,   // mobil alt çubuk: yan yana, kısa etiketler
])

@php
    $btn = $compact
        ? 'group inline-flex h-13 min-w-0 items-center justify-center gap-2 px-2 text-[0.75rem] font-medium uppercase tracking-[0.06em] transition-colors'
        : 'group inline-flex h-13 items-center justify-center gap-3 px-5 text-[0.8125rem] font-medium uppercase tracking-[0.12em] transition-colors';
    // Numara girilmemişse buton görünür ama pasiftir (Site Ayarları'ndan numara girilince çalışır)
    $disabled = 'pointer-events-none cursor-not-allowed opacity-45';

    // Arama butonları: WhatsApp (cep) numarası + sabit hat (kullanıcı talebi 2026-11-08).
    // İki numara aynıysa tek buton gösterilir.
    $calls = [
        ['key' => 'mobile', 'href' => $contact['mobile_tel'] ?? null, 'number' => $contact['mobile'] ?? null,
            'short' => 'Cep', 'aria' => 'Cep telefonunu ara', 'missing' => 'WhatsApp / cep numarası henüz tanımlanmadı'],
        ['key' => 'phone', 'href' => $contact['tel'] ?? null, 'number' => $contact['phone'] ?? null,
            'short' => 'Sabit', 'aria' => 'Sabit hattı ara', 'missing' => 'Telefon numarası henüz tanımlanmadı'],
    ];
    if ($calls[0]['href'] && $calls[0]['href'] === $calls[1]['href']) {
        array_shift($calls);
    }
    $cols = count($calls) + 1;
@endphp

{{-- Kullanıcı kararı: her zaman WhatsApp + arama butonları (TD-047) --}}
<div {{ $attributes->merge(['class' => $compact ? ($cols === 3 ? 'grid grid-cols-[1.4fr_1fr_1fr] gap-2' : 'grid grid-cols-2 gap-2') : 'flex flex-col gap-3']) }}>
    @if ($contact['whatsapp'])
        <a href="{{ $contact['whatsapp'] }}" target="_blank" rel="noopener" data-contact="whatsapp"
           class="{{ $btn }} bg-[#1f7a4d] text-white hover:bg-[#1a6a42]">
    @else
        <span role="link" aria-disabled="true" data-contact="whatsapp" title="WhatsApp numarası henüz tanımlanmadı"
              class="{{ $btn }} {{ $disabled }} bg-[#1f7a4d] text-white">
    @endif
            <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38a9.9 9.9 0 0 0 4.74 1.21h.01c5.46 0 9.9-4.45 9.9-9.91C21.95 6.45 17.5 2 12.04 2Zm0 18.15h-.01a8.2 8.2 0 0 1-4.19-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.22 8.22 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.25-8.24a8.24 8.24 0 0 1 8.24 8.25c0 4.54-3.7 8.23-8.24 8.23Zm4.52-6.16c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.13-.16.24-.64.8-.78.97-.15.16-.29.18-.54.06-.25-.12-1.05-.39-1.99-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.02-.38.11-.5.11-.11.25-.29.37-.43.13-.15.17-.25.25-.42.08-.16.04-.31-.02-.43-.06-.12-.56-1.34-.76-1.84-.2-.48-.41-.42-.56-.42h-.48a.92.92 0 0 0-.67.31c-.23.25-.87.85-.87 2.07 0 1.22.89 2.4 1.02 2.57.12.16 1.75 2.67 4.23 3.74.59.26 1.05.41 1.41.52.59.19 1.13.16 1.56.1.48-.07 1.47-.6 1.67-1.18.21-.58.21-1.07.15-1.18-.06-.1-.23-.16-.48-.29Z" /></svg>
            <span class="truncate">{{ $compact ? 'WhatsApp' : 'WhatsApp\'tan yazın' }}</span>
    @if ($contact['whatsapp'])
        </a>
    @else
        </span>
    @endif

    @foreach ($calls as $call)
        @if ($call['href'])
            <a href="{{ $call['href'] }}" data-contact="{{ $call['key'] }}" aria-label="{{ $call['aria'] }}{{ $call['number'] ? ': '.$call['number'] : '' }}"
               class="{{ $btn }} border border-fg/40 hover:border-fg hover:bg-fg hover:text-bg">
        @else
            <span role="link" aria-disabled="true" data-contact="{{ $call['key'] }}" title="{{ $call['missing'] }}"
                  class="{{ $btn }} {{ $disabled }} border border-fg/40">
        @endif
                <svg class="size-[1.1rem] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M3.5 5.25A2.25 2.25 0 0 1 5.75 3h1.38c.52 0 .97.35 1.09.85l.92 3.7a1.12 1.12 0 0 1-.42 1.17l-1.6 1.2a12.04 12.04 0 0 0 5.96 5.96l1.2-1.6a1.12 1.12 0 0 1 1.17-.42l3.7.92c.5.12.85.57.85 1.09v1.38A2.25 2.25 0 0 1 18.75 21h-.75C10.27 21 3.5 14.23 3.5 6v-.75Z" stroke-linejoin="round" /></svg>
                @if ($compact)
                    <span class="truncate">{{ count($calls) > 1 ? $call['short'] : 'Ara' }}</span>
                @else
                    <span>Hemen arayın @if ($call['number'])<span class="block text-[0.6875rem] normal-case tracking-normal opacity-70">{{ $call['number'] }}</span>@endif</span>
                @endif
        @if ($call['href'])
            </a>
        @else
            </span>
        @endif
    @endforeach
</div>
