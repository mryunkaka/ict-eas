@props(['label' => null, 'name', 'rows' => 4, 'hint' => null, 'value' => null])

<label class="block space-y-2">
    @if ($label)
        <span class="text-sm font-medium text-ink-700">{{ $label }}</span>
    @endif

    <textarea name="{{ $name }}" rows="{{ $rows }}" {{ $attributes->class(['w-full rounded-2xl border border-ink-200 bg-white px-4 py-3 text-sm text-ink-900 outline-none transition placeholder:text-ink-500 focus:border-brand-500']) }}>{{ old($name, $value) }}</textarea>

    @error($name)
        <span class="text-sm text-danger-500">{{ $message }}</span>
    @enderror

    @if ($hint)
        <span class="text-xs text-ink-500">{{ $hint }}</span>
    @endif
</label>
