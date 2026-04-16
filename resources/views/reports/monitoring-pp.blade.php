<x-app-layout>
    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">
    @endpush

    <div class="space-y-6">
        <x-card>
            <form id="monitoring-pp-filters" class="grid gap-4 md:grid-cols-4 xl:grid-cols-7">
                @if ($canFilterAllUnits)
                    <x-select
                        name="unit_id"
                        label="Unit"
                        :value="$selectedUnitId"
                        :options="$units"
                        placeholder="Semua Unit"
                    />
                @endif
                <x-input name="from" type="date" label="Tanggal Form Dari" :value="request('from')" />
                <x-input name="until" type="date" label="Tanggal Form Sampai" :value="request('until')" />
                <div class="flex flex-wrap items-end gap-3 md:col-span-2 xl:col-span-4">
                    <x-button type="button" id="apply-monitoring-pp-filter">Filter</x-button>
                    <x-button :href="route('reports.monitoring-pp')" variant="secondary">Reset</x-button>
                    <x-button type="button" id="export-monitoring-pp-excel" variant="secondary">Export Excel</x-button>
                    <x-button :href="route('reports.monitoring-pp.example-import')" variant="secondary">Download Example Import</x-button>
                    @if ($canBulkDeleteMonitoringPp)
                        <x-button type="button" id="bulk-delete-monitoring-pp" variant="danger" disabled>Bulk Delete</x-button>
                    @endif
                    <x-button :href="route('reports.index')" variant="secondary">Kembali ke Report</x-button>
                </div>
            </form>
        </x-card>

        @if ($canImportMonitoringPp)
            <x-card title="Import Monitoring PP" subtitle="Upload file Excel untuk sinkronisasi data procurement">
                <form method="POST" action="{{ route('reports.monitoring-pp.import-excel') }}" enctype="multipart/form-data" class="grid gap-4 md:grid-cols-[minmax(0,1fr)_auto]">
                    @csrf
                    <x-input name="import_file" type="file" label="Import Excel Monitoring PP" accept=".xlsx,.xls,.csv" />
                    <div class="flex items-end">
                        <x-button type="submit">Import Excel</x-button>
                    </div>
                </form>
            </x-card>
        @endif

        <x-card title="Data Monitoring PP" subtitle="Tampilan monitoring procurement mengikuti pola visual modul Permintaan ICT">
            <div class="ui-data-shell">
                <table id="monitoring-pp-table" class="display w-full text-sm">
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
        </x-card>
    </div>

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
                        <x-input name="photo" type="file" label="Foto Barang" accept="image/*" />
                    </div>

                    <div data-upload-scope="signed-form" class="hidden">
                        <x-input name="signed_pdf" type="file" label="Form ICT Full TTD" accept=".pdf,.jpg,.jpeg,.png,.webp" />
                    </div>

                    <div data-upload-scope="ppnk" class="hidden">
                        <x-input name="attachment" type="file" label="Berkas PPNK/PPK" accept=".pdf,.jpg,.jpeg,.png,.webp" />
                    </div>

                    <div data-upload-scope="ppm" class="hidden">
                        <x-input name="attachment" type="file" label="Berkas PPM" accept=".pdf,.jpg,.jpeg,.png,.webp" />
                    </div>

                    <div data-upload-scope="po" class="hidden">
                        <x-input name="attachment" type="file" label="Berkas PO" accept=".pdf,.jpg,.jpeg,.png,.webp" />
                    </div>

                    <div data-upload-scope="ba" class="hidden">
                        <x-input name="attachment" type="file" label="BA Serah Terima Full TTD" accept=".pdf,.jpg,.jpeg,.png,.webp" />
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
                const filterForm = document.getElementById('monitoring-pp-filters');
                const applyButton = document.getElementById('apply-monitoring-pp-filter');
                const exportButton = document.getElementById('export-monitoring-pp-excel');
                const hasBulkDelete = @js($canBulkDeleteMonitoringPp);
                const canManageUploads = @js($canManageMonitoringPpUploads);
                const selectedRequestIds = new Set();

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
                    pageLength: 25,
                    lengthMenu: [25, 50, 100],
                    scrollX: true,
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
                        search: 'Cari:',
                        lengthMenu: 'Tampilkan _MENU_ baris',
                        info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
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

                    const bulkDeleteButton = document.getElementById('bulk-delete-monitoring-pp');
                    const checkAll = document.getElementById('monitoring-pp-check-all');
                    const checkboxes = document.querySelectorAll('.monitoring-pp-request-checkbox');
                    const checkedOnPage = Array.from(checkboxes).filter((checkbox) => checkbox.checked);

                    bulkDeleteButton.disabled = selectedRequestIds.size === 0;
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

                exportButton.addEventListener('click', () => {
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
