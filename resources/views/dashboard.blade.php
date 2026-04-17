<x-app-layout>
    <div class="space-y-8">
        <x-card class="ui-dashboard-hero">
            <div class="space-y-4">
                <x-badge variant="success">SOP-driven workspace ts</x-badge>
                <div class="space-y-3">
                    <h1 class="font-display text-3xl font-bold text-white sm:text-4xl">Dashboard ICT EAS</h1>
                    <p class="max-w-3xl text-sm leading-7 text-white/78 sm:text-base">Modul mengikuti SOP email, internet, disposal hardware, standar asset, CCTV jembatan timbang, dan format FMR-ICT.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    @if (auth()->user()->canCreateIctRequest())
                        <x-button :href="route('forms.ict-requests.create')">Buat Permintaan Baru</x-button>
                    @endif
                    <x-button :href="route('reports.index')" variant="secondary" class="bg-white/12 text-white ring-white/20 hover:bg-white/18">Buka Report</x-button>
                </div>
            </div>
        </x-card>

        <div x-data="dashboardStats('{{ route('dashboard.stats') }}')" class="space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="font-display text-xl font-semibold text-ink-900">Ringkasan realtime</h2>
                    <p class="text-sm text-ink-500">Statistik dimuat setelah halaman tampil dan diperbarui otomatis tiap 30 detik.</p>
                </div>
                <button type="button" class="ui-inline-action" x-on:click="load()">
                    <x-heroicon-o-arrow-path class="h-4 w-4" />
                    <span>Muat Ulang</span>
                </button>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @foreach ($statCards as $stat)
                    <a href="{{ $stat['href'] }}" class="ui-metric-card">
                        <div class="flex items-start justify-between gap-4">
                            <div class="space-y-2">
                                <p class="text-sm font-medium text-white/70">{{ $stat['label'] }}</p>
                                <p class="font-display text-3xl font-bold text-white" x-text="formatValue('{{ $stat['key'] }}')"></p>
                            </div>
                            <span class="ui-metric-icon">
                                <x-dynamic-component :component="$stat['icon']" class="h-5 w-5" />
                            </span>
                        </div>
                        <div class="mt-6 flex items-center justify-between text-sm text-white/60">
                            <span x-text="loading ? 'Menyelaraskan data...' : 'Buka detail modul'"></span>
                            <x-heroicon-o-arrow-up-right class="h-4 w-4" />
                        </div>
                    </a>
                @endforeach
            </div>

            <template x-if="error">
                <div class="rounded-3xl border border-danger-100 bg-danger-100/60 px-4 py-3 text-sm text-brand-700">
                    Statistik tidak berhasil dimuat. Coba muat ulang atau cek koneksi server.
                </div>
            </template>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <x-card title="Menu Form" subtitle="Form transaksi utama sesuai struktur project">
                <div class="grid gap-3 md:grid-cols-2">
                    @if (auth()->user()->canCreateIctRequest())
                        <x-button :href="route('forms.ict-requests.create')" onclick="window.location=this.getAttribute('href')" variant="secondary">Permintaan Fasilitas ICT</x-button>
                    @endif
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

        <div class="grid gap-6 lg:grid-cols-3">
            <x-card title="Approval Workflow" subtitle="Alur permintaan ICT bergerak manual sesuai role yang login">
                <div class="space-y-3 text-sm text-ink-700">
                    <p>Permintaan ICT: Admin ICT -> Staff ICT -> Asmen ICT -> Manager ICT -> upload PDF final manual.</p>
                    <p>Tanda tangan tetap manual di luar sistem, lalu file PDF lengkap diunggah ulang.</p>
                    <x-button :href="route('forms.ict-requests.index')">Buka Permintaan ICT</x-button>
                </div>
            </x-card>

            <x-card title="Report & Export" subtitle="Rekap modul dengan filter tanggal, status, PDF, dan Excel">
                <div class="space-y-3 text-sm text-ink-700">
                    <p>Semua modul utama sudah bisa direkap dalam satu halaman report.</p>
                    <x-button :href="route('reports.index')" variant="secondary">Buka Report</x-button>
                </div>
            </x-card>

            <x-card title="Admin Tools" subtitle="User management, ping server, disposal asset, dan log CCTV">
                <div class="space-y-3 text-sm text-ink-700">
                    <p>Super admin mengelola user, role, unit, dan data master; Admin ICT menangani operasional teknis.</p>
                    <div class="flex flex-wrap gap-3">
                        @if (auth()->user()->canManageUsers())
                            <x-button :href="route('tools.users.index')" variant="secondary">Kelola User</x-button>
                        @endif
                        @if (auth()->user()->isIctAdmin() || auth()->user()->isSuperAdmin())
                            <x-button :href="route('tools.ping.index')" variant="secondary">Ping Server</x-button>
                        @endif
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
