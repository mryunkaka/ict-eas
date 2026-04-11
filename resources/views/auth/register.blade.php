<x-guest-layout>
    <div class="space-y-6">
        <div class="space-y-2">
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-brand-700">Akun Baru</p>
            <h2 class="font-display text-3xl font-bold text-ink-900 sm:text-4xl">Buat akun awal</h2>
            <p class="text-sm leading-7 text-ink-500">Setelah registrasi, administrator akan memetakan unit dan role pengguna sebelum akun dipakai untuk transaksi operasional.</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf
            <x-select name="unit_id" label="Unit" :options="$units->all()" />
            <x-input name="name" label="Nama" />
            <x-input name="email" type="email" label="Email" placeholder="nama@easgroup.co.id" />
            <x-input name="password" type="password" label="Password" />
            <x-input name="password_confirmation" type="password" label="Konfirmasi Password" />

            <div class="flex flex-col gap-3 sm:flex-row">
                <x-button type="submit">Daftar</x-button>
                <x-button :href="route('login')" variant="secondary">Login</x-button>
            </div>
        </form>
    </div>
</x-guest-layout>
