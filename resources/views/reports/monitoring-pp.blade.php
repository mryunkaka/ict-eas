<x-app-layout>
    <div class="ui-page-workspace">
        <x-card class="ui-page-workspace-card" padding="none">
            <div class="ui-page-toolbar">
                <form id="monitoring-pp-filters" class="ui-page-toolbar-form">
                    @if ($canFilterAllUnits)
                        <x-select
                            name="unit_id"
                            label="Unit"
                            :value="$selectedUnitId"
                            :options="$units"
                            placeholder="Semua Unit"
                            size="compact"
                        />
                    @endif
                    <label class="inline-flex items-center gap-1.5 text-[11px] font-medium text-ink-600">
                        <span>Dari</span>
                        <input type="date" name="from" value="{{ request('from') }}" class="h-7 w-[118px] rounded-lg border border-ink-200 bg-white px-2 text-[11px] text-ink-900 outline-none transition focus:border-brand-500" />
                    </label>
                    <label class="inline-flex items-center gap-1.5 text-[11px] font-medium text-ink-600">
                        <span>Sampai</span>
                        <input type="date" name="until" value="{{ request('until') }}" class="h-7 w-[118px] rounded-lg border border-ink-200 bg-white px-2 text-[11px] text-ink-900 outline-none transition focus:border-brand-500" />
                    </label>
                    <div class="flex flex-wrap items-center gap-2">
                        <x-button type="button" id="apply-monitoring-pp-filter" size="compact" class="!px-2.5 !py-1 !text-[11px]">Terapkan</x-button>
                        <x-button :href="route('reports.monitoring-pp')" variant="secondary" size="compact" class="!px-2.5 !py-1 !text-[11px]">
                            <x-heroicon-o-arrow-path class="mr-1.5 h-3.5 w-3.5" />
                            Reset
                        </x-button>
                        <x-button type="button" id="monitoring-pp-import-export" variant="secondary" size="compact" class="!px-2.5 !py-1 !text-[11px]">
                            <x-heroicon-o-arrow-up-tray class="mr-1.5 h-3.5 w-3.5" />
                            Import/Export
                        </x-button>
                    </div>
                </form>
            </div>

            @if ($canBulkDeleteMonitoringPp)
                <div id="monitoring-pp-selection-bar" class="ui-page-section-bar hidden">
                    <div class="flex flex-wrap items-center justify-between gap-3 text-sm text-ink-700">
                        <span id="monitoring-pp-selection-count" class="text-xs text-ink-500">0 data dipilih</span>
                        <button type="button" id="bulk-delete-monitoring-pp" class="ui-page-danger-button !px-2.5 !py-1 !text-[11px]">
                            <x-heroicon-o-trash class="mr-1.5 h-3.5 w-3.5" />
                            Delete selected
                        </button>
                    </div>
                </div>
            @endif

            <div class="ui-page-table-content">
                <div class="ui-datatable-shell">
                    <table id="monitoring-pp-table" class="ui-table-compact">
                    <thead>
                        <tr>
                            @if ($canBulkDeleteMonitoringPp)
                                <th>
                                    <input type="checkbox" id="monitoring-pp-check-all" class="rounded border-ink-300 text-brand-700 focus:ring-brand-500" />
                                </th>
                            @endif
                            <th>No</th>
                            <th>Unit</th>
                            <th>Jenis Barang</th>
                            <th>Nama Barang</th>
                            <th>Merk</th>
                            <th>Jumlah</th>
                            <th>Harga</th>
                            <th>Total</th>
                            <th>Keterangan</th>
                            <th>Gambar Barang</th>
                            <th>Tanggal Form ICT</th>
                            <th>Form ICT</th>
                            <th>Tanggal PPNK/PPK</th>
                            <th>Berkas PPNK/PPK</th>
                            <th>No PPNK/PPK</th>
                            <th>Tanggal PPM/PR</th>
                            <th>Berkas PPM</th>
                            <th>Nama PPM</th>
                            <th>No PPM</th>
                            <th>No PR</th>
                            <th>Tanggal PO</th>
                            <th>Berkas PO</th>
                            <th>No PO</th>
                            <th>Tanggal Diterima</th>
                            <th>Tanggal Pembuatan BA</th>
                            <th>BA Serah Terima</th>
                            <th>Model/Spesifikasi</th>
                            <th>Serial Number</th>
                            <th>No Asset</th>
                            <th>Pemakai</th>
                            <th>Jabatan</th>
                            <th>Atasan Pemakai</th>
                            <th>Jabatan Atasan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    </table>
                </div>
            </div>
        </x-card>
    </div>

    @if ($canImportMonitoringPp)
        <div id="monitoring-pp-import-export-modal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 py-6">
            <div class="ui-modal-backdrop absolute inset-0"></div>
            <div class="ui-modal-panel relative z-10 w-full max-w-xl">
                <div class="flex items-start justify-between gap-4">
                    <h2 class="text-lg font-semibold text-ink-900">Import / Export Monitoring PP</h2>
                    <button type="button" id="monitoring-pp-import-export-close" class="rounded-full border border-ink-200 px-3 py-1 text-sm font-medium text-ink-600 hover:bg-ink-50">Tutup</button>
                </div>

                <form method="POST" action="{{ route('reports.monitoring-pp.import-excel') }}" enctype="multipart/form-data" class="mt-5 space-y-3">
                    @csrf
                    <x-input name="import_file" type="file" label="Import Excel" accept=".xlsx,.xls,.csv" size="compact" />
                    <div class="flex flex-wrap justify-end gap-2 pt-1">
                        <x-button type="button" id="export-monitoring-pp-excel" variant="secondary" size="compact">Export Excel</x-button>
                        <x-button :href="route('reports.monitoring-pp.example-import')" variant="secondary" size="compact">Download Example Import</x-button>
                        <x-button type="submit" size="compact">Import Excel</x-button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($canBulkDeleteMonitoringPp)
        <form id="monitoring-pp-bulk-delete-form" method="POST" action="{{ route('reports.monitoring-pp.bulk-delete') }}" class="hidden">
            @csrf
            <div id="monitoring-pp-bulk-delete-inputs"></div>
        </form>
    @endif

    @if ($canManageMonitoringPpUploads)
        <div id="monitoring-pp-upload-modal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 py-6">
            <div class="ui-modal-backdrop absolute inset-0"></div>
            <div class="ui-modal-panel relative z-10 w-full max-w-2xl">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 id="monitoring-pp-upload-title" class="text-xl font-semibold text-ink-900">Upload Dokumen</h2>
                        <p id="monitoring-pp-upload-description" class="mt-1 text-sm text-ink-500">Pilih file yang akan diupload.</p>
                    </div>
                    <button type="button" id="monitoring-pp-upload-close" class="rounded-full border border-ink-200 px-3 py-1 text-sm font-medium text-ink-600 hover:bg-ink-50">Tutup</button>
                </div>

                <form id="monitoring-pp-upload-form" method="POST" enctype="multipart/form-data" class="mt-6 space-y-4">
                    @csrf

                    <div data-upload-scope="ppnk" class="hidden grid gap-4 md:grid-cols-2">
                        <x-input name="ppnk_number" label="No PPNK/PPK" />
                        <x-input name="uploaded_at" type="date" label="Tanggal PPNK/PPK" />
                    </div>

                    <div data-upload-scope="ppm" class="hidden grid gap-4 md:grid-cols-3">
                        <x-input name="ppm_number" label="No PPM" />
                        <x-input name="pr_number" label="No PR" />
                        <x-input name="uploaded_at" type="date" label="Tanggal PPM/PR" />
                    </div>

                    <div data-upload-scope="po" class="hidden grid gap-4 md:grid-cols-2">
                        <x-input name="po_number" label="No PO" />
                        <x-input name="uploaded_at" type="date" label="Tanggal PO" />
                    </div>

                    <div data-upload-scope="photo" class="hidden">
                        <x-input name="photo" type="file" label="Foto Barang" accept="image/*" data-auto-compress-image="1" />
                    </div>

                    <div data-upload-scope="signed-form" class="hidden">
                        <x-input name="signed_pdf" type="file" label="Form ICT Full TTD" accept=".pdf,.jpg,.jpeg,.png,.webp" data-auto-compress-image="1" />
                    </div>

                    <div data-upload-scope="ppnk" class="hidden">
                        <x-input name="attachment" type="file" label="Berkas PPNK/PPK" accept=".pdf,.jpg,.jpeg,.png,.webp" data-auto-compress-image="1" />
                    </div>

                    <div data-upload-scope="ppm" class="hidden">
                        <x-input name="attachment" type="file" label="Berkas PPM" accept=".pdf,.jpg,.jpeg,.png,.webp" data-auto-compress-image="1" />
                    </div>

                    <div data-upload-scope="po" class="hidden">
                        <x-input name="attachment" type="file" label="Berkas PO" accept=".pdf,.jpg,.jpeg,.png,.webp" data-auto-compress-image="1" />
                    </div>

                    <div data-upload-scope="ba" class="hidden">
                        <x-input name="attachment" type="file" label="BA Serah Terima Full TTD" accept=".pdf,.jpg,.jpeg,.png,.webp" data-auto-compress-image="1" />
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <x-button type="button" variant="secondary" id="monitoring-pp-upload-cancel">Batal</x-button>
                        <x-button type="submit">Upload</x-button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const pageRoot = document.querySelector('.ui-page-workspace');
                const adminScroll = pageRoot?.closest('.ui-admin-scroll');
                const adminContent = pageRoot?.closest('.ui-admin-content');
                const adminContentInner = pageRoot?.closest('.ui-admin-content-inner');
                [adminScroll, adminContent, adminContentInner].forEach((element) => {
                    element?.classList.add('is-page-scroll-locked');
                });

                const filterForm = document.getElementById('monitoring-pp-filters');
                const applyButton = document.getElementById('apply-monitoring-pp-filter');
                const exportButton = document.getElementById('export-monitoring-pp-excel');
                const importExportTrigger = document.getElementById('monitoring-pp-import-export');
                const importExportModal = document.getElementById('monitoring-pp-import-export-modal');
                const importExportClose = document.getElementById('monitoring-pp-import-export-close');
                const tableElement = document.getElementById('monitoring-pp-table');
                const hasBulkDelete = @js($canBulkDeleteMonitoringPp);
                const canManageUploads = @js($canManageMonitoringPpUploads);
                const canImportMonitoringPp = @js($canImportMonitoringPp);
                const selectedRequestIds = new Set();
                const viewportHeight = window.innerHeight || document.documentElement.clientHeight || 900;
                const tableTop = tableElement?.getBoundingClientRect().top || 0;
                const reservedBottomSpace = 190;
                const tableScrollHeight = Math.max(220, viewportHeight - tableTop - reservedBottomSpace);

                const columnDefs = [
                    { targets: hasBulkDelete ? [1] : [0], width: '60px', searchable: false, orderable: false },
                    { targets: hasBulkDelete ? [2] : [1], width: '180px' },
                    { targets: hasBulkDelete ? [3] : [2], width: '150px' },
                    { targets: hasBulkDelete ? [4] : [3], width: '260px' },
                    { targets: hasBulkDelete ? [5] : [4], width: '150px' },
                    { targets: hasBulkDelete ? [6] : [5], width: '90px', className: 'whitespace-nowrap' },
                    { targets: hasBulkDelete ? [7, 8] : [6, 7], width: '150px', className: 'whitespace-nowrap' },
                    { targets: hasBulkDelete ? [9] : [8], width: '280px' },
                    { targets: hasBulkDelete ? [10] : [9], width: '140px', searchable: false, orderable: false },
                    { targets: hasBulkDelete ? [11, 13, 16, 21, 24, 25] : [10, 12, 15, 20, 23, 24], width: '150px', className: 'whitespace-nowrap' },
                    { targets: hasBulkDelete ? [12, 14, 17, 22, 26] : [11, 13, 16, 21, 25], width: '160px', searchable: false, orderable: false },
                    { targets: hasBulkDelete ? [15, 19, 20, 23, 28, 29] : [14, 18, 19, 22, 27, 28], width: '130px', className: 'whitespace-nowrap' },
                    { targets: hasBulkDelete ? [18, 27] : [17, 26], width: '240px' },
                    { targets: hasBulkDelete ? [30, 32] : [29, 31], width: '180px' },
                    { targets: hasBulkDelete ? [31, 33] : [30, 32], width: '170px' },
                    { targets: hasBulkDelete ? [34] : [33], width: 'auto', className: 'whitespace-nowrap' }
                ];

                if (hasBulkDelete) {
                    columnDefs.unshift({ targets: [0], width: '44px', searchable: false, orderable: false });
                }

                const table = new DataTable('#monitoring-pp-table', {
                    processing: true,
                    serverSide: true,
                    deferRender: true,
                    searchDelay: 400,
                    pageLength: 10,
                    lengthChange: false,
                    scrollX: true,
                    scrollY: `${tableScrollHeight}px`,
                    scrollCollapse: true,
                    info: false,
                    autoWidth: false,
                    ajax: {
                        url: @js(route('reports.monitoring-pp.data')),
                        data(d) {
                            const formData = new FormData(filterForm);

                            d.unit_id = formData.get('unit_id') || '';
                            d.from = formData.get('from') || '';
                            d.until = formData.get('until') || '';
                        }
                    },
                    columnDefs,
                    language: {
                        processing: 'Memuat data...',
                        search: '',
                        searchPlaceholder: 'Cari data...',
                        lengthMenu: 'Tampilkan _MENU_ baris',
                        info: '',
                        infoEmpty: 'Tidak ada data',
                        zeroRecords: 'Data tidak ditemukan',
                        emptyTable: 'Tidak ada data monitoring PP',
                        paginate: {
                            first: 'Awal',
                            previous: 'Sebelumnya',
                            next: 'Berikutnya',
                            last: 'Akhir'
                        }
                    }
                });

                const refreshBulkDeleteState = () => {
                    if (!hasBulkDelete) {
                        return;
                    }

                    const selectionBar = document.getElementById('monitoring-pp-selection-bar');
                    const selectionCount = document.getElementById('monitoring-pp-selection-count');
                    const checkAll = document.getElementById('monitoring-pp-check-all');
                    const checkboxes = document.querySelectorAll('.monitoring-pp-request-checkbox');
                    const checkedOnPage = Array.from(checkboxes).filter((checkbox) => checkbox.checked);

                    if (selectionBar) {
                        selectionBar.classList.toggle('hidden', selectedRequestIds.size === 0);
                    }
                    if (selectionCount) {
                        selectionCount.textContent = `${selectedRequestIds.size} data dipilih`;
                    }
                    checkAll.checked = checkboxes.length > 0 && checkedOnPage.length === checkboxes.length;
                    checkAll.indeterminate = checkedOnPage.length > 0 && checkedOnPage.length < checkboxes.length;
                };

                const syncCheckboxes = () => {
                    if (!hasBulkDelete) {
                        return;
                    }

                    document.querySelectorAll('.monitoring-pp-request-checkbox').forEach((checkbox) => {
                        checkbox.checked = selectedRequestIds.has(checkbox.value);
                    });

                    refreshBulkDeleteState();
                };

                table.on('draw', syncCheckboxes);

                applyButton.addEventListener('click', () => {
                    table.ajax.reload();
                });

                if (canImportMonitoringPp && importExportTrigger && importExportModal) {
                    const closeImportExportModal = () => {
                        importExportModal.classList.add('hidden');
                        importExportModal.classList.remove('flex');
                    };
                    importExportTrigger.addEventListener('click', () => {
                        importExportModal.classList.remove('hidden');
                        importExportModal.classList.add('flex');
                    });
                    importExportClose?.addEventListener('click', closeImportExportModal);
                    importExportModal.addEventListener('click', (event) => {
                        if (event.target === importExportModal || event.target.classList.contains('ui-modal-backdrop')) {
                            closeImportExportModal();
                        }
                    });
                }

                exportButton?.addEventListener('click', () => {
                    const formData = new FormData(filterForm);
                    const params = new URLSearchParams();

                    for (const [key, value] of formData.entries()) {
                        if (value) {
                            params.append(key, value);
                        }
                    }

                    window.location.href = `${@js(route('reports.monitoring-pp.export-excel'))}?${params.toString()}`;
                });

                if (hasBulkDelete) {
                    document.addEventListener('change', (event) => {
                        const target = event.target;

                        if (target.matches('.monitoring-pp-request-checkbox')) {
                            if (target.checked) {
                                selectedRequestIds.add(target.value);
                            } else {
                                selectedRequestIds.delete(target.value);
                            }

                            refreshBulkDeleteState();
                        }
                    });

                    document.getElementById('monitoring-pp-check-all').addEventListener('change', (event) => {
                        const shouldCheck = event.target.checked;

                        document.querySelectorAll('.monitoring-pp-request-checkbox').forEach((checkbox) => {
                            checkbox.checked = shouldCheck;

                            if (shouldCheck) {
                                selectedRequestIds.add(checkbox.value);
                            } else {
                                selectedRequestIds.delete(checkbox.value);
                            }
                        });

                        refreshBulkDeleteState();
                    });

                    document.getElementById('bulk-delete-monitoring-pp').addEventListener('click', () => {
                        if (selectedRequestIds.size === 0) {
                            return;
                        }

                        if (! window.confirm(`Hapus ${selectedRequestIds.size} Form ICT terpilih secara permanen?`)) {
                            return;
                        }

                        const container = document.getElementById('monitoring-pp-bulk-delete-inputs');
                        const form = document.getElementById('monitoring-pp-bulk-delete-form');

                        container.innerHTML = '';
                        Array.from(selectedRequestIds).sort().forEach((requestId) => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'request_ids[]';
                            input.value = requestId;
                            container.appendChild(input);
                        });

                        form.submit();
                    });
                }

                if (canManageUploads) {
                    const modal = document.getElementById('monitoring-pp-upload-modal');
                    const modalForm = document.getElementById('monitoring-pp-upload-form');
                    const modalTitle = document.getElementById('monitoring-pp-upload-title');
                    const modalDescription = document.getElementById('monitoring-pp-upload-description');
                    const modalCloseButtons = [
                        document.getElementById('monitoring-pp-upload-close'),
                        document.getElementById('monitoring-pp-upload-cancel'),
                    ];
                    const scopeElements = modal.querySelectorAll('[data-upload-scope]');
                    const titleMap = {
                        photo: 'Upload Foto Barang',
                        'signed-form': 'Upload Form ICT Full TTD',
                        ppnk: 'Upload Dokumen PPNK/PPK',
                        ppm: 'Upload Dokumen PPM',
                        po: 'Upload Dokumen PO',
                        ba: 'Upload BA Serah Terima',
                    };

                    const resetModal = () => {
                        modalForm.reset();
                        scopeElements.forEach((element) => {
                            element.classList.add('hidden');
                            element.querySelectorAll('input, select, textarea').forEach((input) => {
                                input.disabled = true;
                            });
                        });
                    };

                    const closeModal = () => {
                        modal.classList.add('hidden');
                        modal.classList.remove('flex');
                        resetModal();
                    };

                    const openModal = (button) => {
                        const uploadType = button.dataset.uploadType;
                        const itemName = button.dataset.itemName || button.dataset.formNumber || 'dokumen';

                        resetModal();
                        modalForm.action = button.dataset.uploadAction;
                        modalTitle.textContent = titleMap[uploadType] || 'Upload Dokumen';
                        modalDescription.textContent = `Upload untuk ${itemName}.`;

                        scopeElements.forEach((element) => {
                            if (element.dataset.uploadScope === uploadType) {
                                element.classList.remove('hidden');
                                element.querySelectorAll('input, select, textarea').forEach((input) => {
                                    input.disabled = false;
                                });
                            }
                        });

                        if (uploadType === 'ppnk') {
                            modalForm.querySelector('[name="ppnk_number"]').value = button.dataset.ppnkNumber || '';
                            modalForm.querySelectorAll('[name="uploaded_at"]').forEach((input) => input.value = button.dataset.uploadedAt || '');
                        }

                        if (uploadType === 'ppm') {
                            modalForm.querySelector('[name="ppm_number"]').value = button.dataset.ppmNumber || '';
                            modalForm.querySelector('[name="pr_number"]').value = button.dataset.prNumber || '';
                            modalForm.querySelectorAll('[name="uploaded_at"]').forEach((input) => input.value = button.dataset.uploadedAt || '');
                        }

                        if (uploadType === 'po') {
                            modalForm.querySelector('[name="po_number"]').value = button.dataset.poNumber || '';
                            modalForm.querySelectorAll('[name="uploaded_at"]').forEach((input) => input.value = button.dataset.uploadedAt || '');
                        }

                        modal.classList.remove('hidden');
                        modal.classList.add('flex');
                    };

                    document.addEventListener('click', (event) => {
                        const uploadButton = event.target.closest('[data-monitoring-upload="true"]');

                        if (uploadButton) {
                            openModal(uploadButton);
                            return;
                        }

                        if (event.target === modal || event.target.classList.contains('monitoring-modal-backdrop')) {
                            closeModal();
                        }
                    });

                    modalCloseButtons.forEach((button) => button.addEventListener('click', closeModal));
                }
            });
        </script>
    @endpush
</x-app-layout>
