<button type="button"
        x-data
        @click="$store.theme.toggle()"
        :aria-pressed="$store.theme.isDark.toString()"
        :aria-label="$store.theme.isDark ? 'Açık temaya geç' : 'Koyu temaya geç'"
        :title="$store.theme.isDark ? 'Açık tema' : 'Koyu tema'"
        aria-label="Temayı değiştir"
        {{ $attributes->merge(['class' => 'relative inline-flex size-10 items-center justify-center rounded-full border border-current/25 transition-colors hover:border-current/60']) }}>
    {{-- Güneş (koyu temadayken gösterilir) --}}
    <svg x-show="$store.theme.isDark" class="size-[1.05rem]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" aria-hidden="true">
        <circle cx="12" cy="12" r="4.25" />
        <path d="M12 2.5v2.25M12 19.25v2.25M4.75 12H2.5M21.5 12h-2.25M6.87 6.87 5.28 5.28M18.72 18.72l-1.59-1.59M6.87 17.13l-1.59 1.59M18.72 5.28l-1.59 1.59" stroke-linecap="round" />
    </svg>
    {{-- Ay (açık temadayken gösterilir) --}}
    <svg x-show="!$store.theme.isDark" x-cloak class="size-[1.05rem]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" aria-hidden="true">
        <path d="M20.25 14.4A8.25 8.25 0 0 1 9.6 3.75a8.25 8.25 0 1 0 10.65 10.65Z" stroke-linejoin="round" />
    </svg>
</button>
