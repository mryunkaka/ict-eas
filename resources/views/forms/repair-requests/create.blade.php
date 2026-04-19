<x-app-layout>
    <div class="ui-page-workspace">
        <x-card title="Form Permohonan Perbaikan Fasilitas ICT" subtitle="Mengikuti struktur FMR-ICT-04 · Draft tersimpan otomatis di browser sampai disubmit atau dihapus" class="mx-auto w-full max-w-7xl">
            <form method="POST" action="{{ route('forms.repairs.store') }}" x-data="persistedForm('repair-request-create')" @submit="clearOnSubmit()" class="space-y-5">
                @csrf

                <div class="rounded-3xl border border-ink-100 bg-ink-50/50 p-4">
                    <div class="mb-3">
                        <h3 class="font-display text-sm font-semibold text-ink-700">Detail Permohonan</h3>
                        <p class="mt-0.5 text-xs text-ink-500">Pilih asset terkait, jenis masalah, prioritas, dan jelaskan ringkasan serta gejala awal.</p>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="min-w-0">
                            <x-select name="asset_id" label="Asset Terkait" :options="$assets->pluck('name', 'id')->all()" placeholder="Pilih asset jika ada" />
                        </div>
                        <div class="min-w-0">
                            <x-select name="problem_type" label="Jenis Masalah" :options="['hardware' => 'Hardware', 'software' => 'Software', 'network' => 'Network', 'printer' => 'Printer']" />
                        </div>
                        <div class="min-w-0">
                            <x-select name="priority" label="Prioritas" :options="['low' => 'Low', 'normal' => 'Normal', 'high' => 'High', 'critical' => 'Critical']" />
                        </div>
                        <div class="min-w-0 md:col-span-2">
                            <x-input name="problem_summary" label="Ringkasan Masalah" />
                        </div>
                    </div>
                </div>

                <div class="min-w-0">
                    <x-textarea name="troubleshooting_note" label="Gejala / Troubleshooting Awal" rows="4" />
                </div>

                <div class="flex flex-wrap items-center gap-3 border-t border-ink-100 pt-4">
                    <x-button type="submit">Submit</x-button>
                    <x-button type="button" variant="secondary" @click="clearDraft()">Clear Data</x-button>
                    <x-button :href="route('forms.repairs.index')" variant="secondary">Kembali</x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
