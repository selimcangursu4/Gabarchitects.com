@props([
    'project',            // App\Models\Project (cover + category eager loaded)
    'index' => null,
    'aspect' => 'aspect-[4/3]',
    'sizes' => '(min-width: 1024px) 50vw, 100vw',
])

<article {{ $attributes->merge(['class' => 'group']) }}>
    <a href="{{ $project->url() }}" class="block focus-visible:outline-offset-8">
        <div class="relative overflow-hidden bg-surface {{ $aspect }}">
            <x-site.picture :media="$project->cover" :alt="$project->title.($project->subtitle ? ' — '.$project->subtitle : '')" :sizes="$sizes" class="img-zoom" />
        </div>

        {{-- Künye şeridi --}}
        <div class="measure-line mt-5 grid grid-cols-[auto_1fr] items-baseline gap-x-5 border-b border-line pb-5">
            @if ($index)
                <span class="eyebrow tabular-nums">{{ str_pad($index, 2, '0', STR_PAD_LEFT) }}</span>
            @endif
            <h3 class="text-h3 {{ $index ? '' : 'col-span-2' }}">{{ $project->title }}</h3>
            @php
                $meta = array_filter([$project->location, $project->category?->name, $project->year]);
            @endphp
            @if ($meta)
                <p class="col-start-2 mt-2 flex flex-wrap gap-x-3 text-[0.875rem] text-muted">
                    @foreach ($meta as $item)
                        @if (! $loop->first)<span aria-hidden="true">·</span>@endif
                        <span>{{ $item }}</span>
                    @endforeach
                </p>
            @endif
        </div>
    </a>
</article>
