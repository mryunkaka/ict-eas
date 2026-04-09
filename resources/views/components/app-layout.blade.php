<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'ICT EAS') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div class="min-h-screen">
            <header class="border-b border-white/70 bg-white/70 backdrop-blur">
                <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-6 py-4">
                    <div>
                        <a href="{{ route('dashboard') }}" class="font-display text-xl font-bold text-ink-900">ICT EAS</a>
                        <p class="text-sm text-ink-500">Sistem Informasi ICT EAS</p>
                    </div>
                    <nav class="flex flex-wrap items-center gap-3 text-sm text-ink-700">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                        <a href="{{ route('forms.ict-requests.index') }}">Form ICT</a>
                        <a href="{{ route('forms.email-requests.index') }}">Email</a>
                        <a href="{{ route('forms.repairs.index') }}">Perbaikan</a>
                        <a href="{{ route('forms.incidents.index') }}">BAK</a>
                        <a href="{{ route('forms.assets.index') }}">Asset</a>
                        <a href="{{ route('inventory.index') }}">Stok</a>
                        <a href="{{ route('forms.projects.index') }}">Project</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-button type="submit" variant="secondary">Logout</x-button>
                        </form>
                    </nav>
                </div>
            </header>

            <main class="mx-auto max-w-7xl px-6 py-10">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
