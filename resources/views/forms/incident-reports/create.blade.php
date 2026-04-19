<x-app-layout>
    <div class="ui-page-workspace">
        <x-card title="Form Berita Acara Kejadian" subtitle="Jika CCTV jembatan timbang off, proses timbang harus dihentikan dan BA wajib dibuat" class="mx-auto w-full max-w-5xl">
            <form method="POST" action="{{ route('forms.incidents.store') }}" x-data="persistedForm('incident-report-create')" @submit="clearOnSubmit()" class="space-y-5">
                @csrf
                <div class="grid gap-4 md:grid-cols-2">
                    <x-select name="incident_type" label="Jenis Kejadian" :options="['damage' => 'Kerusakan ICT', 'cctv_outage' => 'CCTV Down', 'network' => 'Gangguan Network']" />
                    <x-input name="title" label="Judul Kejadian" />
                    <x-input name="occurred_at" type="date" label="Tanggal Kejadian" />
                    <x-select name="asset_id" label="Asset Terkait" :options="$assets->pluck('name', 'id')->all()" placeholder="Pilih asset jika ada" />
                    <x-select name="repairable" label="Bisa Diperbaiki" :options="['yes' => 'Ya', 'no' => 'Tidak']" />
                </div>
                <x-textarea name="description" label="Uraian Kejadian" rows="4" />
                <x-textarea name="follow_up" label="Tindak Lanjut" rows="4" />
                <div class="flex flex-wrap items-center gap-3 border-t border-ink-100 pt-4">
                    <x-button type="submit">Submit</x-button>
                    <x-button type="button" variant="secondary" @click="clearDraft()">Clear Data</x-button>
                    <x-button :href="route('forms.incidents.index')" variant="secondary">Kembali</x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
