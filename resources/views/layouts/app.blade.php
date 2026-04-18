<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ICT EAS') }}</title>
        <link rel="preload" as="image" href="{{ asset('images/eas-new.png') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body>
	        @php
	            $user = auth()->user();
                $profileJobTitle = $user?->job_title ?: ($user?->role?->label() ?? '-');

	            $primaryMenu = array_values(array_filter([
	                ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => request()->routeIs('dashboard'), 'icon' => 'heroicon-o-squares-2x2'],
	                ['label' => 'Permintaan ICT', 'route' => 'forms.ict-requests.index', 'active' => request()->routeIs('forms.ict-requests.*'), 'icon' => 'heroicon-o-computer-desktop'],
	                ($user?->canAccessAssetHandovers() ?? false) ? ['label' => 'Serah Terima', 'route' => 'forms.asset-handovers.index', 'active' => request()->routeIs('forms.asset-handovers.*'), 'icon' => 'heroicon-o-document-text'] : null,
	                ['label' => 'Permohonan Email', 'route' => 'forms.email-requests.index', 'active' => request()->routeIs('forms.email-requests.*'), 'icon' => 'heroicon-o-envelope'],
	                ['label' => 'Perbaikan ICT', 'route' => 'forms.repairs.index', 'active' => request()->routeIs('forms.repairs.*'), 'icon' => 'heroicon-o-wrench-screwdriver'],
	                ['label' => 'BAK / Insiden', 'route' => 'forms.incidents.index', 'active' => request()->routeIs('forms.incidents.*'), 'icon' => 'heroicon-o-exclamation-triangle'],
	                ['label' => 'Master Asset', 'route' => 'forms.assets.index', 'active' => request()->routeIs('forms.assets.*'), 'icon' => 'heroicon-o-cube'],
	                ['label' => 'Stok Barang', 'route' => 'inventory.index', 'active' => request()->routeIs('inventory.*'), 'icon' => 'heroicon-o-archive-box'],
	                ['label' => 'Project ICT', 'route' => 'forms.projects.index', 'active' => request()->routeIs('forms.projects.*'), 'icon' => 'heroicon-o-clipboard-document-list'],
	            ]));

            $adminMenu = array_values(array_filter([
                ['label' => 'Report', 'route' => 'reports.index', 'active' => request()->routeIs('reports.index', 'reports.export.*'), 'icon' => 'heroicon-o-chart-bar-square'],
                ['label' => 'Laporan/Rekap Permintaan ICT', 'route' => 'reports.monitoring-pp', 'active' => request()->routeIs('reports.monitoring-pp'), 'icon' => 'heroicon-o-table-cells'],
                ($user?->canManageUsers() ?? false) ? ['label' => 'User Management', 'route' => 'tools.users.index', 'active' => request()->routeIs('tools.users.*'), 'icon' => 'heroicon-o-users'] : null,
	                ($user?->canManageUsers() ?? false) ? ['label' => 'Ping Server', 'route' => 'tools.ping.index', 'active' => request()->routeIs('tools.ping.*'), 'icon' => 'heroicon-o-server-stack'] : null,
	                ($user?->canManageUsers() ?? false) ? ['label' => 'DB Connection', 'route' => 'tools.db-connection.index', 'active' => request()->routeIs('tools.db-connection.*'), 'icon' => 'heroicon-o-circle-stack'] : null,
                    ($user?->canManageUsers() ?? false) ? ['label' => 'SQL Sync', 'route' => 'tools.sql-sync.index', 'active' => request()->routeIs('tools.sql-sync.*'), 'icon' => 'heroicon-o-arrow-down-tray'] : null,
	            ]));

	            $pageTitle = match (true) {
	                request()->routeIs('dashboard') => 'Dashboard',
	                request()->routeIs('reports.monitoring-pp') => 'Monitoring PP',
	                request()->routeIs('reports.*') => 'Report Rekap',
	                request()->routeIs('forms.asset-handovers.*') => 'Berita Acara Serah Terima',
	                request()->routeIs('forms.assets.*') => 'Asset Management',
	                request()->routeIs('inventory.*') => 'Stok Barang',
	                request()->routeIs('forms.projects.*') => 'Project ICT',
	                request()->routeIs('forms.incidents.*') => 'Insiden ICT',
	                request()->routeIs('forms.repairs.*') => 'Perbaikan ICT',
	                request()->routeIs('forms.email-requests.*') => 'Permohonan Email',
	                request()->routeIs('forms.ict-requests.*') => 'Permintaan ICT',
	                request()->routeIs('tools.users.*') => 'User Management',
	                request()->routeIs('tools.ping.*') => 'Ping Server',
	                request()->routeIs('tools.db-connection.*') => 'DB Connection',
                    request()->routeIs('tools.sql-sync.*') => 'SQL Sync',
	                request()->routeIs('profile.*') => 'Profil Pengguna',
	                default => 'ICT EAS Workspace',
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
                                <span class="block text-xs tracking-[0.12em] text-white/60">Sistem Informasi ICT</span>
                            </span>
                        </a>
	                        <button type="button" class="ui-admin-close-button" x-on:click="closeSidebar()">
	                            <x-heroicon-o-x-mark class="h-5 w-5" />
	                        </button>
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
	                        <div class="rounded-2xl border border-white/10 bg-white/5 p-3">
                                <div class="flex items-stretch gap-3">
                                    <div class="min-w-0 flex-1 space-y-1.5">
                                        <div class="truncate text-sm font-semibold text-white">{{ $user?->name ?? 'Guest' }}</div>
                                        <div class="text-xs text-white/70">{{ $profileJobTitle }}</div>
                                        <div class="text-xs text-white/60">Unit: {{ $user?->unit?->name ?? '-' }}</div>
                                    </div>

                                    <div class="w-px self-stretch bg-white/15"></div>

	                                <div class="flex w-[120px] flex-col justify-center gap-1.5">
                                        @if ($user)
	                                        <a href="{{ route('profile.edit') }}" class="ui-sidebar-mini-action">
	                                            <x-heroicon-o-user-circle class="h-4 w-4 shrink-0" />
	                                            <span>Profil</span>
	                                        </a>
	                                        <form method="POST" action="{{ route('logout') }}">
	                                            @csrf
	                                            <button type="submit" class="ui-sidebar-mini-action w-full text-left">
	                                                <x-heroicon-o-arrow-right-on-rectangle class="h-4 w-4 shrink-0" />
	                                                <span>Logout</span>
	                                            </button>
	                                        </form>
                                        @else
                                            <a href="{{ route('login') }}" class="ui-sidebar-mini-action">
                                                <x-heroicon-o-arrow-right-on-rectangle class="h-4 w-4 shrink-0" />
                                                <span>Login</span>
                                            </a>
                                        @endif
	                                </div>
                                </div>
                            </div>
	                    </div>
	                </div>
	            </aside>

            <div class="ui-admin-main" :class="sidebarOpen ? 'lg:pl-[19rem]' : 'lg:pl-0'">
                <div class="ui-admin-scroll">
	                <header class="ui-admin-header">
	                    <div class="ui-admin-header-inner">
	                        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
	                            <div class="flex items-start gap-3">
	                            <button type="button" class="ui-admin-toggle" x-on:click="toggleSidebar()">
	                                <x-heroicon-o-bars-3 class="h-5 w-5" />
	                            </button>
	                            <div class="space-y-1">
	                                <h1 class="font-display text-2xl font-semibold tracking-[-0.03em] text-ink-900">{{ $pageTitle }}</h1>
	                            </div>
	                            </div>
	                            <div class="rounded-2xl border border-ink-100 bg-white/80 px-4 py-2 text-sm text-ink-600 shadow-sm">
	                                <span class="font-medium text-ink-900" x-text="currentWitaLabel"></span>
	                            </div>
	                        </div>
	                    </div>
	                </header>

                <main class="ui-admin-content is-page-scroll-locked">
                    <div class="ui-admin-content-inner is-page-scroll-locked">
                        {{ $slot }}
                    </div>
                </main>
                </div>
            </div>
        </div>
        @stack('scripts')
    </body>
</html>
