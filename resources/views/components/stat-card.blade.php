@props(['label', 'value'])

<x-card>
    <div class="space-y-2">
        <p class="text-sm text-ink-500">{{ $label }}</p>
        <p class="font-display text-3xl font-bold text-ink-900">{{ $value }}</p>
    </div>
</x-card>
