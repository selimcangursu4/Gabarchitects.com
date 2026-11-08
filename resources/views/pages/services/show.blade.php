@use('App\Support\StructuredData')
<x-layouts.site :title="$service->meta_title ?: $service->title" :description="$service->meta_description ?: $service->excerpt"
    :image="$service->cover" :schema="[StructuredData::service($service)]">
    <x-site.page-header
        :title="$service->title"
        eyebrow="Hizmet"
        :intro="$service->excerpt"
        :breadcrumbs="[['label' => 'Hizmetler', 'url' => route('services.index')], ['label' => $service->title]]" />

    @if ($service->cover)
        <div class="container-gab mt-12 lg:mt-16">
            <div class="aspect-[16/9] overflow-hidden bg-surface lg:aspect-[21/9]">
                <x-site.picture :media="$service->cover" :alt="$service->title" eager />
            </div>
        </div>
    @endif

    <section class="section-y">
        <div class="container-gab">
            <div class="grid-gab gap-y-12">
                <div class="col-span-4 lg:col-span-7">
                    @if ($service->body)
                        <div class="prose-gab">{!! $service->body !!}</div>
                    @else
                        <p class="text-lead">{{ $service->excerpt }}</p>
                        <p class="mt-6 text-muted">Hizmet ayrıntıları yakında eklenecek. Projeniz hakkında bilgi almak için bizimle iletişime geçebilirsiniz.</p>
                    @endif
                </div>
                <nav aria-label="Diğer hizmetler" class="col-span-4 lg:col-span-4 lg:col-start-9">
                    <h2 class="eyebrow">Diğer hizmetler</h2>
                    <ul class="mt-5 border-t border-line">
                        @foreach ($others as $other)
                            <li class="border-b border-line">
                                <a href="{{ route('services.show', $other) }}" class="flex items-center justify-between py-3.5 text-[0.9375rem] transition-colors hover:text-accent">
                                    {{ $other->title }}
                                    <svg class="size-3.5 opacity-50" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.25" aria-hidden="true"><path d="M1 8h13M9.5 3.5 14 8l-4.5 4.5" /></svg>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            </div>
        </div>
    </section>

    @if ($service->gallery->isNotEmpty())
        <section class="pb-24" aria-label="Galeri">
            <div class="container-gab grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($service->gallery as $image)
                    <figure class="aspect-[4/3] overflow-hidden bg-surface"><x-site.picture :media="$image" :alt="$image->alt ?: $service->title" sizes="(min-width: 1024px) 33vw, 50vw" /></figure>
                @endforeach
            </div>
        </section>
    @endif

    @if ($service->projects->isNotEmpty())
        <section class="border-t border-line section-y" aria-labelledby="service-projects">
            <div class="container-gab">
                <h2 id="service-projects" class="text-h2">Bu hizmetle ilgili projeler</h2>
                <div class="mt-12 grid gap-x-6 gap-y-14 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($service->projects as $project)
                        <x-site.project-card :project="$project" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <x-site.cta-band />
</x-layouts.site>
