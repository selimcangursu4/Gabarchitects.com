@if ($paginator->hasPages())
    <nav aria-label="Sayfalama" class="mt-16 flex items-center justify-between border-t border-line pt-8">
        @if ($paginator->onFirstPage())
            <span class="eyebrow text-muted" aria-disabled="true">← Önceki</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="eyebrow text-fg hover:text-accent">← Önceki</a>
        @endif

        <ul class="hidden items-center gap-1 sm:flex">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="px-2 text-muted">{{ $element }}</li>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li>
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="flex size-10 items-center justify-center bg-fg text-[0.875rem] text-bg tabular-nums">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="flex size-10 items-center justify-center text-[0.875rem] tabular-nums hover:bg-surface" aria-label="Sayfa {{ $page }}">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach
        </ul>
        <p class="text-[0.875rem] text-muted sm:hidden">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</p>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="eyebrow text-fg hover:text-accent">Sonraki →</a>
        @else
            <span class="eyebrow text-muted" aria-disabled="true">Sonraki →</span>
        @endif
    </nav>
@endif
