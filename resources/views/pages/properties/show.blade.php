@use('App\Support\StructuredData')
{{-- ÖRNEK ilanlar arama motorlarına kapalı (TD-036) --}}
<x-layouts.site :title="$property->meta_title ?: $property->title" :description="$property->meta_description ?: $property->summary"
    :image="$property->cover" :noindex="$property->is_sample" :schema="[StructuredData::property($property)]">
    @php
        $images = collect([$property->cover])->merge($property->gallery)->filter()->unique('id')->values();
        $specs = array_filter([
            'İşlem' => $property->listing_type->label(),
            'Emlak tipi' => $property->propertyTypeLabel(),
            'Konum' => $property->locationLabel(),
            'Brüt alan' => $property->area_m2 ? number_format($property->area_m2, 0, ',', '.').' m²' : null,
            'Net alan' => $property->net_area_m2 ? number_format($property->net_area_m2, 0, ',', '.').' m²' : null,
            'Oda' => $property->rooms,
            'Banyo' => $property->bathrooms,
            'Durum' => $property->listing_status->label(),
            'İlan no' => $reference,
        ]);
    @endphp

    <section class="pt-28 lg:pt-32">
        <div class="container-gab">
            <x-site.breadcrumbs :items="[
                ['label' => $property->listing_type->label(), 'url' => url('/'.$property->listing_type->slug())],
                ['label' => $property->title],
            ]" />

            @if ($property->is_sample)
                <p class="mt-6 border border-accent/60 px-4 py-3 text-[0.875rem]">Bu bir <strong>örnek ilandır</strong>; yalnızca tasarım ve filtre testi için oluşturulmuştur, gerçek bir portföyü temsil etmez.</p>
            @endif

            {{-- Galeri: büyük görsel + küçük resimler --}}
            <div class="mt-8 grid gap-3 lg:grid-cols-4" x-data="{ active: 0 }">
                <div @class(['relative aspect-[4/3] overflow-hidden bg-surface lg:aspect-[16/10]', 'lg:col-span-3' => $images->count() > 1, 'lg:col-span-4 lg:aspect-[21/9]' => $images->count() <= 1])>
                    @forelse ($images as $image)
                        <div x-show="active === {{ $loop->index }}" @if (! $loop->first) x-cloak @endif class="absolute inset-0">
                            <x-site.picture :media="$image" :alt="$property->title.' — görsel '.$loop->iteration" sizes="(min-width: 1024px) 75vw, 100vw" :eager="$loop->first" />
                        </div>
                    @empty
                        <x-site.picture :media="null" :alt="$property->title" />
                    @endforelse
                </div>
                @if ($images->count() > 1)
                    <ul class="grid grid-cols-4 gap-3 lg:grid-cols-1 lg:content-start">
                        @foreach ($images as $image)
                            <li>
                                <button type="button" @click="active = {{ $loop->index }}" :aria-pressed="(active === {{ $loop->index }}).toString()"
                                        class="block aspect-[4/3] w-full overflow-hidden border border-transparent aria-pressed:border-fg" aria-label="Görsel {{ $loop->iteration }}">
                                    <x-site.picture :media="$image" alt="" sizes="200px" />
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </section>

    <section class="section-y !pt-12 lg:!pt-16">
        <div class="container-gab">
            <div class="grid-gab gap-y-12">
                <div class="col-span-4 lg:col-span-7">
                    <p class="eyebrow">{{ $property->propertyTypeLabel() }} · {{ $property->locationLabel() }}</p>
                    <h1 class="mt-5 text-h2">{{ $property->title }}</h1>

                    @if ($property->summary)
                        <p class="mt-8 text-lead text-muted">{{ $property->summary }}</p>
                    @endif
                    @if ($property->body)
                        <div class="prose-gab mt-8">{!! $property->body !!}</div>
                    @endif

                    <h2 class="mt-14 eyebrow">İlan bilgileri</h2>
                    <dl class="mt-5 grid border-t border-line sm:grid-cols-2">
                        @foreach ($specs as $label => $value)
                            <div class="flex justify-between gap-4 border-b border-line py-4 sm:odd:pr-6 sm:even:pl-6">
                                <dt class="text-muted">{{ $label }}</dt>
                                <dd class="text-right">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                {{-- Fiyat + WhatsApp / arama (yapışkan). İlan sayfasında form yok (kullanıcı kararı). --}}
                <aside class="col-span-4 lg:col-span-4 lg:col-start-9">
                    <div class="border border-line bg-surface p-6 lg:sticky lg:top-28 lg:p-8">
                        <p class="eyebrow">{{ $property->listing_type->label() }} · {{ $reference }}</p>
                        <p class="mt-3 font-serif text-[2.5rem] leading-none">{{ $property->formattedPrice() ?? 'Fiyat için iletişime geçin' }}</p>
                        <hr class="my-8 border-line">
                        <h2 class="font-serif text-h3">Bu ilanla ilgileniyorum</h2>
                        <p class="mt-2 text-[0.9375rem] text-muted">İlan hakkında bilgi almak için bize WhatsApp'tan yazın ya da arayın.</p>
                        <x-site.property-contact :contact="$contact" class="mt-6" />
                    </div>
                </aside>
            </div>
        </div>
    </section>

    {{-- Mobil: ekranın altında sabit WhatsApp / Ara çubuğu --}}
    <div data-bottom-bar class="fixed inset-x-0 bottom-0 z-40 border-t border-line bg-bg/95 px-4 py-3 backdrop-blur-md lg:hidden" style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom))">
        <x-site.property-contact :contact="$contact" compact />
    </div>
    <div class="h-20 lg:hidden" aria-hidden="true"></div>

    @if ($similar->isNotEmpty())
        <section class="border-t border-line section-y" aria-labelledby="similar-title">
            <div class="container-gab">
                <h2 id="similar-title" class="text-h2">Benzer ilanlar</h2>
                <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:gap-6 xl:grid-cols-3">
                    @foreach ($similar as $item)
                        <x-site.property-card :property="$item" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.site>
