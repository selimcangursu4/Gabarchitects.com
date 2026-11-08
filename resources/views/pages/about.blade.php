<x-layouts.site :title="$page->meta_title ?: 'Hakkımızda'" :description="$page->meta_description ?: $page->field('paragraphs.0')">
    <x-site.page-header
        :title="$page->field('title', 'Hakkımızda')"
        eyebrow="Kurumsal"
        :intro="$page->field('subtitle')"
        :breadcrumbs="[['label' => 'Kurumsal'], ['label' => 'Hakkımızda']]" />

    {{-- Firma tanıtımı --}}
    <section class="section-y">
        <div class="container-gab">
            <div class="grid-gab gap-y-12">
                <div class="col-span-4 lg:col-span-4">
                    <x-site.section-label number="1" label="Biz kimiz" />
                </div>
                <div class="col-span-4 lg:col-span-7 lg:col-start-6">
                    @foreach ($page->field('paragraphs', []) as $paragraph)
                        <p @class(['text-statement font-serif' => $loop->first, 'mt-8 text-lead text-muted' => ! $loop->first])>{{ $paragraph }}</p>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Vizyon / Misyon / Kuruluş hikâyesi (yalnızca girilmiş olanlar) --}}
    @php
        $statements = array_filter([
            'Vizyon' => $page->field('vision'),
            'Misyon' => $page->field('mission'),
            'Hikâyemiz' => $page->field('story'),
        ]);
    @endphp
    @if ($statements)
        <section class="border-t border-line bg-surface section-y">
            {{-- Tailwind sınıfları derleme anında taranır: dinamik "lg:grid-cols-{n}" yerine açık eşleme --}}
            <div class="container-gab grid gap-12 {{ [1 => 'lg:grid-cols-1', 2 => 'lg:grid-cols-2', 3 => 'lg:grid-cols-3'][count($statements)] }}">
                @foreach ($statements as $label => $text)
                    <div data-reveal>
                        <x-site.section-label :number="$loop->iteration + 1" :label="$label" />
                        <p class="mt-8 font-serif text-h3">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Değerler --}}
    @if ($page->field('values'))
        <section class="border-t border-line section-y">
            <div class="container-gab">
                <h2 class="eyebrow">Değerlerimiz</h2>
                <ul class="mt-10 grid border-t border-line sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($page->field('values') as $value)
                        {{-- 4 dar sütunda uzun kelime ("Sürdürülebilirlik") taşmasın: lg'de bir kademe küçük + kırılabilir --}}
                        <li class="min-w-0 break-words border-b border-line py-8 font-serif text-h2 lg:text-h3 sm:odd:pr-6 sm:even:pl-6 lg:px-6 lg:first:pl-0 lg:[&:not(:last-child)]:border-r">{{ $value }}</li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- Ekip (kayıt yoksa bölüm gösterilmez) --}}
    @if ($team->isNotEmpty())
        <section class="border-t border-line section-y" aria-labelledby="team-title">
            <div class="container-gab">
                <h2 id="team-title" class="text-h2">Ekibimiz</h2>
                <div class="mt-12 grid gap-x-6 gap-y-12 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($team as $member)
                        <figure>
                            <div class="aspect-[4/5] overflow-hidden bg-surface"><x-site.picture :media="$member->photo" :alt="$member->name" sizes="(min-width: 1024px) 25vw, 50vw" /></div>
                            <figcaption class="mt-4">
                                <p class="font-serif text-h3">{{ $member->name }}</p>
                                <p class="text-[0.875rem] text-muted">{{ $member->title }}</p>
                            </figcaption>
                        </figure>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- SSS --}}
    @if ($faqs->isNotEmpty())
        <section class="border-t border-line section-y" aria-labelledby="faq-title">
            <div class="container-gab grid-gab gap-y-10">
                <h2 id="faq-title" class="col-span-4 text-h2 lg:col-span-4">Merak edilenler</h2>
                <div class="col-span-4 border-t border-line lg:col-span-7 lg:col-start-6">
                    @foreach ($faqs as $faq)
                        <details class="group border-b border-line">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-6 py-6 font-serif text-[1.375rem] leading-snug [&::-webkit-details-marker]:hidden">
                                {{ $faq->question }}
                                <span class="relative size-4 shrink-0" aria-hidden="true">
                                    <span class="absolute inset-x-0 top-1/2 h-px bg-current"></span>
                                    <span class="absolute inset-y-0 left-1/2 w-px bg-current transition-transform duration-300 group-open:scale-y-0"></span>
                                </span>
                            </summary>
                            <p class="pb-6 text-muted">{{ $faq->answer }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <x-site.cta-band />
</x-layouts.site>
