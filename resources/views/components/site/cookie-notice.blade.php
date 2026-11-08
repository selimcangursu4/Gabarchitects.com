@php
    // Metin panelden (Site Ayarları → Çerez bildirimi) değiştirilebilir; müşteri/hukuk onaylı metin girilmelidir.
    // Varsayılan metin yalnızca bugünkü teknik gerçeği söyler (TD-055): sitede yalnızca zorunlu çerez var.
    // Sürüm = metnin özeti: metin değişirse bant ziyaretçiye yeniden gösterilir.
    $text = settings('legal.cookie_banner_text')
        ?: 'Bu sitede yalnızca sitenin çalışması için zorunlu çerezler (oturum ve form güvenliği) ve tema tercihiniz için tarayıcı depolaması kullanılır. Reklam, analiz veya üçüncü taraf çerezi kullanılmaz.';
    $version = substr(md5($text), 0, 8);
@endphp

{{-- KVKK / çerez bildirimi (brief §10). Yalnızca zorunlu çerez olduğu için onay değil bilgilendirme: tek "Anladım" butonu. --}}
<div x-data="cookieNotice('{{ $version }}')" x-show="open" x-cloak x-transition.opacity.duration.300ms
     role="region" aria-label="Çerez bildirimi" data-cookie-notice
     class="cookie-notice fixed inset-x-4 bottom-4 z-50 border border-line bg-surface p-5 shadow-[0_12px_40px_rgba(0,0,0,0.25)] sm:inset-x-auto sm:right-6 sm:bottom-6 sm:max-w-md">
    <p class="text-[0.875rem] leading-relaxed text-muted">
        {{ $text }}
        <a href="{{ route('legal.cookies') }}" class="whitespace-nowrap text-fg underline decoration-line underline-offset-4 hover:decoration-current">Çerez Politikası</a>
    </p>
    <button type="button" @click="dismiss()"
            class="mt-4 inline-flex h-11 items-center bg-fg px-6 text-[0.8125rem] font-medium uppercase tracking-[0.14em] text-bg transition-colors hover:bg-accent hover:text-[#111111]">
        Anladım
    </button>
</div>
