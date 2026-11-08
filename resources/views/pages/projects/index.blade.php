<x-layouts.site :title="$category ? $category->name.' Projeleri' : 'Projeler'" :description="$category?->meta_description">
    <x-site.page-header
        :title="$category ? $category->name : 'Projelerimiz'"
        eyebrow="Projeler"
        intro="Her proje; fizibilite analizinden proje geliştirmeye, yatırım danışmanlığından uygulama yönetimine kadar bütüncül bir anlayışla yönetilir."
        :breadcrumbs="array_filter([
            ['label' => 'Projeler', 'url' => route('projects.index')],
            $category ? ['label' => $category->name] : null,
        ])">
        @if ($categories->isNotEmpty())
            <nav aria-label="Proje kategorileri" class="mt-12 flex flex-wrap gap-2 lg:mt-16">
                <a href="{{ route('projects.index') }}" @if (! $category) aria-current="page" @endif
                   @class(['inline-flex h-11 items-center border px-5 text-[0.8125rem] font-medium uppercase tracking-[0.14em] transition-colors',
                           'border-fg bg-fg text-bg' => ! $category, 'border-line hover:border-fg' => $category])>Tümü</a>
                @foreach ($categories as $cat)
                    <a href="{{ route('projects.category', $cat) }}" @if ($category?->is($cat)) aria-current="page" @endif
                       @class(['inline-flex h-11 items-center border px-5 text-[0.8125rem] font-medium uppercase tracking-[0.14em] transition-colors',
                               'border-fg bg-fg text-bg' => $category?->is($cat), 'border-line hover:border-fg' => ! $category?->is($cat)])>{{ $cat->name }}</a>
                @endforeach
            </nav>
        @endif
    </x-site.page-header>

    {{-- Devam eden projeler (talep 3) — yalnızca kayıt varsa --}}
    @if ($ongoing->isNotEmpty() && $projects->onFirstPage())
        <section class="section-y border-b border-line" aria-labelledby="ongoing-title">
            <div class="container-gab">
                <h2 id="ongoing-title" class="flex items-center gap-4 text-h2">
                    Devam eden projeler
                    <span class="eyebrow tabular-nums">({{ $ongoing->count() }})</span>
                </h2>
                <div class="mt-12 grid gap-x-6 gap-y-14 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($ongoing as $project)
                        <x-site.project-card :project="$project" sizes="(min-width: 1280px) 30vw, (min-width: 640px) 45vw, 100vw" data-reveal />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="section-y" aria-labelledby="all-projects-title">
        <div class="container-gab">
            <h2 id="all-projects-title" @class(['text-h2', 'sr-only' => $ongoing->isEmpty()])>
                {{ $ongoing->isNotEmpty() ? 'Tamamlanan projeler' : 'Tüm projeler' }}
            </h2>

            @if ($projects->isEmpty())
                <p class="py-16 text-center text-muted">Bu kategoride henüz yayınlanmış proje yok.</p>
            @else
                <div @class(['grid gap-x-6 gap-y-14 sm:grid-cols-2 xl:grid-cols-3', 'mt-12' => $ongoing->isNotEmpty()])>
                    @foreach ($projects as $project)
                        <x-site.project-card :project="$project" :index="$projects->firstItem() + $loop->index"
                                             sizes="(min-width: 1280px) 30vw, (min-width: 640px) 45vw, 100vw" data-reveal />
                    @endforeach
                </div>
                {{ $projects->links() }}
            @endif
        </div>
    </section>
</x-layouts.site>
