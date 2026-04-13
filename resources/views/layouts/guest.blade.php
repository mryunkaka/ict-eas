<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ICT EAS') }}</title>
        <link rel="preload" as="image" href="{{ asset('images/eas-new.png') }}">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        @php
            $isRegisterPage = request()->routeIs('register');
            $isLoginPage = request()->routeIs('login');
        @endphp

        <div class="ui-shell">
            <main class="{{ $isRegisterPage ? 'ui-auth-wrap' : ($isLoginPage ? 'ui-login-wrap' : 'mx-auto w-full max-w-5xl px-4 py-8 sm:px-6 lg:px-8') }}">
                @if ($isRegisterPage)
                    <section class="ui-auth-card">
                        <div class="ui-auth-visual">
                            <span class="ui-dot-grid"></span>
                            <div class="relative space-y-5">
                                <span class="ui-kicker">ICT EAS</span>
                                <div class="space-y-3 text-center">
                                    <h1 class="font-display text-3xl font-bold sm:text-4xl">Registrasi Pengguna</h1>
                                    <p class="mx-auto max-w-md text-sm leading-7 text-white/82 sm:text-base">Lengkapi data awal pengguna dan pilih unit kerja sebelum akun dipetakan lebih lanjut oleh administrator.</p>
                                </div>
                                <div class="ui-auth-logo-stage">
                                    <span class="ui-auth-logo-ring"></span>
                                    <span class="ui-brand-mark h-20 w-20 rounded-[1.75rem]">
                                        <x-application-logo class="h-10 w-10 fill-current" />
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="ui-auth-form">
                            {{ $slot }}
                        </div>
                    </section>
                @elseif ($isLoginPage)
                    <section class="ui-login-card">
                        <div class="ui-login-visual">
                            <span class="ui-dot-grid"></span>
                            <div class="relative flex flex-col items-center justify-start gap-2 pt-2 text-center text-white sm:pt-1">
                                <span class="ui-kicker ui-login-kicker">ICT EAS</span>
                                <span class="ui-brand-mark ui-login-brand-mark">
                                    <x-application-logo class="h-full w-full" />
                                </span>
                                <div class="space-y-0.5">
                                    <p class="ui-login-hero-title">User Access Portal</p>
                                    <p class="ui-login-hero-subtitle">Secure Login Workspace</p>
                                </div>
                            </div>
                        </div>

                        <div class="ui-login-form">
                            {{ $slot }}
                        </div>
                    </section>
                @else
                    <section class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/92 shadow-[0_24px_60px_-28px_rgba(17,32,51,0.28)] backdrop-blur lg:grid lg:grid-cols-[280px_minmax(0,1fr)]">
                        <div class="relative hidden bg-[linear-gradient(160deg,rgba(17,32,51,0.98),rgba(48,68,94,0.92)_58%,rgba(15,159,127,0.88))] lg:flex lg:min-h-[420px] lg:items-center lg:justify-center">
                            <span class="ui-dot-grid"></span>
                            <div class="relative flex flex-col items-center gap-6 px-10 text-center text-white">
                                <span class="ui-kicker">ICT EAS</span>
                                <span class="ui-brand-mark h-24 w-24 rounded-[1.9rem]">
                                    <x-application-logo class="h-12 w-12 fill-current" />
                                </span>
                            </div>
                        </div>

                        <div class="ui-auth-form flex items-center">
                            <div class="w-full">
                                <div class="mb-8 flex items-center gap-4 lg:hidden">
                                    <span class="ui-brand-mark">
                                        <x-application-logo class="h-7 w-7 fill-current" />
                                    </span>
                                    <div>
                                        <p class="font-display text-lg font-semibold text-ink-900">ICT EAS</p>
                                        <p class="text-xs uppercase tracking-[0.18em] text-ink-500">User Login</p>
                                    </div>
                                </div>
                                {{ $slot }}
                            </div>
                        </div>
                    </section>
                @endif
            </main>
        </div>
    </body>
</html>
