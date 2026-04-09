<x-app-layout>
    <div class="space-y-6">
        <x-card title="Master Asset" subtitle="Lifecycle asset, inventarisasi, dan pencarian serial">
            <form method="GET" class="grid gap-4 md:grid-cols-[1fr_auto]">
                <x-input name="search" label="Cari Asset" :value="request('search')" />
                <x-button type="submit">Cari</x-button>
            </form>
        </x-card>
        <x-table>
            <thead class="bg-ink-50 text-left text-ink-500">
                <tr>
                    <th class="px-4 py-3">Asset</th>
                    <th class="px-4 py-3">Unit</th>
                    <th class="px-4 py-3">Serial</th>
                    <th class="px-4 py-3">Lifecycle</th>
                    <th class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @forelse ($assets as $asset)
                    <tr>
                        <td class="px-4 py-3">{{ $asset->name }}</td>
                        <td class="px-4 py-3">{{ $asset->unit?->name }}</td>
                        <td class="px-4 py-3">{{ $asset->serial_number }}</td>
                        <td class="px-4 py-3"><x-badge>{{ strtoupper($asset->lifecycle_status) }}</x-badge></td>
                        <td class="px-4 py-3"><x-button :href="route('forms.assets.show', $asset)" variant="secondary">Detail</x-button></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-ink-500">Belum ada asset.</td></tr>
                @endforelse
            </tbody>
        </x-table>
        {{ $assets->links() }}
    </div>
</x-app-layout>
