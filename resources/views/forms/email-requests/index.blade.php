@php
    $canBulkDelete = auth()->user()->isIctAdmin() || auth()->user()->isSuperAdmin();
    $pageIds = $requests->pluck('id')->map(fn ($id) => (string) $id)->values();
    $fullTtdUploadUrlTemplate = route('forms.email-requests.upload-full-ttd', ['emailRequest' => '__ID__']);
@endphp

<x-app-layout>
    <script>
        function emailRequestsData() {
            return {
                pageIds: @js($pageIds),
                selectedIds: [],
                get allSelectedOnPage() {
                    const visibleIds = this.getVisiblePageIds();
                    return visibleIds.length > 0 && visibleIds.every((id) => this.selectedIds.includes(id));
                },
                toggleSelection(id, checked) {
                    const value = String(id);
                    if (checked) {
                        this.selectedIds = Array.from(new Set([...this.selectedIds, value]));
                    } else {
                        this.selectedIds = this.selectedIds.filter((itemId) => itemId !== value);
                    }
                },
                getVisiblePageIds() {
                    return Array.from(document.querySelectorAll('#email-requests-table tbody input[data-select-id]'))
                        .filter((el) => el.offsetParent !== null)
                        .map((el) => String(el.getAttribute('data-select-id') ?? ''))
                        .filter(Boolean);
                },
                togglePageSelection(event) {
                    const visibleIds = this.getVisiblePageIds();
                    if (!visibleIds.length) return;
                    if (event.target.checked) {
                        this.selectedIds = Array.from(new Set([...this.selectedIds, ...visibleIds]));
                    } else {
                        this.selectedIds = this.selectedIds.filter((id) => !visibleIds.includes(id));
                    }
                },
                clearSelection() {
                    this.selectedIds = [];
                },
                fullTtdModalOpen: false,
                fullTtdFormAction: '',
                fullTtdDate: '',
                openFullTtdModal(payload) {
                    this.fullTtdFormAction = this.buildFullTtdAction(payload.id);
                    this.fullTtdDate = payload.date || new Date().toISOString().slice(0, 10);
                    this.fullTtdModalOpen = true;
                },
                closeFullTtdModal() {
                    this.fullTtdModalOpen = false;
                    this.fullTtdFormAction = '';
                    this.fullTtdDate = '';
                },
                buildFullTtdAction(id) {
                    return @js($fullTtdUploadUrlTemplate).replace('__ID__', String(id));
                },
            };
        }

        document.addEventListener('DOMContentLoaded', () => {
            const pageRoot = document.querySelector('.ui-page-workspace');
            const adminScroll = pageRoot?.closest('.ui-admin-scroll');
            const adminContent = pageRoot?.closest('.ui-admin-content');
            const adminContentInner = pageRoot?.closest('.ui-admin-content-inner');

            [adminScroll, adminContent, adminContentInner].forEach((element) => {
                element?.classList.add('is-page-scroll-locked');
            });

            const tableElement = document.getElementById('email-requests-table');
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
                    emptyTable: 'Belum ada data permohonan email.',
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

    <div x-data="emailRequestsData()" class="ui-page-workspace ui-page-workspace--flush-top">
        @if (session('status'))
            <x-alert>{{ session('status') }}</x-alert>
        @endif

        <div
            x-show="fullTtdModalOpen"
            x-cloak
            x-transition.opacity.duration.200ms
            x-on:keydown.escape.window="closeFullTtdModal()"
            class="fixed inset-0 z-50 flex items-center justify-center bg-ink-900/50 p-4"
        >
            <div class="w-full max-w-lg rounded-3xl bg-white p-5 shadow-2xl">
                <h3 class="font-display text-lg font-semibold text-ink-900">Upload Full TTD</h3>
                <p class="mt-1 text-sm text-ink-500">Isi tanggal dan lampirkan file PDF permohonan email yang sudah lengkap ditandatangani (file gambar atau PDF). File akan diproses otomatis.</p>
                <form method="POST" :action="fullTtdFormAction" enctype="multipart/form-data" class="mt-4 space-y-4">
                    @csrf
                    <input type="hidden" name="from" value="{{ $filters['from'] }}">
                    <input type="hidden" name="until" value="{{ $filters['until'] }}">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    <input type="hidden" name="direction" value="{{ $direction }}">

                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-ink-500">Tanggal</label>
                        <input
                            type="date"
                            name="ttd_date"
                            x-model="fullTtdDate"
                            required
                            class="w-full rounded-2xl border border-ink-200 px-3 py-2 text-sm text-ink-900 focus:border-brand-500 focus:outline-none"
                        />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-ink-500">File Full TTD</label>
                        <input
                            type="file"
                            name="ttd_file"
                            accept=".pdf,.jpg,.jpeg,.png,.webp,image/*,application/pdf"
                            required
                            class="w-full rounded-2xl border border-ink-200 px-3 py-2 text-sm text-ink-900 focus:border-brand-500 focus:outline-none"
                        />
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <x-button type="button" variant="secondary" x-on:click="closeFullTtdModal()">Batal</x-button>
                        <x-button type="submit">Upload</x-button>
                    </div>
                </form>
            </div>
        </div>

        <x-card padding="none" class="ui-page-workspace-card">
            <div class="ui-page-toolbar"></div>

            <div class="ui-page-table-content">
                <form id="email-requests-filter-form" method="GET" action="{{ route('forms.email-requests.index') }}" class="hidden">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    <input type="hidden" name="direction" value="{{ $direction }}">
                </form>

                @if ($canBulkDelete)
                    <form method="POST" action="{{ route('forms.email-requests.bulk-destroy') }}" class="ui-page-section-bar" x-on:submit="if (selectedIds.length === 0) { $event.preventDefault(); } else if (!confirm('Hapus data yang dipilih?')) { $event.preventDefault(); }">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="from" value="{{ $filters['from'] }}">
                        <input type="hidden" name="until" value="{{ $filters['until'] }}">
                        <input type="hidden" name="sort" value="{{ $sort }}">
                        <input type="hidden" name="direction" value="{{ $direction }}">

                        <template x-for="id in selectedIds" :key="id">
                            <input type="hidden" name="selected_ids[]" :value="id">
                        </template>

                        <div class="flex flex-wrap items-center justify-between gap-3 text-sm text-ink-700" x-cloak x-show="selectedIds.length > 0">
                            <div class="flex flex-wrap items-center gap-2">
                                <label class="inline-flex items-center gap-1.5 text-[11px] font-medium text-ink-600">
                                    <span>Dari</span>
                                    <input type="date" name="from" form="email-requests-filter-form" value="{{ $filters['from'] }}" class="h-7 w-[118px] rounded-lg border border-ink-200 bg-white px-2 text-[11px] text-ink-900 outline-none transition focus:border-brand-500" />
                                </label>
                                <label class="inline-flex items-center gap-1.5 text-[11px] font-medium text-ink-600">
                                    <span>Sampai</span>
                                    <input type="date" name="until" form="email-requests-filter-form" value="{{ $filters['until'] }}" class="h-7 w-[118px] rounded-lg border border-ink-200 bg-white px-2 text-[11px] text-ink-900 outline-none transition focus:border-brand-500" />
                                </label>
                                <x-button type="submit" form="email-requests-filter-form" size="compact" class="!px-2.5 !py-1 !text-[11px]">
                                    Terapkan
                                </x-button>
                                <x-button :href="route('forms.email-requests.index')" variant="secondary" size="compact" class="!px-2.5 !py-1 !text-[11px]">
                                    <x-heroicon-o-arrow-path class="mr-1.5 h-3.5 w-3.5" />
                                    Reset
                                </x-button>
                                <x-button :href="route('forms.email-requests.create')" size="compact" class="!px-2.5 !py-1 !text-[11px]">
                                    <x-heroicon-o-plus class="mr-1.5 h-3.5 w-3.5" />
                                    Buat Permohonan
                                </x-button>
                                <button type="submit" class="ui-page-danger-button !px-2.5 !py-1 !text-[11px]">
                                    <x-heroicon-o-trash class="mr-1.5 h-3.5 w-3.5" />
                                    Delete selected
                                </button>
                            </div>

                            <div class="flex flex-wrap items-center gap-4">
                                <span class="text-ink-600" x-text="`${selectedIds.length} records selected`"></span>
                                <button type="button" x-on:click="clearSelection()" class="text-danger-600 hover:underline">Deselect all</button>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3" x-cloak x-show="selectedIds.length === 0">
                            <div class="flex flex-wrap items-center gap-2">
                                <label class="inline-flex items-center gap-1.5 text-[11px] font-medium text-ink-600">
                                    <span>Dari</span>
                                    <input type="date" name="from" form="email-requests-filter-form" value="{{ $filters['from'] }}" class="h-7 w-[118px] rounded-lg border border-ink-200 bg-white px-2 text-[11px] text-ink-900 outline-none transition focus:border-brand-500" />
                                </label>
                                <label class="inline-flex items-center gap-1.5 text-[11px] font-medium text-ink-600">
                                    <span>Sampai</span>
                                    <input type="date" name="until" form="email-requests-filter-form" value="{{ $filters['until'] }}" class="h-7 w-[118px] rounded-lg border border-ink-200 bg-white px-2 text-[11px] text-ink-900 outline-none transition focus:border-brand-500" />
                                </label>
                                <x-button type="submit" form="email-requests-filter-form" size="compact" class="!px-2.5 !py-1 !text-[11px]">
                                    Terapkan
                                </x-button>
                                <x-button :href="route('forms.email-requests.index')" variant="secondary" size="compact" class="!px-2.5 !py-1 !text-[11px]">
                                    <x-heroicon-o-arrow-path class="mr-1.5 h-3.5 w-3.5" />
                                    Reset
                                </x-button>
                                <div class="ui-page-record-count">Total {{ $requests->count() }} data</div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <x-button :href="route('forms.email-requests.create')" size="compact" class="!px-2.5 !py-1 !text-[11px]">
                                    <x-heroicon-o-plus class="mr-1.5 h-3.5 w-3.5" />
                                    Buat Permohonan
                                </x-button>
                            </div>
                        </div>
                    </form>
                @else
                    <div class="ui-page-section-bar">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <label class="inline-flex items-center gap-1.5 text-[11px] font-medium text-ink-600">
                                    <span>Dari</span>
                                    <input type="date" name="from" form="email-requests-filter-form" value="{{ $filters['from'] }}" class="h-7 w-[118px] rounded-lg border border-ink-200 bg-white px-2 text-[11px] text-ink-900 outline-none transition focus:border-brand-500" />
                                </label>
                                <label class="inline-flex items-center gap-1.5 text-[11px] font-medium text-ink-600">
                                    <span>Sampai</span>
                                    <input type="date" name="until" form="email-requests-filter-form" value="{{ $filters['until'] }}" class="h-7 w-[118px] rounded-lg border border-ink-200 bg-white px-2 text-[11px] text-ink-900 outline-none transition focus:border-brand-500" />
                                </label>
                                <x-button type="submit" form="email-requests-filter-form" size="compact" class="!px-2.5 !py-1 !text-[11px]">
                                    Terapkan
                                </x-button>
                                <x-button :href="route('forms.email-requests.index')" variant="secondary" size="compact" class="!px-2.5 !py-1 !text-[11px]">
                                    <x-heroicon-o-arrow-path class="mr-1.5 h-3.5 w-3.5" />
                                    Reset
                                </x-button>
                                <span>Total {{ $requests->count() }} data</span>
                            </div>
                            <x-button :href="route('forms.email-requests.create')" size="compact" class="!px-2.5 !py-1 !text-[11px]">
                                <x-heroicon-o-plus class="mr-1.5 h-3.5 w-3.5" />
                                Buat Permohonan
                            </x-button>
                        </div>
                    </div>
                @endif

                <div class="ui-datatable-shell">
                    <table id="email-requests-table" class="ui-table-compact">
                        <thead>
                            <tr>
                                @if ($canBulkDelete)
                                    <th>
                                        <label class="inline-flex items-center gap-2">
                                            <input type="checkbox" :checked="allSelectedOnPage" x-on:change="togglePageSelection($event)" class="rounded border-ink-300 text-ink-900 focus:ring-ink-400" />
                                            <span class="sr-only">Pilih semua</span>
                                        </label>
                                    </th>
                                @endif
                                <th class="ui-table-cell-nowrap">
                                    <x-sort-link column="created_at" label="Tanggal" :sort="$sort" :direction="$direction" size="compact" />
                                </th>
                                <th class="ui-table-cell-nowrap">
                                    <x-sort-link column="employee_name" label="Pemohon" :sort="$sort" :direction="$direction" size="compact" />
                                </th>
                                <th class="ui-table-cell-nowrap">Dept</th>
                                <th class="ui-table-cell-nowrap">Jabatan</th>
                                <th class="ui-table-cell-nowrap">
                                    <x-sort-link column="status" label="Status" :sort="$sort" :direction="$direction" size="compact" />
                                </th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100">
                            @foreach ($requests as $emailRequest)
                                <tr>
                                    @if ($canBulkDelete)
                                        <td>
                                            <input
                                                type="checkbox"
                                                data-select-id="{{ $emailRequest->id }}"
                                                :checked="selectedIds.includes('{{ $emailRequest->id }}')"
                                                x-on:change="toggleSelection('{{ $emailRequest->id }}', $event.target.checked)"
                                                class="rounded border-ink-300 text-ink-900 focus:ring-ink-400"
                                            />
                                        </td>
                                    @endif
                                    <td class="ui-table-cell-nowrap text-sm text-ink-700">{{ optional($emailRequest->created_at)->format('d M Y H:i') }}</td>
                                    <td class="ui-table-cell-nowrap font-medium text-ink-900">{{ $emailRequest->employee_name }}</td>
                                    <td class="ui-table-cell-nowrap">{{ $emailRequest->department_name ?: '-' }}</td>
                                    <td class="ui-table-cell-nowrap">{{ $emailRequest->job_title ?: '-' }}</td>
                                    <td class="ui-table-cell-nowrap">
                                        <x-badge size="compact" variant="warning">{{ strtoupper($emailRequest->status) }}</x-badge>
                                    </td>
                                    <td>
                                        @php
                                            $hasFullTtd = filled($emailRequest->full_ttd_path);
                                            $fullTtdUrl = $hasFullTtd ? \Illuminate\Support\Facades\Storage::disk('public')->url($emailRequest->full_ttd_path) : null;
                                            $fullTtdDateValue = optional($emailRequest->full_ttd_signed_at)->format('Y-m-d') ?: '';
                                        @endphp
                                        <div class="ui-action-row ui-action-row--compact justify-end">
                                            <x-button
                                                type="button"
                                                variant="action-review"
                                                x-on:click="openFullTtdModal({ id: '{{ $emailRequest->id }}', date: '{{ $fullTtdDateValue }}' })"
                                                title="{{ $hasFullTtd ? 'Ganti File Full TTD' : 'Upload Full TTD' }}"
                                            >
                                                <x-heroicon-o-arrow-up-tray class="ui-action-icon" />
                                            </x-button>

                                            @if ($hasFullTtd && $fullTtdUrl)
                                                <x-button href="{{ $fullTtdUrl }}" target="_blank" variant="action-pdf" title="PDF Full TTD">
                                                    <x-heroicon-o-document-text class="ui-action-icon" />
                                                </x-button>
                                            @else
                                                <x-button :href="route('forms.email-requests.pdf', $emailRequest)" target="_blank" variant="action-pdf" title="PDF permohonan (belum TTD)">
                                                    <x-heroicon-o-document-text class="ui-action-icon" />
                                                </x-button>
                                            @endif
                                        </div>
                                        @if ($emailRequest->full_ttd_name)
                                            <div class="mt-1 text-right text-[11px] text-ink-500">
                                                Full TTD: {{ $emailRequest->full_ttd_name }}
                                            </div>
                                        @endif
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
