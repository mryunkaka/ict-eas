<x-app-layout>
    <div class="space-y-6" x-data="{ selected: '{{ (string) request('ict_request_id') }}' }">
        <x-card title="Buat Berita Acara Serah Terima" subtitle="Input manual BA serah terima (asset/non-asset) dan generate PDF.">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm text-ink-600">Pilih Form ICT dulu agar itemnya muncul.</p>
                <x-button :href="route('forms.asset-handovers.index')" variant="secondary">Kembali</x-button>
            </div>
        </x-card>

        <x-card>
            <form method="POST" action="{{ route('forms.asset-handovers.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="block space-y-2">
                        <span class="text-sm font-medium text-ink-700">Form ICT</span>
                        <select
                            name="ict_request_id"
                            x-model="selected"
                            x-on:change="window.location = '{{ route('forms.asset-handovers.create') }}' + '?ict_request_id=' + encodeURIComponent(selected)"
                            class="w-full rounded-2xl border border-ink-200 bg-white px-4 py-3 text-sm text-ink-900 outline-none transition focus:border-brand-500"
                        >
                            <option value="">-- pilih --</option>
                            @foreach ($ictRequests as $req)
                                <option value="{{ $req->id }}" @selected(optional($selectedRequest)->id === $req->id)>
                                    {{ $req->subject ?: ('#'.$req->id) }} ({{ $req->unit?->name ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block space-y-2">
                        <span class="text-sm font-medium text-ink-700">Item</span>
                        <select
                            name="ict_request_item_id"
                            class="w-full rounded-2xl border border-ink-200 bg-white px-4 py-3 text-sm text-ink-900 outline-none transition focus:border-brand-500"
                            @disabled(! $selectedRequest)
                        >
                            <option value="">-- pilih --</option>
                            @foreach ($items as $item)
                                <option value="{{ $item->id }}">
                                    {{ $item->item_name }} ({{ $item->brand_type ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <div class="rounded-3xl border border-ink-100 bg-ink-50/60 p-5">
                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="block space-y-2">
                            <span class="text-sm font-medium text-ink-700">Tipe Serah Terima</span>
                            <select name="handover_type" class="w-full rounded-2xl border border-ink-200 bg-white px-4 py-3 text-sm text-ink-900 outline-none transition focus:border-brand-500">
                                <option value="asset">Asset (buat asset + generate berita acara)</option>
                                <option value="non_asset">Non Asset (tanpa asset)</option>
                            </select>
                        </label>
                        <label class="block space-y-2">
                            <span class="text-sm font-medium text-ink-700">Dept / Lokasi</span>
                            <input name="dept" class="w-full rounded-2xl border border-ink-200 bg-white px-4 py-3 text-sm text-ink-900 outline-none transition focus:border-brand-500" placeholder="Dept / lokasi asset" />
                        </label>
                    </div>

                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <x-input name="model_specification" label="Model / Spesifikasi" placeholder="Model / spesifikasi" />
                        <x-input name="serial_number" label="Serial Number" placeholder="Serial number" />
                        <x-input name="asset_number" label="Asset Number" placeholder="Nomor asset" />
                        <x-input name="recipient_name" label="Nama Penerima" placeholder="Nama penerima" />
                        <x-input name="recipient_position" label="Jabatan Penerima" placeholder="Jabatan penerima" />
                        <x-input name="witness_name" label="Nama Saksi (ICT)" placeholder="Nama saksi" />
                        <x-input name="witness_position" label="Jabatan Saksi" placeholder="Jabatan saksi" />
                        <x-input name="deliverer_name" label="Nama Penyerah (HRGA)" placeholder="Nama penyerah" />
                        <x-input name="deliverer_position" label="Jabatan Penyerah" placeholder="Jabatan penyerah" />
                    </div>

                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <label class="block space-y-2">
                            <span class="text-sm font-medium text-ink-700">Upload Surat Jalan</span>
                            <input type="file" name="surat_jalan" accept=".pdf,.jpg,.jpeg,.png,.webp" data-auto-compress-image="1" class="w-full rounded-2xl border border-ink-200 bg-white px-4 py-3 text-sm text-ink-900 outline-none transition file:mr-2 file:rounded-lg file:border-0 file:bg-ink-100 file:px-2 file:py-1.5 file:text-xs file:font-semibold file:text-ink-700 hover:file:bg-ink-200 focus:border-brand-500" />
                        </label>
                        <label class="block space-y-2">
                            <span class="text-sm font-medium text-ink-700">Upload Foto / Lampiran Serah Terima</span>
                            <input type="file" name="serah_terima" accept=".pdf,.jpg,.jpeg,.png,.webp" data-auto-compress-image="1" class="w-full rounded-2xl border border-ink-200 bg-white px-4 py-3 text-sm text-ink-900 outline-none transition file:mr-2 file:rounded-lg file:border-0 file:bg-ink-100 file:px-2 file:py-1.5 file:text-xs file:font-semibold file:text-ink-700 hover:file:bg-ink-200 focus:border-brand-500" />
                        </label>
                    </div>

                    <div class="mt-4">
                        <x-input name="description" label="Keterangan (non-asset)" placeholder="Keterangan penerimaan barang (untuk non-asset)" />
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <x-button type="submit"><x-heroicon-o-check-circle class="mr-2 h-4 w-4" />Simpan & Generate PDF</x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>

