@props([
    'column',
    'label',
    'sort' => null,
    'direction' => 'asc',
])

@php
    $isActive = $sort === $column;
    $nextDirection = $isActive && $direction === 'asc' ? 'desc' : 'asc';
    $query = array_merge(request()->query(), [
        'sort' => $column,
        'direction' => $nextDirection,
    ]);
@endphp

<a href="{{ url()->current().'?'.http_build_query($query) }}" class="inline-flex items-center gap-2 font-semibold text-ink-600 transition hover:text-ink-900">
    <span>{{ $label }}</span>
    @if ($isActive && $direction === 'asc')
        <x-heroicon-o-chevron-up class="h-4 w-4" />
    @elseif ($isActive && $direction === 'desc')
        <x-heroicon-o-chevron-down class="h-4 w-4" />
    @else
        <x-heroicon-o-arrows-up-down class="h-4 w-4 text-ink-300" />
    @endif
</a>
