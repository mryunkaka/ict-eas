<x-app-layout>
    <div class="space-y-6">
        <x-card title="Akses Ditolak" subtitle="Anda tidak bisa mengakses halaman ini.">
            <div class="space-y-4">
                <p class="text-sm text-ink-600">
                    Jika Anda merasa ini seharusnya bisa diakses, silakan hubungi Admin/Asmen/Manager ICT untuk diberikan hak akses.
                </p>
                <div class="flex flex-wrap gap-3">
                    <x-button :href="route('dashboard')" variant="secondary">Kembali ke Dashboard</x-button>
                    <x-button :href="url()->previous()" variant="secondary">Kembali</x-button>
                </div>
            </div>
        </x-card>
    </div>
</x-app-layout>

