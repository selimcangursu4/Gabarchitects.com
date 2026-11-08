@props([
    'filters' => [],
    'locations' => ['cities' => collect(), 'districts' => collect()],
    'variant' => 'hero',        // hero | panel
    'fixedType' => false,       // /satilik, /kiralik sayfalarında işlem tipi URL'den gelir
])

@php
    $listingTypes = config('real_estate.listing_types');
    $currentType = $filters['islem'] ?? 'satilik';
    $rooms = array_map('intval', $filters['oda'] ?? []);
    $hero = $variant === 'hero';
    $field = 'h-12 w-full border border-line bg-bg px-3.5 text-[0.9375rem] text-fg outline-none transition-colors focus:border-fg';
    $label = 'mb-2 block text-[0.75rem] font-medium uppercase tracking-[0.12em] text-subtle';
    // Hero: fiyat / m² / oda "Daha fazla seçenek" arkasında (kullanıcı talebi). Bu alanlardan biri doluysa açık başlar.
    $advancedActive = $rooms !== [] || collect(['fiyat_min', 'fiyat_max', 'm2_min', 'm2_max'])->contains(fn ($k) => filled($filters[$k] ?? null));
@endphp

{{--
    Portföy filtresi (talep 1). JS kapalıyken de çalışır: GET /portfoy?islem=...
    JS açıkken işlem tipine göre kanonik adrese (/satilik, /kiralik) gönderir,
    ilçeleri seçilen ile göre süzer, arsa gibi tiplerde oda seçimini gizler.
--}}
<form method="GET"
      action="{{ route('properties.index') }}"
      x-data="propertyFilter({
          islem: @js($currentType),
          il: @js($filters['il'] ?? ''),
          ilce: @js($filters['ilce'] ?? ''),
          tip: @js($filters['tip'] ?? ''),
          more: @js($advancedActive),
          districts: @js($locations['districts']),
          noRoomTypes: @js(config('real_estate.types_without_rooms')),
          actions: @js(collect($listingTypes)->mapWithKeys(fn ($t) => [$t['slug'] => url('/'.$t['slug'])])),
      })"
      @submit="submit($event)"
      role="search"
      aria-label="Portföy arama"
      {{ $attributes->merge(['class' => $hero ? 'border border-line bg-bg/92 p-5 text-fg backdrop-blur-md sm:p-7' : 'text-fg']) }}>

    {{-- İşlem tipi: Satılık / Kiralık --}}
    <fieldset>
        <legend class="sr-only">İşlem tipi</legend>
        <div class="grid grid-cols-2 border border-line">
            @foreach ($listingTypes as $type)
                <label class="relative cursor-pointer">
                    <input type="radio" name="islem" value="{{ $type['slug'] }}" x-model="islem" class="peer sr-only"
                           @checked($currentType === $type['slug'])>
                    <span class="flex h-12 items-center justify-center text-[0.8125rem] font-medium uppercase tracking-[0.14em] text-muted transition-colors peer-checked:bg-fg peer-checked:text-bg peer-focus-visible:outline peer-focus-visible:outline-1 peer-focus-visible:outline-accent">
                        {{ $type['label'] }}
                    </span>
                </label>
            @endforeach
        </div>
    </fieldset>

    <div @class(['mt-5 grid gap-4', 'sm:grid-cols-2' => $hero, 'grid-cols-1' => ! $hero])>
        {{-- Emlak tipi --}}
        <div @class(['sm:col-span-2' => $hero])>
            <label for="f-tip-{{ $variant }}" class="{{ $label }}">Emlak tipi</label>
            <select id="f-tip-{{ $variant }}" name="tip" x-model="tip" class="{{ $field }}">
                <option value="">Tümü</option>
                @foreach (config('real_estate.property_types') as $group => $types)
                    <optgroup label="{{ $group }}">
                        @foreach ($types as $key => $typeLabel)
                            <option value="{{ $key }}" @selected(($filters['tip'] ?? '') === $key)>{{ $typeLabel }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>

        {{-- Konum --}}
        <div>
            <label for="f-il-{{ $variant }}" class="{{ $label }}">İl</label>
            <select id="f-il-{{ $variant }}" name="il" x-model="il" @change="ilce = ''; renderDistricts()" class="{{ $field }}">
                <option value="">Tümü</option>
                @foreach ($locations['cities'] as $city)
                    <option value="{{ $city }}" @selected(($filters['il'] ?? '') === $city)>{{ $city }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="f-ilce-{{ $variant }}" class="{{ $label }}">İlçe</label>
            {{-- JS kapalıyken ilçeler il başlıklarıyla gruplu gelir; JS açıkken seçilen ile göre yeniden kurulur --}}
            <select id="f-ilce-{{ $variant }}" name="ilce" x-ref="district" class="{{ $field }}">
                <option value="">Tümü</option>
                @foreach ($locations['districts'] as $city => $districts)
                    <optgroup label="{{ $city }}">
                        @foreach ($districts as $district)
                            <option value="{{ $district }}" @selected(($filters['ilce'] ?? '') === $district)>{{ $district }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>

        @if ($hero)
            {{-- Gelişmiş seçenekler düğmesi: yalnızca JS açıkken görünür (JS kapalıyken alanlar zaten açık) --}}
            <div class="sm:col-span-2">
                <button type="button" @click="more = !more"
                        aria-controls="f-more-hero" aria-expanded="{{ $advancedActive ? 'true' : 'false' }}" :aria-expanded="more.toString()"
                        data-more-toggle
                        class="hidden h-11 w-full items-center justify-between border border-dashed border-line px-4 text-[0.8125rem] font-medium uppercase tracking-[0.12em] text-muted transition-colors hover:border-fg hover:text-fg [.js_&]:flex">
                    <span x-text="more ? 'Daha az seçenek' : 'Daha fazla seçenek'">{{ $advancedActive ? 'Daha az seçenek' : 'Daha fazla seçenek' }}</span>
                    <span class="flex items-center gap-2 normal-case tracking-normal text-subtle">
                        <span x-show="!more" class="text-[0.75rem]">Fiyat · m² · Oda</span>
                        <svg class="size-3.5 transition-transform duration-300" :class="more && 'rotate-180'" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.25" aria-hidden="true"><path d="m2.5 4.5 3.5 3.5 3.5-3.5" /></svg>
                    </span>
                </button>
            </div>
        @endif

        {{--
            Hero'da açılır bölüm. data-cloak: .js sınıfı (head'deki satır içi script) varken ilk boyamada gizli →
            sayfa kayması yok; Alpine başlayınca kaldırılır ve x-show devralır. JS kapalıyken alanlar görünür.
            Panel (yan filtre) varyantında "contents": alanlar doğrudan ana ızgaraya akar, her zaman açık.
        --}}
        <div id="f-more-{{ $variant }}"
             @if ($hero)
                 x-ref="more" x-show="more"
                 x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                 @unless ($advancedActive) data-cloak @endunless
             @endif
             @class(['grid gap-4 sm:col-span-2 sm:grid-cols-2' => $hero, 'contents' => ! $hero])>

        {{-- Fiyat --}}
        <fieldset @class(['sm:col-span-2' => $hero])>
            <legend class="{{ $label }}">Fiyat (₺)</legend>
            <div class="grid grid-cols-2 gap-3">
                <input type="text" inputmode="numeric" name="fiyat_min" value="{{ $filters['fiyat_min'] ?? '' }}" placeholder="En az" aria-label="En az fiyat" class="{{ $field }}" autocomplete="off">
                <input type="text" inputmode="numeric" name="fiyat_max" value="{{ $filters['fiyat_max'] ?? '' }}" placeholder="En çok" aria-label="En çok fiyat" class="{{ $field }}" autocomplete="off">
            </div>
        </fieldset>

        {{-- Metrekare --}}
        <fieldset @class(['sm:col-span-2' => $hero])>
            <legend class="{{ $label }}">Metrekare (brüt)</legend>
            <div class="grid grid-cols-2 gap-3">
                <input type="text" inputmode="numeric" name="m2_min" value="{{ $filters['m2_min'] ?? '' }}" placeholder="En az" aria-label="En az metrekare" class="{{ $field }}" autocomplete="off">
                <input type="text" inputmode="numeric" name="m2_max" value="{{ $filters['m2_max'] ?? '' }}" placeholder="En çok" aria-label="En çok metrekare" class="{{ $field }}" autocomplete="off">
            </div>
        </fieldset>

        {{-- Oda sayısı (arsa vb. tiplerde gizlenir) --}}
        <fieldset x-show="!noRoomTypes.includes(tip)" @class(['sm:col-span-2' => $hero])>
            <legend class="{{ $label }}">Oda sayısı</legend>
            <div class="flex flex-wrap gap-2">
                @foreach (config('real_estate.room_options') as $value => $roomLabel)
                    <label class="cursor-pointer">
                        <input type="checkbox" name="oda[]" value="{{ $value }}" class="peer sr-only" @checked(in_array($value, $rooms, true))>
                        <span class="inline-flex h-10 items-center border border-line px-3.5 text-[0.875rem] transition-colors peer-checked:border-fg peer-checked:bg-fg peer-checked:text-bg peer-focus-visible:outline peer-focus-visible:outline-1 peer-focus-visible:outline-accent">{{ $roomLabel }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>
        </div>{{-- /#f-more --}}

        @unless ($hero)
            <div>
                <label for="f-sort" class="{{ $label }}">Sıralama</label>
                <select id="f-sort" name="siralama" class="{{ $field }}">
                    @foreach (config('real_estate.sort_options') as $key => $sortLabel)
                        <option value="{{ $key }}" @selected(($filters['siralama'] ?? 'yeni') === $key)>{{ $sortLabel }}</option>
                    @endforeach
                </select>
            </div>
        @endunless
    </div>

    <div class="mt-6 flex items-center gap-4">
        <button type="submit" class="group inline-flex h-13 flex-1 items-center justify-center gap-3 bg-fg px-6 text-[0.8125rem] font-medium uppercase tracking-[0.14em] text-bg transition-colors hover:bg-accent hover:text-[#111111]">
            <svg class="size-4" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.25" aria-hidden="true"><circle cx="7" cy="7" r="5" /><path d="m11 11 3.5 3.5" /></svg>
            Portföyde ara
        </button>
        @unless ($hero)
            <a href="{{ $fixedType ? url('/'.$currentType) : route('properties.index') }}" class="text-[0.8125rem] text-muted underline decoration-line underline-offset-4 hover:text-fg">Temizle</a>
        @endunless
    </div>
</form>
