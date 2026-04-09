@props(['variant' => 'primary', 'type' => 'button', 'href' => null])

@php
    $variants = [
        'primary' => 'bg-ink-900 text-white hover:bg-ink-700',
        'secondary' => 'bg-white text-ink-900 ring-1 ring-ink-200 hover:bg-ink-50',
        'success' => 'bg-brand-500 text-white hover:bg-brand-700',
        'danger' => 'bg-danger-500 text-white hover:opacity-95',
    ];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class(['inline-flex items-center justify-center rounded-2xl px-4 py-3 text-sm font-semibold transition', $variants[$variant] ?? $variants['primary']]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class(['inline-flex items-center justify-center rounded-2xl px-4 py-3 text-sm font-semibold transition', $variants[$variant] ?? $variants['primary']]) }}>
        {{ $slot }}
    </button>
@endif
