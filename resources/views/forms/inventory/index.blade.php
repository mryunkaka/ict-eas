<x-app-layout>
    <div class="space-y-6">
        <x-card title="Stok Barang" subtitle="Stok EAS dan stok unit dapat dipisahkan per scope">
            <div class="flex flex-wrap gap-3">
                <x-button :href="route('inventory.index', ['scope' => 'eas'])" variant="{{ $scope === 'eas' ? 'primary' : 'secondary' }}">Stok Barang EAS</x-button>
                <x-button :href="route('inventory.index', ['scope' => 'unit'])" variant="{{ $scope === 'unit' ? 'primary' : 'secondary' }}">Stok Barang Unit</x-button>
                <x-button :href="route('inventory.index', ['scope' => 'all'])" variant="{{ $scope === 'all' ? 'primary' : 'secondary' }}">Global</x-button>
            </div>
        </x-card>
        <x-table>
            <thead class="bg-ink-50 text-left text-ink-500">
                <tr>
                    <th class="px-4 py-3">Kode</th>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Scope</th>
                    <th class="px-4 py-3">Qty</th>
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
