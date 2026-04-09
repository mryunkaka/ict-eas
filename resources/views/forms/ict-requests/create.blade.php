<x-app-layout>
    <div class="space-y-6">
        <x-card title="Form Permintaan Fasilitas ICT" subtitle="Draft tersimpan otomatis di browser sampai disubmit atau dihapus">
            <form method="POST" action="{{ route('forms.ict-requests.store') }}" x-data="persistedForm('ict-request-create')" @submit="clearOnSubmit()" class="space-y-5">
                @csrf
                <div class="grid gap-4 md:grid-cols-2">
                    <x-input name="subject" label="Subjek Permintaan" />
                    <x-select name="request_category" label="Kategori" :options="['hardware' => 'Hardware', 'software' => 'Software', 'accessories' => 'Accessories']" />
                    <x-select name="priority" label="Urgensi" :options="['urgent' => 'Urgent', 'normal' => 'Normal']" />
                    <x-input name="needed_at" type="date" label="Dibutuhkan Tanggal" />
                </div>

                <x-textarea name="justification" label="Alasan Kebutuhan" rows="4" />
                <x-textarea name="additional_budget_reason" label="Alasan Tambahan Anggaran" rows="3" />

                <x-card title="Detail Barang">
                    <div class="grid gap-4 md:grid-cols-2">
                        <x-input name="item_name" label="Nama Barang" />
                        <x-input name="brand_type" label="Merk / Tipe" />
                        <x-input name="quantity" type="number" label="Jumlah" value="1" />
                        <x-input name="estimated_price" type="number" label="Estimasi Harga" />
                    </div>
                    <div class="mt-4">
                        <x-textarea name="item_notes" label="Keterangan" rows="3" />
                    </div>
                </x-card>

                <div class="flex flex-wrap gap-3">
                    <x-button type="submit">Submit</x-button>
                    <x-button type="button" variant="secondary" @click="clearDraft()">Clear Data</x-button>
                    <x-button :href="route('forms.ict-requests.index')" variant="secondary">Kembali</x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
