@php
    $initialAuthMode = request('mode') === 'register' || $errors->register->any() ? 'register' : 'login';
@endphp

<x-guest-layout>
    <div x-data="{ mode: '{{ $initialAuthMode }}' }" class="ui-login-content">
        @if (session('status'))
            <x-alert>{{ session('status') }}</x-alert>
        @endif

        <div class="ui-auth-flip-scene" :class="{ 'is-flipped': mode === 'register', 'is-register': mode === 'register' }">
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
                            @if (Route::has('register'))
                                <button type="button" class="ui-login-switch" x-on:click="mode = 'register'">
                                    Belum punya akun? Registrasi
                                </button>
                            @endif
                        </div>
                    </form>
                </section>

                <section class="ui-auth-face ui-auth-face-back">
                    <form method="POST" action="{{ route('register') }}" class="ui-login-stack ui-login-stack-register">
                        @csrf
                        <x-select name="unit_id" label="Unit" :options="$units->all()" bag="register" class="ui-login-input ui-login-select" />
                        <x-input name="name" label="Nama" bag="register" class="ui-login-input" />
                        <x-input name="email" type="email" label="Email Kantor" placeholder="nama@easgroup.co.id" bag="register" class="ui-login-input" />
                        <x-input name="password" type="password" label="Password" bag="register" class="ui-login-input" />
                        <x-input name="password_confirmation" type="password" label="Konfirmasi Password" bag="register" class="ui-login-input" />

                        <div class="ui-login-actions">
                            <x-button type="submit" class="ui-login-primary-button">Registrasi</x-button>
                            <button type="button" class="ui-login-switch" x-on:click="mode = 'login'">
                                Sudah punya akun? Login
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
</x-guest-layout>
