<x-app-layout>
    <div class="space-y-8">
        <x-card variant="soft">
            <div class="space-y-3">
                <x-badge variant="success">SOP-driven workspace</x-badge>
                <h1 class="font-display text-3xl font-bold text-ink-900">Dashboard ICT EAS</h1>
                <p class="text-ink-700">Modul mengikuti SOP email, internet, disposal hardware, standar asset, CCTV jembatan timbang, dan format FMR-ICT.</p>
            </div>
        </x-card>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($stats as $stat)
                <x-stat-card :label="$stat['label']" :value="$stat['value']" />
            @endforeach
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <x-card title="Menu Form" subtitle="Form transaksi utama sesuai struktur project">
                <div class="grid gap-3 md:grid-cols-2">
                    <x-button :href="route('forms.ict-requests.create')" onclick="window.location=this.getAttribute('href')" variant="secondary">Permintaan Fasilitas ICT</x-button>
                    <x-button :href="route('forms.email-requests.create')" onclick="window.location=this.getAttribute('href')" variant="secondary">Permohonan Email</x-button>
                    <x-button :href="route('forms.repairs.create')" onclick="window.location=this.getAttribute('href')" variant="secondary">Permohonan Perbaikan</x-button>
                    <x-button :href="route('forms.incidents.create')" onclick="window.location=this.getAttribute('href')" variant="secondary">Berita Acara</x-button>
                    <x-button :href="route('forms.assets.index')" onclick="window.location=this.getAttribute('href')" variant="secondary">Master Asset</x-button>
                    <x-button :href="route('forms.projects.create')" onclick="window.location=this.getAttribute('href')" variant="secondary">Pengajuan Project</x-button>
                </div>
            </x-card>

            <x-card title="Kontrol SOP" subtitle="Aturan yang sudah dijadikan baseline sistem">
                <div class="space-y-3 text-sm text-ink-700">
                    <p>Email wajib approval atasan dan verifikasi HRGA sebelum diproses ICT.</p>
                    <p>CCTV timbangan yang down wajib menghentikan timbang dan membuat berita acara.</p>
                    <p>Asset menyimpan serial, user, unit, lokasi, kondisi, dan lifecycle untuk inventarisasi 6 bulanan.</p>
                    <p>Internet dapat diarahkan ke modul kebijakan/log dan pembatasan akses per unit.</p>
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
