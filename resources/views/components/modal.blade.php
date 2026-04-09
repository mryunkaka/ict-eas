@props(['show' => false])

<div x-data="{ open: @js($show) }" x-show="open" class="fixed inset-0 z-50 flex items-center justify-center bg-ink-900/40 p-6">
    <div {{ $attributes->class(['w-full max-w-xl rounded-3xl bg-white p-6 shadow-xl']) }}>
        {{ $slot }}
    </div>
</div>
