@props(['name', 'label', 'checked' => false])

<label class="flex items-center gap-3 text-sm text-ink-700">
    <input
        type="checkbox"
        name="{{ $name }}"
        value="1"
        @checked(old($name, $checked))
        {{ $attributes->class(['h-4 w-4 rounded border border-ink-200 text-brand-500 focus:ring-brand-500']) }}
    >
    <span>{{ $label }}</span>
</label>
