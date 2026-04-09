<x-guest-layout>
    <div class="space-y-5">
        <div>
            <h2 class="font-display text-2xl font-bold text-ink-900">Login</h2>
            <p class="text-sm text-ink-500">Masuk untuk mengakses form dan data sesuai unit.</p>
        </div>

        @if (session('status'))
            <x-alert>{{ session('status') }}</x-alert>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <x-input name="email" type="email" label="Email" />
            <x-input name="password" type="password" label="Password" />

            <label class="flex items-center gap-2 text-sm text-ink-700">
                <input type="checkbox" name="remember">
                <span>Remember me</span>
            </label>

            <div class="flex flex-wrap gap-3">
                <x-button type="submit">Masuk</x-button>
                <x-button :href="route('password.request')" variant="secondary">Lupa Password</x-button>
            </div>
        </form>
    </div>
</x-guest-layout>
