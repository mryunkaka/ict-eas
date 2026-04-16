<x-app-layout>
    <div class="space-y-6">
        <x-card>
            <div class="space-y-4">
                <div class="flex flex-wrap gap-3">
                    <x-button :href="route('inventory.index', ['scope' => 'eas'])" variant="{{ $scope === 'eas' ? 'primary' : 'secondary' }}">Stok Barang EAS</x-button>
                    <x-button :href="route('inventory.index', ['scope' => 'unit'])" variant="{{ $scope === 'unit' ? 'primary' : 'secondary' }}">Stok Barang Unit</x-button>
                    <x-button :href="route('inventory.index', ['scope' => 'all'])" variant="{{ $scope === 'all' ? 'primary' : 'secondary' }}">Global</x-button>
                </div>

                <form method="GET" class="grid gap-4 md:grid-cols-[minmax(0,1fr)_180px_auto]">
                    <input type="hidden" name="scope" value="{{ $scope }}">
                    <x-input name="search" label="Cari Stok" :value="$search" />
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
                        <x-button :href="route('inventory.index', ['scope' => $scope])" variant="secondary"><x-heroicon-o-arrow-path class="mr-2 h-4 w-4" />Reset</x-button>
                    </div>
                </form>
            </div>
        </x-card>
        <x-table>
            <thead class="bg-ink-50 text-left text-ink-500">
                <tr>
                    <th class="px-4 py-3"><x-sort-link column="code" label="Kode" :sort="$sort" :direction="$direction" /></th>
                    <th class="px-4 py-3"><x-sort-link column="name" label="Nama" :sort="$sort" :direction="$direction" /></th>
                    <th class="px-4 py-3"><x-sort-link column="scope" label="Scope" :sort="$sort" :direction="$direction" /></th>
                    <th class="px-4 py-3"><x-sort-link column="quantity_on_hand" label="Qty" :sort="$sort" :direction="$direction" /></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @forelse ($items as $item)
                    <tr>
                        <td class="px-4 py-3">{{ $item->code }}</td>
                        <td class="px-4 py-3">{{ $item->name }}</td>
                        <td class="px-4 py-3">{{ strtoupper($item->scope) }}</td>
                        <td class="px-4 py-3">{{ $item->quantity_on_hand }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-ink-500">Belum ada stok.</td></tr>
                @endforelse
            </tbody>
        </x-table>
        {{ $items->links() }}
    </div>
</x-app-layout>
