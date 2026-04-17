<x-app-layout>
    <div class="space-y-6">
        <x-card title="Akses Ditolak" subtitle="Anda tidak memiliki izin untuk membuka halaman ini.">
            <div class="space-y-4">
                <p class="text-sm text-ink-600">
                    Halaman ini hanya dapat diakses oleh role <strong>Asmen ICT</strong> atau di atasnya.
                </p>
                <div class="flex flex-wrap gap-3">
                    <x-button :href="route('dashboard')" variant="secondary">Kembali ke Dashboard</x-button>
                    <x-button :href="route('forms.ict-requests.index')" variant="secondary">Ke Permintaan ICT</x-button>
                </div>
            </div>
        </x-card>
    </div>
</x-app-layout>

