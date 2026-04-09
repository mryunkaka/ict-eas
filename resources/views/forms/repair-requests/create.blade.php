<x-app-layout>
    <x-card title="Form Permohonan Perbaikan Fasilitas ICT" subtitle="Mengikuti struktur FMR-ICT-04">
        <form method="POST" action="{{ route('forms.repairs.store') }}" x-data="persistedForm('repair-request-create')" @submit="clearOnSubmit()" class="space-y-5">
            @csrf
            <div class="grid gap-4 md:grid-cols-2">
                <x-select name="asset_id" label="Asset Terkait" :options="$assets->pluck('name', 'id')->all()" placeholder="Pilih asset jika ada" />
                <x-select name="problem_type" label="Jenis Masalah" :options="['hardware' => 'Hardware', 'software' => 'Software', 'network' => 'Network', 'printer' => 'Printer']" />
                <x-select name="priority" label="Prioritas" :options="['low' => 'Low', 'normal' => 'Normal', 'high' => 'High', 'critical' => 'Critical']" />
                <x-input name="problem_summary" label="Ringkasan Masalah" />
            </div>
            <x-textarea name="troubleshooting_note" label="Gejala / Troubleshooting Awal" rows="4" />
            <div class="flex flex-wrap gap-3">
                <x-button type="submit">Submit</x-button>
                <x-button type="button" variant="secondary" @click="clearDraft()">Clear Data</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
