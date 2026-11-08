<x-layouts.site :title="$category ? $category->name.' — Blog' : 'Blog'" :description="$category?->meta_description">
    <x-site.page-header
        :title="$category?->name ?? 'Blog'"
        eyebrow="Blog & Haberler"
        :breadcrumbs="array_filter([['label' => 'Blog', 'url' => route('blog.index')], $category ? ['label' => $category->name] : null])">
        @if ($categories->isNotEmpty())
            <nav aria-label="Blog kategorileri" class="mt-12 flex flex-wrap gap-2">
                <a href="{{ route('blog.index') }}" @class(['inline-flex h-11 items-center border px-5 text-[0.8125rem] uppercase tracking-[0.14em]', 'border-fg bg-fg text-bg' => ! $category, 'border-line hover:border-fg' => $category])>Tümü</a>
                @foreach ($categories as $cat)
                    <a href="{{ route('blog.category', $cat) }}" @class(['inline-flex h-11 items-center border px-5 text-[0.8125rem] uppercase tracking-[0.14em]', 'border-fg bg-fg text-bg' => $category?->is($cat), 'border-line hover:border-fg' => ! $category?->is($cat)])>{{ $cat->name }}</a>
                @endforeach
            </nav>
        @endif
    </x-site.page-header>

    <section class="section-y">
        <div class="container-gab">
            @if ($posts->isEmpty())
                <div class="py-16 text-center">
                    <p class="font-serif text-h3">Henüz yayınlanmış yazı yok.</p>
                    <p class="mt-4 text-muted">Yazılar yönetim panelinden eklendiğinde burada listelenecek.</p>
                </div>
            @else
                <div class="grid gap-x-6 gap-y-14 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($posts as $post)
                        <article class="group" data-reveal>
                            <a href="{{ route('blog.show', $post) }}" class="block">
                                <div class="aspect-[4/3] overflow-hidden bg-surface">
                                    <x-site.picture :media="$post->cover" :alt="$post->title" sizes="(min-width: 1280px) 30vw, (min-width: 640px) 45vw, 100vw" class="img-zoom" />
                                </div>
                                <p class="eyebrow mt-5">
                                    <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->translatedFormat('d F Y') }}</time>
                                    @if ($post->category) · {{ $post->category->name }} @endif
                                </p>
                                <h2 class="measure-line mt-3 pb-4 font-serif text-h3">{{ $post->title }}</h2>
                                @if ($post->excerpt)
                                    <p class="mt-4 text-muted">{{ \Illuminate\Support\Str::limit($post->excerpt, 160) }}</p>
                                @endif
                            </a>
                        </article>
                    @endforeach
                </div>
                {{ $posts->links() }}
            @endif
        </div>
    </section>
</x-layouts.site>
