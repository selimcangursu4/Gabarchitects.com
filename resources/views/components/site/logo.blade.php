{{--
    Geçici tipografik logotype: "GAB" + iki satırlık "Gayrimenkul / Proje Geliştirme" (TD-061).
    Müşteriden vektör (SVG) logo geldiğinde yalnızca bu bileşen değiştirilecek (açık/koyu varyant).
    Not: metin Türkçe (lang="tr") → büyük harfte "GAYRİMENKUL" doğru noktalı İ ile yazılır.
--}}
<a href="{{ route('home') }}" {{ $attributes->merge(['class' => 'group inline-flex items-center gap-3 leading-none']) }} aria-label="{{ config('gab.brand') }} — Ana sayfa">
    <span class="font-serif text-[1.875rem] tracking-[-0.01em]">GAB</span>
    <span class="flex flex-col gap-[0.2rem] whitespace-nowrap border-l border-current/30 pl-3 text-[0.5625rem] font-medium uppercase leading-none tracking-[0.24em] opacity-80 transition-opacity group-hover:opacity-100" aria-hidden="true">
        <span>Gayrimenkul</span>
        <span>Proje Geliştirme</span>
    </span>
</a>
