@props(['label' => null, 'name', 'options' => [], 'placeholder' => 'Pilih data', 'value' => null, 'bag' => 'default'])

<label class="block space-y-2">
    @if ($label)
        <span class="text-sm font-medium text-ink-700">{{ $label }}</span>
    @endif

    <select name="{{ $name }}" {{ $attributes->class(['w-full rounded-2xl border border-ink-200 bg-white px-4 py-2.5 text-sm text-ink-900 outline-none transition focus:border-brand-500']) }}>
        <option value="">{{ $placeholder }}</option>
        @foreach ($options as $optionValue => $text)
            <option value="{{ $optionValue }}" @selected(old($name, $value) == $optionValue)>{{ $text }}</option>
        @endforeach
    </select>

    @error($name, $bag)
        <span class="text-sm text-danger-500">{{ $message }}</span>
    @enderror
</label>
