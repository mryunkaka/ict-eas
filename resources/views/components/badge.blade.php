@props(['variant' => 'default'])

@php
    $variants = [
        'default' => 'bg-ink-100 text-ink-700',
        'success' => 'bg-brand-100 text-brand-700',
        'warning' => 'bg-accent-100 text-accent-500',
        'danger' => 'bg-danger-100 text-danger-500',
    ];
@endphp

<span {{ $attributes->class(['inline-flex rounded-full px-3 py-1 text-xs font-semibold', $variants[$variant] ?? $variants['default']]) }}>
    {{ $slot }}
</span>
