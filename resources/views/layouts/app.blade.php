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
            $user = auth()->user();
            $routeName = request()->route()?->getName() ?? 'dashboard';

            $primaryMenu = array_values(array_filter([
                ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => request()->routeIs('dashboard'), 'icon' => 'heroicon-o-squares-2x2'],
                ['label' => 'Permintaan ICT', 'route' => 'forms.ict-requests.index', 'active' => request()->routeIs('forms.ict-requests.*'), 'icon' => 'heroicon-o-computer-desktop'],
                ['label' => 'Permohonan Email', 'route' => 'forms.email-requests.index', 'active' => request()->routeIs('forms.email-requests.*'), 'icon' => 'heroicon-o-envelope'],
                ['label' => 'Perbaikan ICT', 'route' => 'forms.repairs.index', 'active' => request()->routeIs('forms.repairs.*'), 'icon' => 'heroicon-o-wrench-screwdriver'],
                ['label' => 'BAK / Insiden', 'route' => 'forms.incidents.index', 'active' => request()->routeIs('forms.incidents.*'), 'icon' => 'heroicon-o-exclamation-triangle'],
                ['label' => 'Master Asset', 'route' => 'forms.assets.index', 'active' => request()->routeIs('forms.assets.*'), 'icon' => 'heroicon-o-cube'],
                ['label' => 'Stok Barang', 'route' => 'inventory.index', 'active' => request()->routeIs('inventory.*'), 'icon' => 'heroicon-o-archive-box'],
                ['label' => 'Project ICT', 'route' => 'forms.projects.index', 'active' => request()->routeIs('forms.projects.*'), 'icon' => 'heroicon-o-clipboard-document-list'],
            ]));

            $adminMenu = array_values(array_filter([
                $user->canProcessApprovals() ? ['label' => 'Approval Center', 'route' => 'approvals.index', 'active' => request()->routeIs('approvals.*'), 'icon' => 'heroicon-o-check-badge'] : null,
                ['label' => 'Report', 'route' => 'reports.index', 'active' => request()->routeIs('reports.*'), 'icon' => 'heroicon-o-chart-bar-square'],
                $user->canManageUsers() ? ['label' => 'User Management', 'route' => 'tools.users.index', 'active' => request()->routeIs('tools.users.*'), 'icon' => 'heroicon-o-users'] : null,
                $user->canManageUsers() ? ['label' => 'Ping Server', 'route' => 'tools.ping.index', 'active' => request()->routeIs('tools.ping.*'), 'icon' => 'heroicon-o-server-stack'] : null,
            ]));

            [$pageTitle, $pageSubtitle] = match (true) {
                request()->routeIs('dashboard') => ['Dashboard', 'Ringkasan operasional ICT EAS yang dimuat ringan dan responsif.'],
                request()->routeIs('approvals.*') => ['Approval Center', 'Antrian approval lintas fungsi dengan fokus ke aksi prioritas.'],
                request()->routeIs('reports.*') => ['Report Rekap', 'Laporan terpusat untuk analisis, export, dan audit data.'],
                request()->routeIs('forms.assets.*') => ['Asset Management', 'Inventaris, lifecycle, dan histori asset dalam satu workspace.'],
                request()->routeIs('inventory.*') => ['Stok Barang', 'Pantau inventori aktif per unit dan scope operasional.'],
                request()->routeIs('forms.projects.*') => ['Project ICT', 'Pengajuan project dan tracking kebutuhan implementasi.'],
                request()->routeIs('forms.incidents.*') => ['Insiden ICT', 'Catat kejadian, tindak lanjut, dan log maintenance.'],
                request()->routeIs('forms.repairs.*') => ['Perbaikan ICT', 'Kelola ticket kerusakan dan progres penanganan.'],
                request()->routeIs('forms.email-requests.*') => ['Permohonan Email', 'Permohonan internal yang diproses manual oleh role ICT terkait.'],
                request()->routeIs('forms.ict-requests.*') => ['Permintaan ICT', 'Permintaan diproses bertahap hingga penerimaan barang disimpan dan status menjadi Barang Sudah Diterima.'],
                request()->routeIs('tools.users.*') => ['User Management', 'Administrasi akun, role, unit, dan status akses.'],
                request()->routeIs('tools.ping.*') => ['Ping Server', 'Validasi host, port, dan latensi dari panel admin.'],
                request()->routeIs('profile.*') => ['Profil Pengguna', 'Kelola informasi akun dan keamanan login.'],
                default => ['ICT EAS Workspace', 'Admin panel terpadu untuk kebutuhan operasional harian.'],
            };
        @endphp

        <div x-data="adminShell()" class="ui-admin-shell">
            <div
                x-cloak
                x-show="sidebarOpen"
                x-transition.opacity
                class="ui-admin-overlay"
                x-on:click="closeSidebar()"
            ></div>

            <aside
                class="ui-admin-sidebar"
                :class="sidebarOpen ? 'translate-x-0 lg:translate-x-0' : '-translate-x-full lg:-translate-x-full'"
            >
                <div class="ui-admin-sidebar-panel">
                    <div class="ui-admin-brand">
                        <a href="{{ route('dashboard') }}" class="ui-admin-brand-link" x-on:click="if (window.innerWidth < 1024) closeSidebar()">
                            <span class="ui-admin-brand-mark">
                                <x-application-logo class="h-9 w-9" />
                            </span>
                            <span>
                                <span class="block font-display text-lg font-semibold text-white">ICT EAS</span>
                                <span class="block text-xs uppercase tracking-[0.22em] text-white/55">Admin Control</span>
                            </span>
                        </a>
                        <button type="button" class="ui-admin-close-button" x-on:click="closeSidebar()">
                            <x-heroicon-o-x-mark class="h-5 w-5" />
                        </button>
                    </div>

                    <div class="ui-admin-sidebar-headline">
                        <p class="text-sm text-white/70">Sidebar default tertutup agar area kerja langsung lapang saat halaman dibuka.</p>
                    </div>

                    <div x-ref="sidebarScroll" class="ui-admin-sidebar-scroll">
                        <section class="space-y-3">
                            <p class="ui-admin-menu-label">Workspace</p>
                            <nav class="space-y-1.5">
                                @foreach ($primaryMenu as $item)
                                    <a
                                        href="{{ route($item['route']) }}"
                                        class="{{ $item['active'] ? 'ui-admin-nav-item is-active' : 'ui-admin-nav-item' }}"
                                        @if ($item['active']) x-ref="activeNav" @endif
                                        x-on:click="navigateFromSidebar()"
                                    >
                                        <x-dynamic-component :component="$item['icon']" class="h-5 w-5 shrink-0" />
                                        <span>{{ $item['label'] }}</span>
                                    </a>
                                @endforeach
                            </nav>
                        </section>

                        @if ($adminMenu !== [])
                            <section class="space-y-3">
                                <p class="ui-admin-menu-label">Admin Tools</p>
                                <nav class="space-y-1.5">
                                    @foreach ($adminMenu as $item)
                                        <a
                                            href="{{ route($item['route']) }}"
                                            class="{{ $item['active'] ? 'ui-admin-nav-item is-active' : 'ui-admin-nav-item' }}"
                                            @if ($item['active']) x-ref="activeNav" @endif
                                            x-on:click="navigateFromSidebar()"
                                        >
                                            <x-dynamic-component :component="$item['icon']" class="h-5 w-5 shrink-0" />
                                            <span>{{ $item['label'] }}</span>
                                        </a>
                                    @endforeach
                                </nav>
                            </section>
                        @endif
                    </div>

                    <div class="ui-admin-sidebar-footer">
                        <div class="rounded-3xl border border-white/10 bg-white/6 px-4 py-4">
                            <p class="text-sm font-semibold text-white">{{ $user->name }}</p>
                            <p class="mt-1 text-xs uppercase tracking-[0.18em] text-white/45">{{ $user->email }}</p>
                            @if ($user->unit)
                                <p class="mt-3 text-sm text-white/65">{{ $user->unit->name }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </aside>

            <div class="ui-admin-main" :class="sidebarOpen ? 'lg:pl-[19rem]' : 'lg:pl-0'">
                <div class="ui-admin-scroll">
                <header class="ui-admin-header">
                    <div class="ui-admin-header-inner">
                        <div class="flex items-start gap-3">
                            <button type="button" class="ui-admin-toggle" x-on:click="toggleSidebar()">
                                <x-heroicon-o-bars-3 class="h-5 w-5" />
                            </button>
                            <div class="space-y-1">
                                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-brand-700">ICT EAS Control Center</p>
                                <h1 class="font-display text-2xl font-semibold tracking-[-0.03em] text-ink-900">{{ $pageTitle }}</h1>
                                <p class="max-w-2xl text-sm text-ink-500">{{ $pageSubtitle }}</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-end gap-3">
                            <a href="{{ route('profile.edit') }}" class="ui-header-chip">
                                <x-heroicon-o-user-circle class="h-5 w-5" />
                                <span>Profil</span>
                            </a>

                            <x-dropdown align="right" width="56" contentClasses="py-2 ui-admin-dropdown">
                                <x-slot name="trigger">
                                    <button type="button" class="ui-header-profile">
                                        <span class="ui-header-profile-mark">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                        <span class="text-left">
                                            <span class="block text-sm font-semibold text-ink-900">{{ $user->name }}</span>
                                            <span class="block text-xs uppercase tracking-[0.18em] text-ink-500">{{ $routeName }}</span>
                                        </span>
                                        <x-heroicon-o-chevron-down class="h-4 w-4 text-ink-500" />
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <div class="px-2">
                                        <a href="{{ route('dashboard') }}" class="ui-admin-dropdown-link">Dashboard</a>
                                        <a href="{{ route('profile.edit') }}" class="ui-admin-dropdown-link">Profil</a>
                                        <form method="POST" action="{{ route('logout') }}" class="pt-1">
                                            @csrf
                                            <button type="submit" class="ui-admin-dropdown-link w-full text-left text-brand-700">Logout</button>
                                        </form>
                                    </div>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    </div>
                </header>

                <main class="ui-admin-content">
                    <div class="ui-admin-content-inner">
                        {{ $slot }}
                    </div>
                </main>
                </div>
            </div>
        </div>
    </body>
</html>
