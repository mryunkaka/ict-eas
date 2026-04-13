@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center gap-2 rounded-2xl border border-ink-100 bg-ink-50 px-4 py-3 text-sm text-ink-300">
                <x-heroicon-o-chevron-left class="h-4 w-4" />
                Sebelumnya
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex items-center gap-2 rounded-2xl border border-ink-200 bg-white px-4 py-3 text-sm font-semibold text-ink-700 transition hover:bg-ink-50">
                <x-heroicon-o-chevron-left class="h-4 w-4" />
                Sebelumnya
            </a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex items-center gap-2 rounded-2xl border border-ink-200 bg-white px-4 py-3 text-sm font-semibold text-ink-700 transition hover:bg-ink-50">
                Berikutnya
                <x-heroicon-o-chevron-right class="h-4 w-4" />
            </a>
        @else
            <span class="inline-flex items-center gap-2 rounded-2xl border border-ink-100 bg-ink-50 px-4 py-3 text-sm text-ink-300">
                Berikutnya
                <x-heroicon-o-chevron-right class="h-4 w-4" />
            </span>
        @endif
    </nav>
@endif
