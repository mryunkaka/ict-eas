<x-guest-layout>
    <div class="space-y-5">
        <div>
            <h2 class="font-display text-2xl font-bold text-ink-900">Registrasi</h2>
            <p class="text-sm text-ink-500">Registrasi awal. Mapping role dan unit tetap dikontrol administrator.</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf
            <x-input name="name" label="Nama" />
            <x-input name="email" type="email" label="Email" />
            <x-input name="password" type="password" label="Password" />
            <x-input name="password_confirmation" type="password" label="Konfirmasi Password" />

            <div class="flex flex-wrap gap-3">
                <x-button type="submit">Daftar</x-button>
                <x-button :href="route('login')" variant="secondary">Login</x-button>
            </div>
        </form>
    </div>
</x-guest-layout>
