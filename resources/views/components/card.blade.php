@props(['title' => null, 'subtitle' => null, 'variant' => 'default', 'padding' => 'default'])

@php
    $variants = [
        'default' => 'border border-ink-100 bg-white/90 shadow-[0_20px_50px_-30px_rgba(17,32,51,0.35)]',
        'soft' => 'border border-brand-100 bg-brand-50/70',
        'danger' => 'border border-danger-100 bg-white',
    ];

$paddings = [
        'default' => 'p-5',
        'compact' => 'p-3.5',
        'none' => 'p-0',
    ];
@endphp

<article {{ $attributes->class(['rounded-3xl', $paddings[$padding] ?? $paddings['default'], $variants[$variant] ?? $variants['default']]) }}>
    @if ($title || $subtitle)
        <header>
            @if ($title)
                <h3 class="font-display text-base font-semibold text-ink-900">{{ $title }}</h3>
            @endif

            @if ($subtitle)
                <p class="mt-1 text-[13px] text-ink-500">{{ $subtitle }}</p>
            @endif
        </header>
    @endif

    @if (trim($slot))
        <div class="{{ $title || $subtitle ? 'mt-4' : '' }}">
            {{ $slot }}
        </div>
    @endif
</article>
