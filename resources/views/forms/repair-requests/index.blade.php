@php
    $problemTypeLabels = [
        'hardware' => 'Hardware',
        'software' => 'Software',
        'network' => 'Network',
        'printer' => 'Printer',
    ];
@endphp

<x-app-layout>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const pageRoot = document.querySelector('.ui-page-workspace');
            const adminScroll = pageRoot?.closest('.ui-admin-scroll');
            const adminContent = pageRoot?.closest('.ui-admin-content');
            const adminContentInner = pageRoot?.closest('.ui-admin-content-inner');

            [adminScroll, adminContent, adminContentInner].forEach((element) => {
                element?.classList.add('is-page-scroll-locked');
            });

            const tableElement = document.getElementById('repairs-table');
            if (!tableElement || typeof window.DataTable === 'undefined') {
                return;
            }

            const viewportHeight = window.innerHeight || document.documentElement.clientHeight || 900;
            const tableTop = tableElement.getBoundingClientRect().top || 0;
            const reservedBottomSpace = 190;
            const tableScrollHeight = Math.max(220, viewportHeight - tableTop - reservedBottomSpace);

            new window.DataTable(tableElement, {
                paging: true,
                searching: true,
                info: false,
                ordering: false,
                lengthChange: false,
                pageLength: 10,
                scrollX: true,
                scrollY: `${tableScrollHeight}px`,
                scrollCollapse: true,
                language: {
                    search: '',
                    searchPlaceholder: 'Cari data...',
                    zeroRecords: 'Data tidak ditemukan',
                    emptyTable: 'Belum ada permohonan perbaikan.',
                    paginate: {
                        first: '«',
                        previous: '‹',
                        next: '›',
                        last: '»',
                    },
                },
            });
        });
    </script>

    <div class="ui-page-workspace ui-page-workspace--flush-top">
        @if (session('status'))
            <x-alert>{{ session('status') }}</x-alert>
        @endif

        <x-card padding="none" class="ui-page-workspace-card">
            <div class="ui-page-toolbar"></div>

            <div class="ui-page-table-content">
                <form id="repairs-filter-form" method="GET" action="{{ route('forms.repairs.index') }}" class="hidden">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    <input type="hidden" name="direction" value="{{ $direction }}">
                </form>

                <div class="ui-page-section-bar">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <label class="inline-flex items-center gap-1.5 text-[11px] font-medium text-ink-600">
                                <span>Dari</span>
                                <input type="date" name="from" form="repairs-filter-form" value="{{ $filters['from'] }}" class="h-7 w-[118px] rounded-lg border border-ink-200 bg-white px-2 text-[11px] text-ink-900 outline-none transition focus:border-brand-500" />
                            </label>
                            <label class="inline-flex items-center gap-1.5 text-[11px] font-medium text-ink-600">
                                <span>Sampai</span>
                                <input type="date" name="until" form="repairs-filter-form" value="{{ $filters['until'] }}" class="h-7 w-[118px] rounded-lg border border-ink-200 bg-white px-2 text-[11px] text-ink-900 outline-none transition focus:border-brand-500" />
                            </label>
                            <x-button type="submit" form="repairs-filter-form" size="compact" class="!px-2.5 !py-1 !text-[11px]">
                                Terapkan
                            </x-button>
                            <x-button :href="route('forms.repairs.index')" variant="secondary" size="compact" class="!px-2.5 !py-1 !text-[11px]">
                                <x-heroicon-o-arrow-path class="mr-1.5 h-3.5 w-3.5" />
                                Reset
                            </x-button>
                            <div class="ui-page-record-count">Total {{ $requests->count() }} data</div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <x-button :href="route('forms.repairs.create')" size="compact" class="!px-2.5 !py-1 !text-[11px]">
                                <x-heroicon-o-plus class="mr-1.5 h-3.5 w-3.5" />
                                Buat Permohonan
                            </x-button>
                        </div>
                    </div>
                </div>

                <div class="ui-datatable-shell">
                    <table id="repairs-table" class="ui-table-compact">
                        <thead>
                            <tr>
                                <th class="ui-table-cell-nowrap">
                                    <x-sort-link column="created_at" label="Tanggal" :sort="$sort" :direction="$direction" size="compact" />
                                </th>
                                <th class="ui-table-cell-nowrap">Pemohon</th>
                                <th class="ui-table-cell-nowrap">
                                    <x-sort-link column="problem_summary" label="Masalah" :sort="$sort" :direction="$direction" size="compact" />
                                </th>
                                <th class="ui-table-cell-nowrap">Jenis</th>
                                <th class="ui-table-cell-nowrap">Asset</th>
                                <th class="ui-table-cell-nowrap">
                                    <x-sort-link column="priority" label="Prioritas" :sort="$sort" :direction="$direction" size="compact" />
                                </th>
                                <th class="ui-table-cell-nowrap">
                                    <x-sort-link column="status" label="Status" :sort="$sort" :direction="$direction" size="compact" />
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100">
                            @foreach ($requests as $repairRequest)
                                <tr>
                                    <td class="ui-table-cell-nowrap text-sm text-ink-700">{{ optional($repairRequest->created_at)->format('d M Y H:i') }}</td>
                                    <td class="ui-table-cell-nowrap font-medium text-ink-900">{{ $repairRequest->requester?->name ?? '-' }}</td>
                                    <td class="max-w-[14rem] truncate text-sm text-ink-800" title="{{ $repairRequest->problem_summary }}">{{ $repairRequest->problem_summary }}</td>
                                    <td class="ui-table-cell-nowrap text-sm text-ink-700">{{ $problemTypeLabels[$repairRequest->problem_type] ?? strtoupper($repairRequest->problem_type) }}</td>
                                    <td class="ui-table-cell-nowrap text-sm text-ink-700">{{ $repairRequest->asset?->name ?? '-' }}</td>
                                    <td class="ui-table-cell-nowrap text-sm text-ink-700">{{ strtoupper($repairRequest->priority) }}</td>
                                    <td class="ui-table-cell-nowrap">
                                        <x-badge size="compact" variant="warning">{{ strtoupper($repairRequest->status) }}</x-badge>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </x-card>
    </div>
</x-app-layout>
