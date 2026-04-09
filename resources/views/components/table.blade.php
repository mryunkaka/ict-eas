<div {{ $attributes->class(['overflow-hidden rounded-3xl border border-ink-100 bg-white']) }}>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-ink-100 text-sm">
            {{ $slot }}
        </table>
    </div>
</div>
