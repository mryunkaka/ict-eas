@props(['variant' => 'default', 'size' => 'default'])

@php
    $variants = [
        'default' => 'bg-ink-100 text-ink-700',
        'success' => 'bg-brand-100 text-brand-700',
        'warning' => 'bg-accent-100 text-accent-500',
        'danger' => 'bg-danger-100 text-danger-500',
    ];

    $sizes = [
        'default' => 'px-2.5 py-1 text-[11px]',
        'compact' => 'px-2 py-0.5 text-[10px]',
    ];
@endphp

<span {{ $attributes->class(['inline-flex rounded-full font-semibold', $sizes[$size] ?? $sizes['default'], $variants[$variant] ?? $variants['default']]) }}>
    {{ $slot }}
</span>
