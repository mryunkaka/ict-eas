<x-app-layout>
    <div class="space-y-6">
        <x-card>
            <form method="GET" class="grid gap-4 md:grid-cols-[minmax(0,1fr)_180px_auto]">
                <x-input name="search" label="Cari Asset" :value="request('search')" />
                <label class="block space-y-2">
                    <span class="text-sm font-medium text-ink-700">Tampilkan</span>
                    <select name="per_page" class="w-full rounded-2xl border border-ink-200 bg-white px-4 py-2.5 text-sm text-ink-900 outline-none transition focus:border-brand-500">
                        @foreach ([10, 20, 30, 50, 100] as $option)
                            <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }} data</option>
                        @endforeach
                    </select>
                </label>
                <div class="flex items-end gap-2">
                    <x-button type="submit"><x-heroicon-o-magnifying-glass class="mr-2 h-4 w-4" />Cari</x-button>
                    <x-button :href="route('forms.assets.index')" variant="secondary"><x-heroicon-o-arrow-path class="mr-2 h-4 w-4" />Reset</x-button>
                </div>
            </form>
        </x-card>
        <x-table>
            <thead class="bg-ink-50 text-left text-ink-500">
                <tr>
                    <th class="px-4 py-3"><x-sort-link column="name" label="Asset" :sort="$sort" :direction="$direction" /></th>
                    <th class="px-4 py-3">Unit</th>
                    <th class="px-4 py-3"><x-sort-link column="serial_number" label="Serial" :sort="$sort" :direction="$direction" /></th>
                    <th class="px-4 py-3"><x-sort-link column="lifecycle_status" label="Lifecycle" :sort="$sort" :direction="$direction" /></th>
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
                        <td class="px-4 py-3">
                            <div class="ui-action-row">
                                <x-button :href="route('forms.assets.show', $asset)" variant="action-neutral" title="Detail">
                                    <x-heroicon-o-eye class="ui-action-icon" />
                                </x-button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-ink-500">Belum ada asset.</td></tr>
                @endforelse
            </tbody>
        </x-table>
        {{ $assets->links() }}
    </div>
</x-app-layout>
