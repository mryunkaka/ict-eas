<x-app-layout>
    <div class="space-y-6">
        <x-card>
            <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <form method="GET" class="grid gap-4 md:grid-cols-[minmax(0,1fr)_180px_auto]">
                    <x-input name="search" label="Cari" :value="request('search')" placeholder="Form number / subject / asset / serial / penerima" />
                    <label class="block space-y-2">
                        <span class="text-sm font-medium text-ink-700">Tampilkan</span>
                        <select name="per_page" class="w-full rounded-2xl border border-ink-200 bg-white px-4 py-2.5 text-sm text-ink-900 outline-none transition focus:border-brand-500">
                            @foreach ([10, 20, 30, 50, 100] as $option)
                                <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }} data</option>
                            @endforeach
                        </select>
                    </label>
                    <div class="flex flex-wrap items-end gap-2">
                        <x-button type="submit"><x-heroicon-o-magnifying-glass class="mr-2 h-4 w-4" />Cari</x-button>
                        <x-button :href="route('forms.asset-handovers.index')" variant="secondary"><x-heroicon-o-arrow-path class="mr-2 h-4 w-4" />Reset</x-button>
                    </div>
                </form>

                <div class="flex flex-wrap gap-3">
                    <x-button :href="route('forms.asset-handovers.create')"><x-heroicon-o-plus class="mr-2 h-4 w-4" />Buat Manual</x-button>
                </div>
            </div>
        </x-card>

        <div class="overflow-hidden rounded-3xl border border-ink-100 bg-white/90 shadow-[0_20px_50px_-30px_rgba(17,32,51,0.35)]">
            <div class="border-b border-ink-100 p-4 text-sm text-ink-600">
                Total {{ $handovers->total() }} data
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ink-100 text-sm">
                    <thead class="bg-ink-50 text-left text-ink-500">
                        <tr>
                            <th class="px-4 py-3">Form</th>
                            <th class="px-4 py-3">Barang</th>
                            <th class="px-4 py-3">Asset</th>
                            <th class="px-4 py-3">Penerima</th>
                            <th class="px-4 py-3">Dibuat</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100">
                        @forelse ($handovers as $handover)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-ink-900">{{ $handover->ictRequest?->form_number ?? '-' }}</div>
                                    <div class="mt-1 text-xs text-ink-500">{{ $handover->ictRequest?->subject ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-ink-900">{{ $handover->ictRequestItem?->item_name ?? '-' }}</div>
                                    <div class="mt-1 text-xs text-ink-500">{{ $handover->ictRequestItem?->brand_type ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-ink-900">{{ $handover->asset_number ?: '-' }}</div>
                                    <div class="mt-1 text-xs text-ink-500">{{ $handover->serial_number ?: '-' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-ink-900">{{ $handover->recipient_name ?: '-' }}</div>
                                    <div class="mt-1 text-xs text-ink-500">{{ $handover->recipient_position ?: '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-ink-700">
                                    {{ optional($handover->created_at)->format('d M Y H:i') }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="ui-action-row">
                                        @if ($handover->handover_report_path)
                                            <x-button :href="route('forms.asset-handovers.pdf', $handover)" target="_blank" variant="action-pdf" title="PDF">
                                                <x-heroicon-o-document-text class="ui-action-icon" />
                                            </x-button>
                                        @else
                                            <x-badge variant="warning">Belum PDF</x-badge>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-6 text-center text-ink-500">Belum ada berita acara serah terima.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-6">
                {{ $handovers->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
