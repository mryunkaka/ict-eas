<x-guest-layout>
    <div class="space-y-5">
        <x-badge variant="warning">Project baseline</x-badge>
        <h2 class="font-display text-3xl font-bold text-ink-900">Arsitektur konsisten untuk multi-unit ICT EAS</h2>
        <p class="text-ink-700">Gunakan login untuk mengakses dashboard, form ICT, approval email, asset, stok, dan project. Semua tampilan utama mengikuti pola komponen Blade dan Tailwind via Vite.</p>
        <div class="flex flex-wrap gap-3">
            <x-button :href="route('login')" onclick="window.location=this.getAttribute('href')">Masuk</x-button>
            <x-button :href="route('register')" onclick="window.location=this.getAttribute('href')" variant="secondary">Registrasi</x-button>
        </div>
    </div>
</x-guest-layout>
