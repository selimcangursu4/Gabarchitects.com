@use('App\Support\StructuredData')
<x-layouts.site :title="$project->meta_title ?: $project->title" :description="$project->meta_description ?: $project->summary" :over-hero="(bool) $project->cover"
    :image="$project->cover" :schema="[StructuredData::project($project)]">
    @php
        $facts = array_filter([
            'Konum' => $project->location,
            'Kategori' => $project->category?->name,
            'Yıl' => $project->year,
            'Alan' => $project->area_m2 ? number_format($project->area_m2, 0, ',', '.').' m²' : null,
            'Durum' => $project->progress?->label(),
            'Hizmet' => $project->services->pluck('title')->implode(', ') ?: null,
        ]);
    @endphp

    {{-- Tam ekran kapak --}}
    <section @class(['relative isolate flex items-end overflow-hidden', 'min-h-[88svh] bg-[#111111] text-on-image' => $project->cover, 'pt-40' => ! $project->cover])>
        @if ($project->cover)
            <div class="absolute inset-0 -z-10">
                <x-site.picture :media="$project->cover" :alt="$project->title" eager class="hero-image" />
                <div class="absolute inset-0 bg-[linear-gradient(to_bottom,rgba(17,17,17,0.5)_0%,rgba(17,17,17,0)_25%,rgba(17,17,17,0)_50%,rgba(17,17,17,0.85)_100%)]"></div>
            </div>
        @endif
        <div class="container-gab w-full pb-12 lg:pb-16">
            <x-site.breadcrumbs :items="[['label' => 'Projeler', 'url' => route('projects.index')], ['label' => $project->title]]"
                :on-image="(bool) $project->cover" @class(['text-on-image/80' => $project->cover]) />
            <h1 class="mt-8 max-w-[16ch] text-h1">{{ $project->title }}</h1>
            @if ($project->subtitle)
                <p class="mt-6 text-lead opacity-85">{{ $project->subtitle }}</p>
            @endif
        </div>
    </section>

    {{-- Künye şeridi + açıklama --}}
    <section class="section-y">
        <div class="container-gab">
            <div class="grid-gab gap-y-12">
                <dl class="col-span-4 border-t border-line lg:col-span-4">
                    @foreach ($facts as $label => $value)
                        <div class="flex justify-between gap-4 border-b border-line py-4">
                            <dt class="eyebrow self-center">{{ $label }}</dt>
                            <dd class="text-right">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
                <div class="col-span-4 lg:col-span-7 lg:col-start-6">
                    @if ($project->summary)
                        <p class="text-statement font-serif">{{ $project->summary }}</p>
                    @endif
                    @if ($project->body)
                        <div class="prose-gab mt-10 text-muted">{!! $project->body !!}</div>
                    @endif
                    @if (! $project->summary && ! $project->body)
                        <p class="text-lead text-muted">Proje detayları yakında eklenecek.</p>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Editoryal galeri: tam genişlik / iki kolon ritmi (sıra CMS'ten) --}}
    @if ($project->gallery->isNotEmpty())
        <section class="pb-24 lg:pb-32" aria-label="Proje galerisi">
            <div class="container-gab grid gap-4 sm:grid-cols-2 lg:gap-6">
                @foreach ($project->gallery as $image)
                    @php
                        $wide = $loop->index % 3 === 0;
                    @endphp
                    <figure @class(['overflow-hidden bg-surface', 'sm:col-span-2 aspect-[16/9]' => $wide, 'aspect-[4/5]' => ! $wide]) data-reveal>
                        <x-site.picture :media="$image" :alt="$image->alt ?: $project->title.' — görsel '.$loop->iteration" :sizes="$wide ? '100vw' : '(min-width: 640px) 50vw, 100vw'" />
                    </figure>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Önceki / sonraki --}}
    <nav aria-label="Proje gezinme" class="border-y border-line">
        <div class="container-gab grid sm:grid-cols-2">
            <div class="border-line py-10 sm:border-r sm:pr-8">
                @if ($previous)
                    <a href="{{ $previous->url() }}" class="group block">
                        <span class="eyebrow">← Önceki proje</span>
                        <span class="mt-3 block font-serif text-h3 transition-transform duration-500 group-hover:-translate-x-1">{{ $previous->title }}</span>
                    </a>
                @endif
            </div>
            <div class="py-10 text-right sm:pl-8">
                @if ($next)
                    <a href="{{ $next->url() }}" class="group block">
                        <span class="eyebrow">Sonraki proje →</span>
                        <span class="mt-3 block font-serif text-h3 transition-transform duration-500 group-hover:translate-x-1">{{ $next->title }}</span>
                    </a>
                @endif
            </div>
        </div>
    </nav>

    @if ($related->isNotEmpty())
        <section class="section-y" aria-labelledby="related-title">
            <div class="container-gab">
                <h2 id="related-title" class="text-h2">İlgili projeler</h2>
                <div class="mt-12 grid gap-x-6 gap-y-14 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($related as $item)
                        <x-site.project-card :project="$item" sizes="(min-width: 1280px) 30vw, (min-width: 640px) 45vw, 100vw" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.site>
