<x-app-layout>
    <div class="space-y-6">
        @if (session('status'))
            <x-alert>{{ session('status') }}</x-alert>
        @endif

        <x-card title="{{ $asset->name }}" subtitle="Detail asset, disposal workflow, transfer, dan riwayat lifecycle">
            <div class="grid gap-4 text-sm text-ink-700 md:grid-cols-2">
                <p><strong>Unit:</strong> {{ $asset->unit?->name }}</p>
                <p><strong>Serial:</strong> {{ $asset->serial_number ?: '-' }}</p>
                <p><strong>Kondisi:</strong> {{ strtoupper($asset->condition_status) }}</p>
                <p><strong>Lifecycle:</strong> {{ strtoupper($asset->lifecycle_status) }}</p>
            </div>
        </x-card>

        @if (auth()->user()->isIctAdmin())
            <x-card title="Update Lifecycle" subtitle="Transfer, redistribusi, atau disposal asset">
                <form method="POST" action="{{ route('forms.assets.lifecycle.update', $asset) }}" class="grid gap-4 md:grid-cols-2">
                    @csrf
                    <x-select
                        name="action_type"
                        label="Aksi Lifecycle"
                        :options="[
                            'redistribute' => 'Redistribusi',
                            'transfer' => 'Transfer Unit',
                            'disposal' => 'Disposal',
                        ]"
                    />
                    <x-select name="to_unit_id" label="Unit Tujuan" :options="$units->all()" />
                    <div class="md:col-span-2">
                        <x-textarea name="notes" label="Catatan" rows="3" />
                    </div>
                    <div class="md:col-span-2">
                        <x-button type="submit">Simpan Lifecycle</x-button>
                    </div>
                </form>
            </x-card>
        @endif

        <x-card title="Riwayat Lifecycle" subtitle="Audit trail perpindahan dan disposal asset">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-ink-50 text-left text-ink-500">
                        <tr>
                            <th class="px-4 py-3">Waktu</th>
                            <th class="px-4 py-3">Aksi</th>
                            <th class="px-4 py-3">Dari</th>
                            <th class="px-4 py-3">Ke</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">PIC</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100">
                        @forelse ($asset->lifecycleLogs as $log)
                            <tr>
                                <td class="px-4 py-3">{{ $log->processed_at?->format('d M Y H:i') }}</td>
                                <td class="px-4 py-3">{{ strtoupper($log->action_type) }}</td>
                                <td class="px-4 py-3">{{ $log->fromUnit?->name ?: '-' }}</td>
                                <td class="px-4 py-3">{{ $log->toUnit?->name ?: '-' }}</td>
                                <td class="px-4 py-3">{{ strtoupper($log->previous_status.' -> '.$log->next_status) }}</td>
                                <td class="px-4 py-3">{{ $log->actor?->name }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-6 text-center text-ink-500">Belum ada riwayat lifecycle.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
</x-app-layout>
