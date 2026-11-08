{{--
    Geçici tipografik logotype. Müşteriden vektör (SVG) logo geldiğinde
    yalnızca bu bileşen değiştirilecek (açık/koyu varyant).
--}}
<a href="{{ route('home') }}" {{ $attributes->merge(['class' => 'group inline-flex items-baseline gap-2.5 leading-none']) }} aria-label="GAB Architects — Ana sayfa">
    <span class="font-serif text-[1.875rem] tracking-[-0.01em]">GAB</span>
    {{-- lang="en": tr dilinde uppercase "i" → "İ" olur ("ARCHİTECTS") --}}
    <span lang="en" class="text-[0.625rem] font-medium uppercase tracking-[0.32em] opacity-80 transition-opacity group-hover:opacity-100">Architects</span>
</a>
