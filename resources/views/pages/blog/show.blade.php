@use('App\Support\StructuredData')
<x-layouts.site :title="$post->meta_title ?: $post->title" :description="$post->meta_description ?: $post->excerpt"
    :image="$post->cover" type="article" :schema="[StructuredData::article($post)]">
    <article>
        <header class="pt-36 lg:pt-44">
            <div class="container-gab">
                <x-site.breadcrumbs :items="[['label' => 'Blog', 'url' => route('blog.index')], ['label' => $post->title]]" />
                <div class="mx-auto mt-14 max-w-4xl text-center">
                    <p class="eyebrow">
                        <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->translatedFormat('d F Y') }}</time>
                        @if ($post->category) · {{ $post->category->name }} @endif
                        · {{ $post->readingTime() }} dk okuma
                    </p>
                    <h1 class="mt-6 text-h1">{{ $post->title }}</h1>
                    @if ($post->author)
                        <p class="mt-6 text-muted">{{ $post->author->name }}</p>
                    @endif
                </div>
            </div>
        </header>

        @if ($post->cover)
            <div class="container-gab mt-14">
                <div class="aspect-[16/9] overflow-hidden bg-surface"><x-site.picture :media="$post->cover" :alt="$post->title" eager /></div>
            </div>
        @endif

        <div class="container-gab section-y">
            @if ($post->excerpt)
                <p class="mx-auto max-w-[68ch] font-serif text-statement">{{ $post->excerpt }}</p>
            @endif
            <div class="prose-gab mx-auto mt-10">{!! $post->body !!}</div>
        </div>
    </article>

    @if ($related->isNotEmpty())
        <section class="border-t border-line section-y" aria-labelledby="related-posts">
            <div class="container-gab">
                <h2 id="related-posts" class="text-h2">Diğer yazılar</h2>
                <div class="mt-12 grid gap-x-6 gap-y-12 sm:grid-cols-3">
                    @foreach ($related as $item)
                        <a href="{{ route('blog.show', $item) }}" class="group block">
                            <div class="aspect-[4/3] overflow-hidden bg-surface"><x-site.picture :media="$item->cover" :alt="$item->title" sizes="33vw" class="img-zoom" /></div>
                            <h3 class="mt-4 font-serif text-h3">{{ $item->title }}</h3>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.site>
