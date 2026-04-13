@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="text-sm text-ink-500">
            Menampilkan {{ $paginator->firstItem() ?? 0 }} - {{ $paginator->lastItem() ?? 0 }} dari {{ $paginator->total() }} data
        </div>

        <div class="flex items-center gap-2">
            @if ($paginator->onFirstPage())
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-ink-100 bg-ink-50 text-ink-300">
                    <x-heroicon-o-chevron-left class="h-4 w-4" />
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-ink-200 bg-white text-ink-700 transition hover:bg-ink-50">
                    <x-heroicon-o-chevron-left class="h-4 w-4" />
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="inline-flex h-10 min-w-10 items-center justify-center rounded-2xl border border-transparent px-3 text-sm text-ink-400">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="inline-flex h-10 min-w-10 items-center justify-center rounded-2xl bg-ink-900 px-3 text-sm font-semibold text-white">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="inline-flex h-10 min-w-10 items-center justify-center rounded-2xl border border-ink-200 bg-white px-3 text-sm font-semibold text-ink-700 transition hover:bg-ink-50">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-ink-200 bg-white text-ink-700 transition hover:bg-ink-50">
                    <x-heroicon-o-chevron-right class="h-4 w-4" />
                </a>
            @else
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-ink-100 bg-ink-50 text-ink-300">
                    <x-heroicon-o-chevron-right class="h-4 w-4" />
                </span>
            @endif
        </div>
    </nav>
@endif
