@props(['variant' => 'primary', 'type' => 'button', 'href' => null])

@php
    $variants = [
        'primary' => 'bg-ink-900 text-white hover:bg-ink-700',
        'secondary' => 'bg-white text-ink-900 ring-1 ring-ink-200 hover:bg-ink-50',
        'success' => 'bg-brand-500 text-white hover:bg-brand-700',
        'warning' => 'bg-accent-500 text-white hover:opacity-95',
        'danger' => 'bg-danger-500 text-white hover:opacity-95',
        'action-neutral' => 'ui-action-button ui-action-button--neutral',
        'action-pdf' => 'ui-action-button ui-action-button--pdf',
        'action-upload' => 'ui-action-button ui-action-button--upload',
        'action-approve' => 'ui-action-button ui-action-button--approve',
        'action-review' => 'ui-action-button ui-action-button--review',
        'action-danger' => 'ui-action-button ui-action-button--danger',
    ];

    $actionVariants = ['action-neutral', 'action-pdf', 'action-upload', 'action-approve', 'action-review', 'action-danger'];
    $baseClasses = in_array($variant, $actionVariants, true)
        ? ''
        : 'inline-flex shrink-0 items-center justify-center whitespace-nowrap rounded-2xl px-4 py-2.5 text-sm font-semibold transition';
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class([$baseClasses, $variants[$variant] ?? $variants['primary']]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class([$baseClasses, $variants[$variant] ?? $variants['primary']]) }}>
        {{ $slot }}
    </button>
@endif
