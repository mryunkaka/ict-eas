@props(['label' => null, 'name', 'options' => [], 'placeholder' => 'Pilih data', 'value' => null, 'bag' => 'default', 'size' => 'default'])

@php
    $labelClasses = [
        'default' => 'block space-y-2',
        'compact' => 'block space-y-1.5',
    ];

    $labelTextClasses = [
        'default' => 'text-sm font-medium text-ink-700',
        'compact' => 'text-xs font-semibold text-ink-700',
    ];

    $selectClasses = [
        'default' => 'h-10 w-full rounded-xl border border-ink-200 bg-white px-3.5 py-0 text-[13px] text-ink-900 outline-none transition focus:border-brand-500',
        'compact' => 'h-8 w-full rounded-xl border border-ink-200 bg-white px-3 py-0 text-xs text-ink-900 outline-none transition focus:border-brand-500',
    ];
@endphp

<label class="{{ $labelClasses[$size] ?? $labelClasses['default'] }}">
    @if ($label)
        <span class="{{ $labelTextClasses[$size] ?? $labelTextClasses['default'] }}">{{ $label }}</span>
    @endif

    <select name="{{ $name }}" {{ $attributes->class([$selectClasses[$size] ?? $selectClasses['default']]) }}>
        <option value="">{{ $placeholder }}</option>
        @foreach ($options as $optionValue => $text)
            <option value="{{ $optionValue }}" @selected(old($name, $value) == $optionValue)>{{ $text }}</option>
        @endforeach
    </select>

    @error($name, $bag)
        <span class="text-sm text-danger-500">{{ $message }}</span>
    @enderror
</label>
