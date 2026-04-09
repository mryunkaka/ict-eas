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
        <main class="mx-auto flex min-h-screen max-w-6xl items-center px-6 py-10">
            <div class="grid w-full gap-8 lg:grid-cols-[1.2fr_0.8fr]">
                <x-card variant="soft">
                    <div class="space-y-4">
                        <x-badge variant="success">Laravel 13 + Tailwind v4</x-badge>
                        <h1 class="font-display text-4xl font-bold text-ink-900">Sistem Informasi ICT EAS</h1>
                        <p class="max-w-2xl text-base leading-7 text-ink-700">Platform terstruktur untuk permintaan fasilitas ICT, approval email perusahaan, perbaikan, berita acara kejadian, master asset, stok unit, dan project ICT berbasis SOP internal EAS.</p>
                    </div>
                </x-card>

                <x-card>
                    {{ $slot }}
                </x-card>
            </div>
        </main>
    </body>
</html>
