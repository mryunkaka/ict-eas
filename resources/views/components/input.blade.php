@props(['label' => null, 'name', 'type' => 'text', 'hint' => null, 'value' => null, 'bag' => 'default'])

<label class="block space-y-2">
    @if ($label)
        <span class="text-sm font-medium text-ink-700">{{ $label }}</span>
    @endif

    @if ($type === 'password')
        <div x-data="{ reveal: false }" class="relative">
            <input
                name="{{ $name }}"
                x-bind:type="reveal ? 'text' : 'password'"
                value="{{ old($name, $value) }}"
                {{ $attributes->class(['w-full rounded-2xl border border-ink-200 bg-white px-4 py-2.5 pr-12 text-sm text-ink-900 outline-none ring-0 transition placeholder:text-ink-500 focus:border-brand-500']) }}
            />

            <button
                type="button"
                x-on:click="reveal = !reveal"
                x-bind:aria-label="reveal ? 'Sembunyikan password' : 'Tampilkan password'"
                class="absolute inset-y-0 right-3 inline-flex items-center justify-center text-ink-500 transition hover:text-ink-900"
            >
                <svg x-show="!reveal" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
                <svg x-show="reveal" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.584 10.587A2 2 0 0 0 13.414 13.4" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.363 5.365A9.466 9.466 0 0 1 12 5c4.478 0 8.268 2.943 9.542 7a9.49 9.49 0 0 1-4.043 5.138" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.228 6.228A9.49 9.49 0 0 0 2.458 12c1.274 4.057 5.065 7 9.542 7a9.47 9.47 0 0 0 5.141-1.512" />
                </svg>
            </button>
        </div>
    @else
        <input
            name="{{ $name }}"
            type="{{ $type }}"
            value="{{ old($name, $value) }}"
            {{ $attributes->class(['w-full rounded-2xl border border-ink-200 bg-white px-4 py-2.5 text-sm text-ink-900 outline-none ring-0 transition placeholder:text-ink-500 focus:border-brand-500']) }}
        />
    @endif

    @error($name, $bag)
        <span class="text-sm text-danger-500">{{ $message }}</span>
    @enderror

    @if ($hint)
        <span class="text-xs text-ink-500">{{ $hint }}</span>
    @endif
</label>
