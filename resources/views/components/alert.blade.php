@props(['variant' => 'success'])

@php
    $variants = [
        'success' => 'border border-brand-100 bg-brand-50 text-brand-700',
        'warning' => 'border border-accent-100 bg-accent-100 text-accent-500',
    ];
@endphp

<div
    x-data="{ visible: true }"
    x-init="window.setTimeout(() => visible = false, 7000)"
    x-show="visible"
    x-transition.opacity.duration.400ms
    {{ $attributes->class(['rounded-2xl px-4 py-3 text-sm', $variants[$variant] ?? $variants['success']]) }}
>
    {{ $slot }}
</div>
