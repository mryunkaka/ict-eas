<x-guest-layout>
    <div class="ui-login-content">
        @if (session('status'))
            <x-alert>{{ session('status') }}</x-alert>
        @endif

        <div class="ui-auth-flip-scene">
            <div class="ui-auth-flip-card">
                <section class="ui-auth-face ui-auth-face-front">
                    <form method="POST" action="{{ route('login') }}" class="ui-login-stack">
                        @csrf
                        <x-input name="email" type="email" label="Email Kantor" placeholder="nama@easgroup.co.id" bag="login" class="ui-login-input" />
                        <x-input name="password" type="password" label="Password" placeholder="Masukkan password" bag="login" class="ui-login-input" />

                        <div class="ui-login-inline">
                            <x-checkbox name="remember" label="Ingat saya" class="ui-login-checkbox" />
                            <x-button :href="route('password.request')" variant="secondary" class="ui-login-secondary-button">Lupa Password</x-button>
                        </div>

                        <div class="ui-login-actions">
                            <x-button type="submit" class="ui-login-primary-button">Masuk</x-button>
                            <p class="text-center text-sm text-ink-500">Akun dibuat manual oleh super admin.</p>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
</x-guest-layout>
