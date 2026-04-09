@props(['label' => null, 'name', 'options' => [], 'placeholder' => 'Pilih data'])

<label class="block space-y-2">
    @if ($label)
        <span class="text-sm font-medium text-ink-700">{{ $label }}</span>
    @endif

    <select name="{{ $name }}" {{ $attributes->class(['w-full rounded-2xl border border-ink-200 bg-white px-4 py-3 text-sm text-ink-900 outline-none transition focus:border-brand-500']) }}>
        <option value="">{{ $placeholder }}</option>
        @foreach ($options as $value => $text)
            <option value="{{ $value }}" @selected(old($name) == $value)>{{ $text }}</option>
        @endforeach
    </select>

    @error($name)
        <span class="text-sm text-danger-500">{{ $message }}</span>
    @enderror
</label>
