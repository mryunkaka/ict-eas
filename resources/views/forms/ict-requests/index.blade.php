@php
    $requestDetails = $requests->map(function ($request) {
        $globalQuotations = $request->quotations
            ->whereNull('ict_request_item_id')
            ->values()
            ->map(fn ($quotation) => [
                'vendor_name' => $quotation->vendor_name,
                'attachment_name' => $quotation->attachment_name,
                'attachment_url' => $quotation->attachment_path ? \Illuminate\Support\Facades\Storage::url($quotation->attachment_path) : null,
                'is_image' => str_starts_with((string) $quotation->attachment_mime, 'image/'),
            ])
            ->all();

        // Ambil Staff ICT dari unit yang sama
        $staffIct = $request->unit_id
            ? \App\Models\User::where('unit_id', $request->unit_id)
                ->where('role', \App\Enums\UserRole::StaffIct)
                ->first()
            : null;

        // Ambil riwayat HRGA (deliverer) terakhir dari unit yang sama
        $lastDeliverer = $request->unit_id
            ? \App\Models\AssetHandover::whereHas('ictRequest', function ($q) use ($request) {
                    $q->where('unit_id', $request->unit_id);
                })
                ->whereNotNull('deliverer_name')
                ->latest('created_at')
                ->first(['deliverer_name', 'deliverer_position'])
            : null;

        return [
            'id' => $request->id,
            'subject' => $request->subject,
            'revision_number' => (int) $request->revision_number,
            'print_count' => (int) $request->print_count,
            'unit' => $request->departmentDisplayName(),
            'unit_id' => $request->unit_id,
            'requester' => $request->requesterDisplayName(),
            'generated_pdf_url' => route('forms.ict-requests.pdf', $request),
            'copy_pdf_url' => route('forms.ict-requests.pdf', ['ictRequest' => $request, 'copy' => 1]),
            'print_url' => route('forms.ict-requests.print', $request),
            'edit_url' => route('forms.ict-requests.edit', $request),
            'upload_signed_url' => route('approvals.ict.update', $request),
            'upload_ppnk_url' => route('forms.ict-requests.ppnk.store', $request),
            'upload_ppm_url' => route('forms.ict-requests.ppm.store', $request),
            'upload_po_url' => route('forms.ict-requests.po.store', $request),
            'goods_receipt_url' => route('forms.ict-requests.goods-receipt.store', $request),
            'verify_audit_url' => route('forms.ict-requests.verify-audit', $request),
            'priority' => strtoupper($request->priority),
            'raw_status' => $request->status,
            'status' => $request->statusLabel(),
            'can_print' => $request->status === 'checked_by_asmen' && (auth()->user()->isIctAdmin() || auth()->user()->isStaffIct()),
            'requires_signature_upload' => $request->status === 'checked_by_asmen' && (int) $request->print_count > 0 && ! $request->final_signed_pdf_path,
            'can_upload_signed_pdf' => $request->status === 'checked_by_asmen' && (int) $request->print_count > 0 && auth()->user()->isIctAdmin() && ! $request->final_signed_pdf_path,
            'can_manage_ppnk' => in_array($request->status, ['progress_ppnk', 'progress_verifikasi_audit'], true) && auth()->user()->isIctAdmin(),
            'can_manage_ppm' => $request->status === 'progress_ppm' && auth()->user()->isIctAdmin(),
            'can_manage_po' => $request->status === 'progress_po' && auth()->user()->isIctAdmin(),
            'can_manage_goods_receipt' => $request->status === 'progress_waiting_goods' && auth()->user()->isIctAdmin(),
            'can_verify_audit' => $request->status === 'progress_verifikasi_audit' && auth()->user()->isIctAdmin(),
            'is_locked_after_asmen' => in_array($request->status, ['checked_by_asmen', 'progress_ppnk', 'progress_verifikasi_audit', 'progress_ppm', 'progress_po', 'progress_waiting_goods', 'completed'], true),
            'quotation_mode' => $request->quotation_mode,
            'created_at' => optional($request->created_at)->format('d M Y H:i'),
            'final_signed_pdf_name' => $request->final_signed_pdf_name,
            'final_signed_pdf_url' => $request->final_signed_pdf_path ? \Illuminate\Support\Facades\Storage::url($request->final_signed_pdf_path) : null,
            'rejected_reason' => $request->rejected_reason,
            'revision_note' => $request->revision_note,
            'revision_attachment_name' => $request->revision_attachment_name,
            'revision_attachment_url' => $request->revision_attachment_path ? \Illuminate\Support\Facades\Storage::url($request->revision_attachment_path) : null,
            'staff_ict' => $staffIct ? [
                'name' => $staffIct->name,
                'position' => $staffIct->job_title ?? $staffIct->role?->label() ?? 'Staff ICT',
            ] : null,
            'previous_deliverer' => $lastDeliverer ? [
                'name' => $lastDeliverer->deliverer_name,
                'position' => $lastDeliverer->deliverer_position,
            ] : null,
            'total_estimated_price' => $request->items
                ->when(in_array($request->status, ['progress_ppm', 'progress_po', 'completed'], true), function ($items) {
                    return $items->where('audit_status', '!=', 'takeout');
                })
                ->sum(fn ($item) => ((float) ($item->estimated_price ?? 0)) * ((int) ($item->quantity ?? 0))),
            'items' => $request->items
                // Filter: hide takeout items for PPM and beyond
                ->when(in_array($request->status, ['progress_ppm', 'progress_po', 'completed'], true), function ($items) {
                    return $items->where('audit_status', '!=', 'takeout');
                })
                ->map(fn ($item) => [
                'id' => $item->id,
                'item_category' => $item->item_category,
                'item_name' => $item->item_name,
                'brand_type' => $item->brand_type,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'estimated_price' => $item->estimated_price,
                'total_estimated_price' => ((float) ($item->estimated_price ?? 0)) * ((int) ($item->quantity ?? 0)),
                'notes' => $item->notes,
                'photo_name' => $item->photo_name,
                'photo_url' => $item->photo_path ? \Illuminate\Support\Facades\Storage::url($item->photo_path) : null,
                'ppnk_number' => $item->ppnkDocument?->ppnk_number,
                'ppnk_attachment_name' => $item->ppnkDocument?->attachment_name,
                'ppnk_attachment_url' => $item->ppnkDocument?->attachment_path ? \Illuminate\Support\Facades\Storage::url($item->ppnkDocument->attachment_path) : null,
                'ppnk_attachment_is_image' => str_starts_with((string) $item->ppnkDocument?->attachment_mime, 'image/'),
                'ppm_number' => $item->ppmDocument?->ppm_number,
                'ppm_attachment_name' => $item->ppmDocument?->attachment_name,
                'ppm_attachment_url' => $item->ppmDocument?->attachment_path ? \Illuminate\Support\Facades\Storage::url($item->ppmDocument->attachment_path) : null,
                'ppm_attachment_is_image' => str_starts_with((string) $item->ppmDocument?->attachment_mime, 'image/'),
                'po_number' => $item->poDocument?->po_number,
                'po_attachment_name' => $item->poDocument?->attachment_name,
                'po_attachment_url' => $item->poDocument?->attachment_path ? \Illuminate\Support\Facades\Storage::url($item->poDocument->attachment_path) : null,
                'po_attachment_is_image' => str_starts_with((string) $item->poDocument?->attachment_mime, 'image/'),
                'pr_number' => $item->pr_number,
                'audit_status' => $item->audit_status,
                'audit_reason' => $item->audit_reason,
                'takeout_qty' => $item->takeout_qty,
                'quotations' => $item->quotations->map(fn ($quotation) => [
                    'vendor_name' => $quotation->vendor_name,
                    'attachment_name' => $quotation->attachment_name,
                    'attachment_url' => $quotation->attachment_path ? \Illuminate\Support\Facades\Storage::url($quotation->attachment_path) : null,
                    'is_image' => str_starts_with((string) $quotation->attachment_mime, 'image/'),
                ])->all(),
            ])->all(),
            'global_quotations' => $globalQuotations,
        ];
    })->values();
@endphp

<x-app-layout>
    @php
        $pageIds = $requests->pluck('id')->map(fn ($id) => (string) $id)->values();
    @endphp
    <script>
        function ictRequestsData() {
            return {
                pageIds: @js($pageIds),
                selectedIds: [],
                selectAllMatching: false,
                totalMatching: @js($requests->count()),
                detailMap: @js($requestDetails->keyBy('id')),
                openDetailId: null,
                printTarget: null,
                ppnkTarget: null,
                ppmTarget: null,
                poTarget: null,
                goodsReceiptTarget: null,
                handoverTypes: {},
                getHandoverType(index, defaultValue = 'asset') {
                    return this.handoverTypes[index] || defaultValue;
                },
                setHandoverType(index, value) {
                    this.handoverTypes[index] = value;
                },
                auditTarget: null,
                auditStates: {},
                getAuditState(index, field, defaultValue) {
                    const key = `${index}_${field}`;
                    return this.auditStates[key] || defaultValue;
                },
                setAuditState(index, field, value) {
                    this.auditStates[`${index}_${field}`] = value;
                },
                validateTakeoutQty(index, maxQty) {
                    const takeoutQty = this.getAuditState(index, 'takeout_qty', 0);
                    if (parseInt(takeoutQty) > parseInt(maxQty)) {
                        alert(`Jumlah takeout (${takeoutQty}) tidak boleh lebih dari jumlah barang (${maxQty})`);
                        this.setAuditState(index, 'takeout_qty', maxQty);
                        return false;
                    }
                    if (parseInt(takeoutQty) < 0) {
                        this.setAuditState(index, 'takeout_qty', 0);
                        return false;
                    }
                    return true;
                },
                getRemainingQty(index) {
                    const item = this.detailMap[this.auditTarget]?.items?.[index];
                    if (!item) return 0;
                    const takeoutQty = this.getAuditState(index, 'takeout_qty', 0);
                    const status = this.getAuditState(index, 'audit_status', 'approved');
                    if (status === 'takeout') {
                        return parseInt(item.quantity) - parseInt(takeoutQty);
                    }
                    return item.quantity;
                },
                openPrintModal(id) {
                    const targetId = String(id);
                    if ((this.detailMap[targetId]?.print_count ?? 0) > 0) {
                        this.printTarget = targetId;
                        this.submitPrint();
                        return;
                    }
                    this.printTarget = targetId;
                },
                closePrintModal() {
                    this.printTarget = null;
                },
                submitPrint() {
                    if (!this.printTarget) return;
                    this.$refs.printForm.action = this.detailMap[this.printTarget]?.print_url;
                    this.$refs.printForm.submit();
                    this.closePrintModal();
                    setTimeout(() => {
                        window.location.reload();
                    }, 300);
                },
                signedPdfTarget: null,
                signedPdfDate: '',
                getTodayDate() {
                    return new Date().toISOString().slice(0, 10);
                },
                openSignedPdfModal(id) {
                    const targetId = String(id);
                    this.signedPdfTarget = targetId;
                    this.signedPdfDate = this.getTodayDate();
                    this.$nextTick(() => {
                        if (!this.$refs.signedPdfForm) return;
                        this.$refs.signedPdfForm.action = this.detailMap[targetId]?.upload_signed_url ?? '';
                    });
                },
                closeSignedPdfModal() {
                    this.signedPdfTarget = null;
                    this.signedPdfDate = '';
                    if (this.$refs.signedPdfForm) {
                        this.$refs.signedPdfForm.reset();
                    }
                },
                submitSignedPdfForm() {
                    if (!this.signedPdfTarget || !this.$refs.signedPdfForm) return;
                    if (!this.$refs.signedPdfForm.reportValidity()) return;
                    this.$refs.signedPdfForm.submit();
                },
                openPpnkModal(id) {
                    const targetId = String(id);
                    this.ppnkTarget = targetId;
                    this.$nextTick(() => {
                        if (!this.$refs.ppnkForm) return;
                        this.$refs.ppnkForm.action = this.detailMap[targetId]?.upload_ppnk_url ?? '';
                    });
                },
                closePpnkModal() {
                    this.ppnkTarget = null;
                    if (this.$refs.ppnkForm) {
                        this.$refs.ppnkForm.reset();
                    }
                },
                submitPpnkForm() {
                    if (!this.ppnkTarget) return;
                    if (!confirm('Yakin ingin menyimpan PPNK / PPK? Data yang sudah disimpan akan masuk ke proses verifikasi audit.')) {
                        return;
                    }
                    this.$refs.ppnkForm.submit();
                },
                openPpmModal(id) {
                    const targetId = String(id);
                    this.ppmTarget = targetId;
                    this.$nextTick(() => {
                        if (!this.$refs.ppmForm) return;
                        this.$refs.ppmForm.action = this.detailMap[targetId]?.upload_ppm_url ?? '';
                    });
                },
                closePpmModal() {
                    this.ppmTarget = null;
                    if (this.$refs.ppmForm) {
                        this.$refs.ppmForm.reset();
                    }
                },
                submitPpmForm() {
                    if (!this.ppmTarget) return;
                    if (!confirm('Yakin ingin menyimpan PPM? Data yang sudah disimpan akan masuk ke proses PO.')) {
                        return;
                    }
                    this.$refs.ppmForm.submit();
                },
                openPoModal(id) {
                    const targetId = String(id);
                    this.poTarget = targetId;
                    this.$nextTick(() => {
                        if (!this.$refs.poForm) return;
                        this.$refs.poForm.action = this.detailMap[targetId]?.upload_po_url ?? '';
                    });
                },
                closePoModal() {
                    this.poTarget = null;
                    if (this.$refs.poForm) {
                        this.$refs.poForm.reset();
                    }
                },
                submitPoForm() {
                    if (!this.poTarget) return;
                    if (!confirm('Yakin ingin menyimpan PO? Data yang sudah disimpan akan masuk ke proses menunggu barang diterima.')) {
                        return;
                    }
                    this.$refs.poForm.submit();
                },
                openGoodsReceiptModal(id) {
                    const targetId = String(id);
                    this.goodsReceiptTarget = targetId;
                    // Initialize handover types setelah data dimuat
                    this.$nextTick(() => {
                        const items = this.getGoodsReceiptItems();
                        this.handoverTypes = {};
                        items.forEach((_, index) => {
                            this.handoverTypes[index] = 'asset';
                        });
                    });
                },
                closeGoodsReceiptModal() {
                    this.goodsReceiptTarget = null;
                    if (this.$refs.goodsReceiptForm) {
                        this.$refs.goodsReceiptForm.reset();
                    }
                },
                submitGoodsReceiptForm() {
                    if (!this.goodsReceiptTarget) return;
                    if (!confirm('Yakin ingin memproses penerimaan barang ini? Status akan berubah menjadi Barang Sudah Diterima.')) {
                        return;
                    }
                    this.$refs.goodsReceiptForm.submit();
                },
                /**
                 * Menghitung jumlah unit yang diterima per item (qty - takeout_qty)
                 * Mengembalikan array flat dimana setiap unit menjadi entry terpisah
                 */
                getGoodsReceiptItems() {
                    const items = this.detailMap[this.goodsReceiptTarget]?.items ?? [];
                    const result = [];
                    items.forEach((item) => {
                        // Gunakan quantity saat ini (sudah dikurangi jika ada partial takeout)
                        // quantity di database sudah terupdate setelah partial takeout
                        let receivedQty = parseInt(item.quantity) || 0;
                        
                        // Jika audit_status masih 'takeout' (full takeout), skip item ini
                        if (item.audit_status === 'takeout') {
                            return; // Skip item yang full takeout
                        }
                        
                        // Jika ada takeout_qty tapi audit_status bukan 'takeout', 
                        // berarti sudah partial takeout dan quantity sudah dikurangi
                        // Jadi kita pakai quantity yang sudah ada
                        
                        // Buat entry terpisah untuk setiap unit yang diterima
                        for (let i = 0; i < receivedQty; i++) {
                            result.push({
                                ...item,
                                unitIndex: i,
                                receivedQty: receivedQty,
                            });
                        }
                    });
                    return result;
                },
                openAuditModal(id) {
                    const targetId = String(id);
                    this.auditTarget = targetId;
                    this.auditStates = {};
                    const items = this.detailMap[targetId]?.items ?? [];
                    items.forEach((item, index) => {
                        this.setAuditState(index, 'audit_status', 'approved');
                        this.setAuditState(index, 'takeout_qty', 0);
                    });
                },
                closeAuditModal() {
                    this.auditTarget = null;
                    if (this.$refs.auditForm) {
                        this.$refs.auditForm.reset();
                    }
                },
                submitAuditForm() {
                    if (!this.auditTarget) return;
                    const items = this.detailMap[this.auditTarget]?.items ?? [];
                    for (let index = 0; index < items.length; index++) {
                        const item = items[index];
                        const status = this.getAuditState(index, 'audit_status', 'approved');
                        const takeoutQty = this.getAuditState(index, 'takeout_qty', 0);
                        if (status === 'takeout' && parseInt(item.quantity) > 1) {
                            if (parseInt(takeoutQty) > parseInt(item.quantity)) {
                                alert(`Error: Jumlah takeout untuk "${item.item_name}" (${takeoutQty}) tidak boleh lebih dari jumlah barang (${item.quantity})`);
                                return;
                            }
                            if (parseInt(takeoutQty) <= 0) {
                                alert(`Error: Jumlah takeout untuk "${item.item_name}" harus lebih dari 0`);
                                return;
                            }
                        }
                    }
                    if (!confirm('Yakin submit verifikasi audit? Barang yang di-takeout tidak bisa diproses.')) {
                        return;
                    }
                    this.$refs.auditForm.submit();
                },
                get allSelectedOnPage() {
                    return this.pageIds.length > 0 && this.pageIds.every((id) => this.selectedIds.includes(id));
                },
                togglePageSelection(event) {
                    if (event.target.checked) {
                        this.selectedIds = Array.from(new Set([...this.selectedIds, ...this.pageIds]));
                    } else {
                        this.selectedIds = this.selectedIds.filter((id) => !this.pageIds.includes(id));
                        this.selectAllMatching = false;
                    }
                },
                toggleSelectAllMatching() {
                    this.selectAllMatching = !this.selectAllMatching;
                },
                selectAllInDatabase() {
                    this.selectedIds = Array.from(new Set([...this.selectedIds, ...this.pageIds]));
                    this.selectAllMatching = true;
                },
                clearSelection() {
                    this.selectedIds = [];
                    this.selectAllMatching = false;
                },
                openDetail(id) {
                    this.openDetailId = String(id);
                },
                closeDetail() {
                    this.openDetailId = null;
                },
                formatCurrency(value) {
                    if (value === null || value === undefined || value === '') return '-';
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
                },
                defaultPpnkNumber(index) {
                    return this.detailMap[this.ppnkTarget]?.items?.[index]?.ppnk_number ?? '';
                },
                defaultPpmNumber(index) {
                    return this.detailMap[this.ppmTarget]?.items?.[index]?.ppm_number ?? '';
                },
                /**
                 * Menghitung unit yang perlu input PPNK per item
                 * Mengembalikan array flat dimana setiap unit menjadi entry terpisah
                 */
                getPpnkItems() {
                    const items = this.detailMap[this.ppnkTarget]?.items ?? [];
                    const result = [];
                    items.forEach((item) => {
                        let qty = parseInt(item.quantity) || 0;
                        // Buat entry terpisah untuk setiap unit
                        for (let i = 0; i < qty; i++) {
                            result.push({
                                ...item,
                                unitIndex: i,
                                totalQty: qty,
                            });
                        }
                    });
                    return result;
                },
                /**
                 * Menghitung unit yang perlu input PPM per item
                 * Hanya item yang disetujui (bukan takeout)
                 */
                getPpmItems() {
                    const items = this.detailMap[this.ppmTarget]?.items ?? [];
                    const result = [];
                    items.forEach((item) => {
                        // Skip item yang full takeout
                        if (item.audit_status === 'takeout') {
                            return;
                        }
                        
                        let qty = parseInt(item.quantity) || 0;
                        // Buat entry terpisah untuk setiap unit
                        for (let i = 0; i < qty; i++) {
                            result.push({
                                ...item,
                                unitIndex: i,
                                totalQty: qty,
                            });
                        }
                    });
                    return result;
                },
                /**
                 * Menghitung unit yang perlu input PO per item
                 * Hanya item yang disetujui (bukan takeout)
                 */
                getPoItems() {
                    const items = this.detailMap[this.poTarget]?.items ?? [];
                    const result = [];
                    items.forEach((item) => {
                        // Skip item yang full takeout
                        if (item.audit_status === 'takeout') {
                            return;
                        }
                        
                        let qty = parseInt(item.quantity) || 0;
                        // Buat entry terpisah untuk setiap unit
                        for (let i = 0; i < qty; i++) {
                            result.push({
                                ...item,
                                unitIndex: i,
                                totalQty: qty,
                            });
                        }
                    });
                    return result;
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

            const tableElement = document.getElementById('ict-requests-table');

            if (!tableElement || typeof window.DataTable === 'undefined') {
                return;
            }

            const viewportHeight = window.innerHeight || document.documentElement.clientHeight || 900;
            const tableTop = tableElement.getBoundingClientRect().top || 0;
            // Sisakan ruang untuk toolbar datatable + pagination agar tetap terlihat.
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
                    emptyTable: 'Belum ada data.',
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
    <div
        x-data="ictRequestsData()"
        class="ui-page-workspace"
    >
        @if (session('status'))
            <x-alert>{{ session('status') }}</x-alert>
        @endif

        <x-card padding="none" class="ui-page-workspace-card">
            <div class="ui-page-toolbar">
                <form id="ict-requests-filter-form" method="GET" class="hidden"></form>
            </div>

            <div class="ui-page-table-content">
	            @if (auth()->user()->canCreateIctRequest())
	                <form method="POST" action="{{ route('forms.ict-requests.bulk-destroy') }}" class="ui-page-section-bar" x-on:submit="if (!selectAllMatching && selectedIds.length === 0) { $event.preventDefault(); } else if (!confirm('Hapus data yang dipilih?')) { $event.preventDefault(); }">
	                    @csrf
	                    @method('DELETE')
	                    <input type="hidden" name="from" value="{{ $filters['from'] }}">
	                    <input type="hidden" name="until" value="{{ $filters['until'] }}">
	                    <input type="hidden" name="sort" value="{{ $sort }}">
	                    <input type="hidden" name="direction" value="{{ $direction }}">
	                    <input type="hidden" name="select_all_matching" :value="selectAllMatching ? 1 : 0">

	                    <template x-for="id in selectedIds" :key="id">
	                        <input type="hidden" name="selected_ids[]" :value="id">
	                    </template>

	                    <div class="flex flex-wrap items-center justify-between gap-3 text-sm text-ink-700" x-cloak x-show="selectedIds.length > 0 || selectAllMatching">
                            <div class="flex flex-wrap items-center gap-2">
                                <label class="inline-flex items-center gap-1.5 text-[11px] font-medium text-ink-600">
                                    <span>Dari</span>
                                    <input type="date" name="from" form="ict-requests-filter-form" value="{{ $filters['from'] }}" class="h-7 w-[118px] rounded-lg border border-ink-200 bg-white px-2 text-[11px] text-ink-900 outline-none transition focus:border-brand-500" />
                                </label>
                                <label class="inline-flex items-center gap-1.5 text-[11px] font-medium text-ink-600">
                                    <span>Sampai</span>
                                    <input type="date" name="until" form="ict-requests-filter-form" value="{{ $filters['until'] }}" class="h-7 w-[118px] rounded-lg border border-ink-200 bg-white px-2 text-[11px] text-ink-900 outline-none transition focus:border-brand-500" />
                                </label>
                                <x-button type="submit" form="ict-requests-filter-form" size="compact" class="!px-2.5 !py-1 !text-[11px]">
                                    Terapkan
                                </x-button>
                                <x-button :href="route('forms.ict-requests.index')" variant="secondary" size="compact" class="!px-2.5 !py-1 !text-[11px]">
                                    <x-heroicon-o-arrow-path class="mr-1.5 h-3.5 w-3.5" />
                                    Reset
                                </x-button>
	                            <button type="submit" class="ui-page-danger-button !px-2.5 !py-1 !text-[11px]">
	                                <x-heroicon-o-trash class="mr-1.5 h-3.5 w-3.5" />
	                                Delete selected
	                            </button>
                                <x-button :href="route('forms.ict-requests.export', request()->query())" variant="secondary" size="compact" class="!px-2.5 !py-1 !text-[11px]">
                                    <x-heroicon-o-arrow-down-tray class="mr-1.5 h-3.5 w-3.5" />
                                    Export Excel
                                </x-button>
                                <x-button :href="route('forms.ict-requests.create')" size="compact" class="!px-2.5 !py-1 !text-[11px]">
                                    <x-heroicon-o-plus class="mr-1.5 h-3.5 w-3.5" />
                                    Buat Permintaan
                                </x-button>
                            </div>

	                        <div class="flex flex-wrap items-center gap-4">
	                            <span class="text-ink-600" x-text="selectAllMatching ? `${totalMatching} records selected` : `${selectedIds.length} records selected`"></span>
	                            <button
	                                type="button"
	                                x-on:click="selectAllInDatabase()"
	                                x-show="!selectAllMatching && selectedIds.length > 0 && totalMatching > selectedIds.length"
	                                class="text-brand-700 hover:underline"
	                            >
	                                Select all <span x-text="totalMatching"></span> data
	                            </button>
	                            <button type="button" x-on:click="clearSelection()" class="text-danger-600 hover:underline">Deselect all</button>
	                        </div>
	                    </div>

	                    <div class="flex flex-wrap items-center justify-between gap-3" x-cloak x-show="selectedIds.length === 0 && !selectAllMatching">
                            <div class="flex flex-wrap items-center gap-2">
                                <label class="inline-flex items-center gap-1.5 text-[11px] font-medium text-ink-600">
                                    <span>Dari</span>
                                    <input type="date" name="from" form="ict-requests-filter-form" value="{{ $filters['from'] }}" class="h-7 w-[118px] rounded-lg border border-ink-200 bg-white px-2 text-[11px] text-ink-900 outline-none transition focus:border-brand-500" />
                                </label>
                                <label class="inline-flex items-center gap-1.5 text-[11px] font-medium text-ink-600">
                                    <span>Sampai</span>
                                    <input type="date" name="until" form="ict-requests-filter-form" value="{{ $filters['until'] }}" class="h-7 w-[118px] rounded-lg border border-ink-200 bg-white px-2 text-[11px] text-ink-900 outline-none transition focus:border-brand-500" />
                                </label>
                                <x-button type="submit" form="ict-requests-filter-form" size="compact" class="!px-2.5 !py-1 !text-[11px]">
                                    Terapkan
                                </x-button>
                                <x-button :href="route('forms.ict-requests.index')" variant="secondary" size="compact" class="!px-2.5 !py-1 !text-[11px]">
                                    <x-heroicon-o-arrow-path class="mr-1.5 h-3.5 w-3.5" />
                                    Reset
                                </x-button>
                                <div class="ui-page-record-count">
	                                Total {{ $requests->count() }} data
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <x-button :href="route('forms.ict-requests.export', request()->query())" variant="secondary" size="compact" class="!px-2.5 !py-1 !text-[11px]">
                                    <x-heroicon-o-arrow-down-tray class="mr-1.5 h-3.5 w-3.5" />
                                    Export Excel
                                </x-button>
                                <x-button :href="route('forms.ict-requests.create')" size="compact" class="!px-2.5 !py-1 !text-[11px]">
                                    <x-heroicon-o-plus class="mr-1.5 h-3.5 w-3.5" />
                                    Buat Permintaan
                                </x-button>
                            </div>
	                    </div>
	                </form>
	            @else
	                <div class="ui-page-section-bar ui-page-record-count">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <label class="inline-flex items-center gap-1.5 text-[11px] font-medium text-ink-600">
                                    <span>Dari</span>
                                    <input type="date" name="from" form="ict-requests-filter-form" value="{{ $filters['from'] }}" class="h-7 w-[118px] rounded-lg border border-ink-200 bg-white px-2 text-[11px] text-ink-900 outline-none transition focus:border-brand-500" />
                                </label>
                                <label class="inline-flex items-center gap-1.5 text-[11px] font-medium text-ink-600">
                                    <span>Sampai</span>
                                    <input type="date" name="until" form="ict-requests-filter-form" value="{{ $filters['until'] }}" class="h-7 w-[118px] rounded-lg border border-ink-200 bg-white px-2 text-[11px] text-ink-900 outline-none transition focus:border-brand-500" />
                                </label>
                                <x-button type="submit" form="ict-requests-filter-form" size="compact" class="!px-2.5 !py-1 !text-[11px]">
                                    Terapkan
                                </x-button>
                                <x-button :href="route('forms.ict-requests.index')" variant="secondary" size="compact" class="!px-2.5 !py-1 !text-[11px]">
                                    <x-heroicon-o-arrow-path class="mr-1.5 h-3.5 w-3.5" />
                                    Reset
                                </x-button>
                                <span>Total {{ $requests->count() }} data</span>
                            </div>
                            <x-button :href="route('forms.ict-requests.export', request()->query())" variant="secondary" size="compact" class="!px-2.5 !py-1 !text-[11px]">
                                <x-heroicon-o-arrow-down-tray class="mr-1.5 h-3.5 w-3.5" />
                                Export Excel
                            </x-button>
                        </div>
	                </div>
	            @endif

	            <div class="ui-datatable-shell">
	                <table id="ict-requests-table" class="ui-table-compact">
	                    <thead>
	                        <tr>
	                            <th>
	                                <label class="inline-flex items-center gap-2">
	                                    <input type="checkbox" :checked="allSelectedOnPage" x-on:change="togglePageSelection($event)" class="rounded border-ink-300 text-ink-900 focus:ring-ink-400" />
	                                    <span class="sr-only">Pilih semua</span>
	                                </label>
	                            </th>
	                            <th class="ui-table-cell-nowrap">
	                                <x-sort-link column="created_at" label="Tanggal" :sort="$sort" :direction="$direction" size="compact" />
	                            </th>
	                            <th class="ui-table-cell-nowrap">
	                                <x-sort-link column="subject" label="Subject" :sort="$sort" :direction="$direction" size="compact" />
	                            </th>
	                            <th class="ui-table-cell-nowrap">Pemohon</th>
	                            <th class="ui-table-cell-nowrap">Dept</th>
	                            <th>
	                                <x-sort-link column="priority" label="Prioritas" :sort="$sort" :direction="$direction" size="compact" />
	                            </th>
	                            <th class="ui-table-cell-fixed-wide">Alasan kebutuhan</th>
	                            <th class="ui-table-cell-nowrap">
	                                <x-sort-link column="status" label="Status" :sort="$sort" :direction="$direction" size="compact" />
	                            </th>
	                            <th class="text-right">Aksi</th>
	                        </tr>
	                    </thead>
	                    <tbody class="divide-y divide-ink-100">
	                    @foreach ($requests as $request)
	                        <tr>
                            <td>
                                <input type="checkbox" value="{{ $request->id }}" x-model="selectedIds" class="rounded border-ink-300 text-ink-900 focus:ring-ink-400" />
                            </td>
                            <td class="ui-table-cell-nowrap">{{ $request->created_at?->format('d M Y') }}</td>
                            <td>
                                <div class="ui-table-cell-nowrap font-semibold text-ink-900">{{ $request->subject }}</div>
                                @if ($request->rejected_reason || $request->revision_note)
                                    <div class="mt-3 space-y-2 rounded-2xl border border-amber-200 bg-amber-50/80 p-3 text-xs text-ink-700">
                                        @if ($request->rejected_reason)
                                            <div>
                                                <span class="font-semibold text-danger-600">Reject:</span>
                                                {{ \Illuminate\Support\Str::limit($request->rejected_reason, 180) }}
                                            </div>
                                        @endif
                                        @if ($request->revision_note)
                                            <div>
                                                <span class="font-semibold text-amber-700">Revisi:</span>
                                                {{ \Illuminate\Support\Str::limit($request->revision_note, 180) }}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                                @if ($request->revision_attachment_path)
                                    <a href="{{ \Illuminate\Support\Facades\Storage::url($request->revision_attachment_path) }}" target="_blank" class="mt-3 block">
                                        <img
                                            src="{{ \Illuminate\Support\Facades\Storage::url($request->revision_attachment_path) }}"
                                            alt="{{ $request->revision_attachment_name ?: 'Lampiran revisi' }}"
                                            class="h-20 w-28 rounded-2xl border border-amber-200 object-cover shadow-sm"
                                        />
                                    </a>
                                @endif
                            </td>
                            <td class="ui-table-cell-nowrap">{{ $request->requesterDisplayName() }}</td>
                            <td class="ui-table-cell-nowrap">{{ $request->departmentDisplayName() }}</td>
                            <td><x-badge size="compact" variant="{{ $request->priority === 'urgent' ? 'warning' : 'default' }}">{{ strtoupper($request->priority) }}</x-badge></td>
                            <td class="ui-table-cell-fixed-wide ui-table-cell-wrap">{{ \Illuminate\Support\Str::limit($request->justification, 180) }}</td>
                            <td class="ui-table-cell-nowrap"><x-badge size="compact" variant="{{ in_array($request->status, ['progress_ppnk'], true) || ($request->status === 'checked_by_asmen' && (int) $request->print_count > 0 && ! $request->final_signed_pdf_path) ? 'warning' : 'success' }}">{{ $request->statusLabel() }}</x-badge></td>
                            <td>
                                <div class="ui-action-row ui-action-row--compact justify-end">
                                    <x-button type="button" variant="action-neutral" x-on:click="openDetail('{{ $request->id }}')" title="Lihat detail">
                                        <x-heroicon-o-eye class="ui-action-icon" />
                                    </x-button>

                                    @if (auth()->user()->canCreateIctRequest() && !in_array($request->status, ['checked_by_asmen', 'progress_ppnk', 'progress_verifikasi_audit', 'progress_ppm', 'progress_po', 'progress_waiting_goods', 'completed'], true))
                                        <x-button :href="route('forms.ict-requests.edit', $request)" variant="action-neutral" title="Edit">
                                            <x-heroicon-o-pencil-square class="ui-action-icon" />
                                        </x-button>
                                    @endif

                                    @if (($request->status === 'checked_by_asmen') && (auth()->user()->isIctAdmin() || auth()->user()->isStaffIct()))
                                        <x-button type="button" variant="action-neutral" x-on:click="openPrintModal('{{ $request->id }}')" title="{{ (int) $request->print_count > 0 ? 'Print Ulang' : 'Print' }}">
                                            <x-heroicon-o-printer class="ui-action-icon" />
                                        </x-button>
                                    @endif

                                    @if ($request->status === 'checked_by_asmen' && (int) $request->print_count > 0 && auth()->user()->isIctAdmin() && ! $request->final_signed_pdf_path)
                                        <button type="button" x-on:click="openSignedPdfModal('{{ $request->id }}')" class="ui-action-button ui-action-button--upload" title="Upload Form ICT Full TTD">
                                                <x-heroicon-o-arrow-up-tray class="ui-action-icon" />
                                                <span class="sr-only">Upload Form ICT Full TTD</span>
                                        </button>
                                    @endif

                                    @if ($request->status === 'progress_ppnk' && auth()->user()->isIctAdmin())
                                        <x-button type="button" variant="action-review" x-on:click="openPpnkModal('{{ $request->id }}')" title="Upload Data PPNK/PPK">
                                            <x-heroicon-o-clipboard-document-list class="ui-action-icon" />
                                        </x-button>
                                    @endif

                                    @if ($request->status === 'progress_ppm' && auth()->user()->isIctAdmin())
                                        <x-button type="button" variant="action-review" x-on:click="openPpmModal('{{ $request->id }}')" title="Upload Data PPM">
                                            <x-heroicon-o-document-chart-bar class="ui-action-icon" />
                                        </x-button>
                                    @endif

                                    @if ($request->status === 'progress_po' && auth()->user()->isIctAdmin())
                                        <x-button type="button" variant="action-review" x-on:click="openPoModal('{{ $request->id }}')" title="Upload Data PO">
                                            <x-heroicon-o-clipboard-document-check class="ui-action-icon" />
                                        </x-button>
                                    @endif

                                    @if ($request->status === 'progress_waiting_goods' && auth()->user()->isIctAdmin())
                                        <x-button type="button" variant="action-success" x-on:click="openGoodsReceiptModal('{{ $request->id }}')" title="Penerimaan Barang">
                                            <x-heroicon-o-check-circle class="ui-action-icon" />
                                        </x-button>
                                    @endif

                                    @if ($request->status === 'progress_verifikasi_audit' && auth()->user()->isIctAdmin())
                                        <x-button type="button" variant="action-review" x-on:click="openAuditModal('{{ $request->id }}')" title="Verifikasi Audit">
                                            <x-heroicon-o-clipboard-document-check class="ui-action-icon" />
                                        </x-button>
                                    @endif

                                    @if (auth()->user()->canCreateIctRequest() && !in_array($request->status, ['checked_by_asmen', 'progress_ppnk', 'progress_verifikasi_audit', 'progress_ppm', 'progress_po', 'progress_waiting_goods', 'completed'], true))
                                        <form method="POST" action="{{ route('forms.ict-requests.bulk-destroy') }}" onsubmit="return confirm('Hapus permintaan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="selected_ids[]" value="{{ $request->id }}">
                                            <x-button type="submit" variant="action-danger" title="Hapus">
                                                <x-heroicon-o-trash class="ui-action-icon" />
                                            </x-button>
                                        </form>
                                    @endif

                                    @if (auth()->user()->canPermanentDeleteIctRequest())
                                        <form method="POST" action="{{ route('forms.ict-requests.permanent-destroy', $request) }}" onsubmit="return confirm('Hapus data ini secara permanen? Tindakan ini tidak dapat dibatalkan.')">
                                            @csrf
                                            @method('DELETE')
                                            <x-button type="submit" variant="action-danger" title="Hapus Permanen">
                                                <x-heroicon-o-trash class="ui-action-icon" />
                                            </x-button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        </x-card>

        <div
            x-show="openDetailId"
            x-cloak
            x-transition.opacity.duration.200ms
            x-on:keydown.escape.window="closeDetail()"
            class="fixed inset-0 z-50 flex items-center justify-center bg-ink-900/50 p-4"
        >
            <div class="max-h-[90vh] w-full max-w-5xl overflow-hidden rounded-3xl bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-ink-100 px-6 py-4">
                    <div>
                        <h2 class="font-display text-xl font-semibold text-ink-900">Detail Form ICT</h2>
                        <p class="text-sm text-ink-500" x-text="`${detailMap[openDetailId]?.subject ?? ''} - ${detailMap[openDetailId]?.requester ?? '-'}`"></p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a :href="detailMap[openDetailId]?.copy_pdf_url" target="_blank" class="inline-flex items-center gap-2 rounded-2xl border border-ink-200 px-4 py-2 text-sm font-semibold text-ink-700 transition hover:bg-ink-50">
                            <x-heroicon-o-document-text class="h-4 w-4" />
                            <span>View PDF Copy</span>
                        </a>
                        <template x-if="detailMap[openDetailId]?.can_print">
                            <button type="button" x-on:click="openPrintModal(openDetailId)" class="inline-flex items-center gap-2 rounded-2xl border border-ink-200 px-4 py-2 text-sm font-semibold text-ink-700 transition hover:bg-ink-50">
                                <x-heroicon-o-printer class="h-4 w-4" />
                                <span>Print</span>
                            </button>
                        </template>
                        <button type="button" x-on:click="closeDetail()" class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-ink-200 text-ink-600 transition hover:bg-ink-50">
                            <x-heroicon-o-x-mark class="h-5 w-5" />
                        </button>
                    </div>
                </div>

                <div class="max-h-[calc(90vh-80px)] space-y-6 overflow-y-auto px-6 py-5">
                    <div class="grid gap-4 md:grid-cols-5">
                        <div class="rounded-2xl border border-ink-100 bg-ink-50/60 p-4">
                            <div class="text-xs uppercase tracking-wide text-ink-400">Total Harga Permintaan</div>
                            <div class="mt-2 font-semibold text-ink-900" x-text="formatCurrency(detailMap[openDetailId]?.total_estimated_price)"></div>
                        </div>
                        <div class="rounded-2xl border border-ink-100 bg-ink-50/60 p-4">
                            <div class="text-xs uppercase tracking-wide text-ink-400">Unit</div>
                            <div class="mt-2 font-semibold text-ink-900" x-text="detailMap[openDetailId]?.unit ?? '-'"></div>
                        </div>
                        <div class="rounded-2xl border border-ink-100 bg-ink-50/60 p-4">
                            <div class="text-xs uppercase tracking-wide text-ink-400">Prioritas</div>
                            <div class="mt-2 font-semibold text-ink-900" x-text="detailMap[openDetailId]?.priority ?? '-'"></div>
                        </div>
                        <div class="rounded-2xl border border-ink-100 bg-ink-50/60 p-4">
                            <div class="text-xs uppercase tracking-wide text-ink-400">Status</div>
                            <div class="mt-2 font-semibold text-ink-900" x-text="detailMap[openDetailId]?.status ?? '-'"></div>
                        </div>
                        <div class="rounded-2xl border border-ink-100 bg-ink-50/60 p-4">
                            <div class="text-xs uppercase tracking-wide text-ink-400">Revisi</div>
                            <div class="mt-2 font-semibold text-ink-900" x-text="`Rev-${detailMap[openDetailId]?.revision_number ?? 0}`"></div>
                        </div>
                    </div>

                    <div x-show="detailMap[openDetailId]?.revision_note || detailMap[openDetailId]?.revision_attachment_url || detailMap[openDetailId]?.rejected_reason" x-cloak class="rounded-3xl border border-amber-200 bg-amber-50/80 p-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="space-y-3">
                                <div>
                                    <h3 class="font-display text-lg font-semibold text-ink-900">Feedback Approval</h3>
                                    <p class="text-sm text-ink-500">Catatan reject atau revisi terakhir dari approver ICT.</p>
                                </div>
                                <template x-if="detailMap[openDetailId]?.rejected_reason">
                                    <div>
                                        <div class="text-xs uppercase tracking-wide text-ink-400">Alasan Reject</div>
                                        <div class="mt-2 text-sm text-ink-800" x-text="detailMap[openDetailId]?.rejected_reason"></div>
                                    </div>
                                </template>
                                <template x-if="detailMap[openDetailId]?.revision_note">
                                    <div>
                                        <div class="text-xs uppercase tracking-wide text-ink-400">Catatan Revisi</div>
                                        <div class="mt-2 text-sm text-ink-800" x-text="detailMap[openDetailId]?.revision_note"></div>
                                    </div>
                                </template>
                            </div>

                            <template x-if="detailMap[openDetailId]?.revision_attachment_url">
                                <a :href="detailMap[openDetailId]?.revision_attachment_url" target="_blank" class="inline-flex items-center gap-2 rounded-2xl border border-amber-300 bg-white px-4 py-3 text-sm font-semibold text-amber-800 transition hover:bg-amber-100">
                                    <x-heroicon-o-paper-clip class="h-4 w-4" />
                                    <span x-text="detailMap[openDetailId]?.revision_attachment_name || 'Download Lampiran Revisi'"></span>
                                </a>
                            </template>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-ink-100 p-5">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="font-display text-lg font-semibold text-ink-900">Penawaran Global</h3>
                            <span class="text-xs uppercase tracking-wide text-ink-400" x-text="detailMap[openDetailId]?.quotation_mode === 'global' ? 'Aktif' : 'Tidak aktif'"></span>
                        </div>
                        <div class="space-y-3" x-show="detailMap[openDetailId]?.global_quotations?.length">
                            <template x-for="(quotation, index) in detailMap[openDetailId]?.global_quotations ?? []" :key="`global-${index}`">
                                <div class="rounded-2xl border border-ink-100 bg-ink-50/50 p-4">
                                    <div class="font-semibold text-ink-900" x-text="quotation.vendor_name || `Vendor ${index + 1}`"></div>
                                    <template x-if="quotation.attachment_url">
                                        <div class="mt-3 space-y-3">
                                            <a :href="quotation.attachment_url" target="_blank" class="inline-flex items-center gap-2 text-sm font-semibold text-brand-700 hover:text-brand-800">
                                                <x-heroicon-o-paper-clip class="h-4 w-4" />
                                                <span x-text="quotation.attachment_name || 'Buka lampiran'"></span>
                                            </a>
                                            <template x-if="quotation.is_image">
                                                <img :src="quotation.attachment_url" alt="" class="max-h-56 rounded-2xl border border-ink-100 object-cover" />
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                        <p x-show="!(detailMap[openDetailId]?.global_quotations?.length)" class="text-sm text-ink-500">Tidak ada penawaran global.</p>
                    </div>

                    <div class="rounded-3xl border border-ink-100 p-5">
                        <div class="mb-3 flex items-center gap-2 text-sm font-semibold text-ink-900">
                            <x-heroicon-o-document-arrow-up class="h-4 w-4" />
                            PDF Form ICT
                        </div>
                        <a :href="detailMap[openDetailId]?.copy_pdf_url" target="_blank" class="inline-flex items-center gap-2 text-sm font-semibold text-brand-700 hover:text-brand-800">
                            <x-heroicon-o-arrow-top-right-on-square class="h-4 w-4" />
                            <span>Lihat PDF copy hitam putih</span>
                        </a>
                        <div class="mt-4 border-t border-ink-100 pt-4">
                            <div class="mb-2 text-sm font-semibold text-ink-900">PDF Form ICT Full TTD</div>
                        <template x-if="detailMap[openDetailId]?.final_signed_pdf_url">
                            <a :href="detailMap[openDetailId]?.final_signed_pdf_url" target="_blank" class="inline-flex items-center gap-2 text-sm font-semibold text-brand-700 hover:text-brand-800">
                                <x-heroicon-o-arrow-top-right-on-square class="h-4 w-4" />
                                <span x-text="detailMap[openDetailId]?.final_signed_pdf_name || 'Buka PDF final'"></span>
                            </a>
                        </template>
                        <p x-show="!detailMap[openDetailId]?.final_signed_pdf_url" class="text-sm text-ink-500">Belum ada upload PDF TTD lengkap.</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <template x-for="(item, index) in detailMap[openDetailId]?.items ?? []" :key="`item-${index}`">
                            <div class="rounded-3xl border border-ink-100 p-5">
                                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                                    <div>
                                        <div class="text-xs uppercase tracking-wide text-ink-400">Kategori</div>
                                        <div class="mt-2 font-semibold text-ink-900" x-text="item.item_category || '-'"></div>
                                    </div>
                                    <div>
                                        <div class="text-xs uppercase tracking-wide text-ink-400">Nama Barang</div>
                                        <div class="mt-2 font-semibold text-ink-900" x-text="item.item_name || '-'"></div>
                                    </div>
                                    <div>
                                        <div class="text-xs uppercase tracking-wide text-ink-400">Merk / Tipe</div>
                                        <div class="mt-2 font-semibold text-ink-900" x-text="item.brand_type || '-'"></div>
                                    </div>
                                    <div>
                                        <div class="text-xs uppercase tracking-wide text-ink-400">Jumlah</div>
                                        <div class="mt-2 font-semibold text-ink-900" x-text="`${item.quantity || 0} ${item.unit || ''}`"></div>
                                    </div>
                                    <div>
                                        <div class="text-xs uppercase tracking-wide text-ink-400">Estimasi Harga</div>
                                        <div class="mt-2 font-semibold text-ink-900" x-text="formatCurrency(item.estimated_price)"></div>
                                    </div>
                                    <div>
                                        <div class="text-xs uppercase tracking-wide text-ink-400">Total Harga Barang</div>
                                        <div class="mt-2 font-semibold text-ink-900" x-text="formatCurrency(item.total_estimated_price)"></div>
                                    </div>
                                    <div>
                                        <div class="text-xs uppercase tracking-wide text-ink-400">Nomor PPNK / PPK</div>
                                        <div class="mt-2 font-semibold text-ink-900" x-text="item.ppnk_number || '-'"></div>
                                    </div>
                                    <div class="md:col-span-2 xl:col-span-3">
                                        <div class="text-xs uppercase tracking-wide text-ink-400">Keterangan</div>
                                        <div class="mt-2 text-sm text-ink-700" x-text="item.notes || '-'"></div>
                                    </div>
                                </div>

                                <div class="mt-5 grid gap-4 lg:grid-cols-[320px_minmax(0,1fr)]">
                                    <div class="rounded-2xl border border-ink-100 bg-ink-50/50 p-4">
                                        <div class="mb-3 flex items-center gap-2 text-sm font-semibold text-ink-900">
                                            <x-heroicon-o-photo class="h-4 w-4" />
                                            Foto Barang
                                        </div>
                                        <template x-if="item.photo_url">
                                            <div class="space-y-3">
                                                <img :src="item.photo_url" :alt="item.photo_name || item.item_name" class="max-h-64 w-full rounded-2xl border border-ink-100 object-cover" />
                                                <div class="text-sm text-ink-600" x-text="item.photo_name || item.item_name"></div>
                                            </div>
                                        </template>
                                        <p x-show="!item.photo_url" class="text-sm text-ink-500">Tidak ada foto.</p>
                                    </div>

                                    <div class="rounded-2xl border border-ink-100 bg-ink-50/50 p-4">
                                        <div class="mb-3 flex items-center gap-2 text-sm font-semibold text-ink-900">
                                            <x-heroicon-o-paper-clip class="h-4 w-4" />
                                            Penawaran Per Barang
                                        </div>
                                        <div class="space-y-3" x-show="item.quotations?.length">
                                            <template x-for="(quotation, quotationIndex) in item.quotations" :key="`item-quotation-${quotationIndex}`">
                                                <div class="rounded-2xl border border-ink-100 bg-white p-4">
                                                    <div class="font-semibold text-ink-900" x-text="quotation.vendor_name || `Vendor ${quotationIndex + 1}`"></div>
                                                    <template x-if="quotation.attachment_url">
                                                        <div class="mt-3 space-y-3">
                                                            <a :href="quotation.attachment_url" target="_blank" class="inline-flex items-center gap-2 text-sm font-semibold text-brand-700 hover:text-brand-800">
                                                                <x-heroicon-o-arrow-top-right-on-square class="h-4 w-4" />
                                                                <span x-text="quotation.attachment_name || 'Buka lampiran'"></span>
                                                            </a>
                                                            <template x-if="quotation.is_image">
                                                                <img :src="quotation.attachment_url" alt="" class="max-h-40 rounded-2xl border border-ink-100 object-cover" />
                                                            </template>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                        <p x-show="!(item.quotations?.length)" class="text-sm text-ink-500">Tidak ada penawaran per barang.</p>
                                    </div>
                                </div>

                                <div class="mt-4 rounded-2xl border border-ink-100 bg-ink-50/50 p-4">
                                    <div class="mb-3 flex items-center gap-2 text-sm font-semibold text-ink-900">
                                        <x-heroicon-o-clipboard-document-list class="h-4 w-4" />
                                        Dokumen PPNK / PPK
                                    </div>
                                    <template x-if="item.ppnk_attachment_url">
                                        <div class="space-y-3">
                                            <a :href="item.ppnk_attachment_url" target="_blank" class="inline-flex items-center gap-2 text-sm font-semibold text-brand-700 hover:text-brand-800">
                                                <x-heroicon-o-arrow-top-right-on-square class="h-4 w-4" />
                                                <span x-text="item.ppnk_attachment_name || 'Buka dokumen PPNK / PPK'"></span>
                                            </a>
                                            <template x-if="item.ppnk_attachment_is_image">
                                                <img :src="item.ppnk_attachment_url" alt="" class="max-h-40 rounded-2xl border border-ink-100 object-cover" />
                                            </template>
                                        </div>
                                    </template>
                                    <p x-show="!item.ppnk_attachment_url" class="text-sm text-ink-500">Belum ada dokumen PPNK / PPK untuk barang ini.</p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div
            x-show="ppnkTarget"
            x-cloak
            x-transition.opacity.duration.200ms
            x-on:keydown.escape.window="closePpnkModal()"
            class="fixed inset-0 z-[70] flex items-center justify-center bg-ink-900/50 p-4"
        >
            <div class="flex max-h-[90vh] w-full max-w-4xl flex-col overflow-hidden rounded-3xl bg-white shadow-2xl">
                <div class="flex items-start justify-between gap-4 border-b border-ink-100 px-5 py-4">
                    <div>
                        <h3 class="font-display text-lg font-semibold text-ink-900">Upload Data PPNK / PPK</h3>
                        <p class="mt-1 text-sm text-ink-500" x-text="detailMap[ppnkTarget]?.subject || ''"></p>
                    </div>
                    <button type="button" x-on:click="closePpnkModal()" class="inline-flex h-8 w-8 items-center justify-center rounded-2xl border border-ink-200 text-ink-600 transition hover:bg-ink-50">
                        <x-heroicon-o-x-mark class="h-4 w-4" />
                    </button>
                </div>

                                <form x-ref="ppnkForm" method="POST" enctype="multipart/form-data" x-on:submit.prevent="submitPpnkForm()" class="flex min-h-0 flex-1 flex-col">
                    @csrf

                    <div class="flex-1 overflow-y-auto px-5 py-4">
                        <p class="mb-4 text-xs text-ink-500">Isi nomor per unit barang. Jika nomor sama, cukup upload file pada salah satu baris dengan nomor yang sama.</p>

                        <div class="space-y-3">
                            <template x-for="(item, index) in getPpnkItems()" :key="`ppnk-${item.id}-${item.unitIndex}`">
                                <div class="rounded-2xl border border-ink-100 bg-ink-50/50 p-4">
                                    <div class="font-semibold text-ink-900" x-text="item.item_name"></div>
                                    <div class="mt-1 text-xs text-ink-500">
                                        <span x-text="`Unit ${item.unitIndex + 1} dari ${item.totalQty}`"></span>
                                        <span class="mx-1">•</span>
                                        <span x-text="item.brand_type || item.item_category || '-'"></span>
                                    </div>

                                    <input type="hidden" :name="`items[${index}][item_id]`" :value="item.id" />
                                    <input type="hidden" :name="`items[${index}][unit_index]`" :value="item.unitIndex" />

                                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                                        <div>
                                            <label class="block text-xs font-medium text-ink-600">Nomor PPNK / PPK</label>
                                            <input
                                                type="text"
                                                :name="`items[${index}][ppnk_number]`"
                                                :value="defaultPpnkNumber(index)"
                                                placeholder="Contoh: PPNK-001/ICT/2026"
                                                class="mt-1 w-full rounded-xl border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-900 outline-none transition focus:border-brand-500"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-ink-600">Upload Berkas</label>
                                            <input
                                                type="file"
                                                :name="`items[${index}][ppnk_attachment]`"
                                                accept=".pdf,.jpg,.jpeg,.png,.webp"
                                                class="mt-1 w-full rounded-xl border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-900 outline-none transition file:mr-2 file:rounded-lg file:border-0 file:bg-ink-100 file:px-2 file:py-1.5 file:text-xs file:font-semibold file:text-ink-700 hover:file:bg-ink-200 focus:border-brand-500"
                                            />
                                        </div>
                                    </div>

                                    <template x-if="item.ppnk_attachment_name">
                                        <div class="mt-2 text-xs text-amber-700">
                                            File saat ini:
                                            <a :href="item.ppnk_attachment_url" target="_blank" class="font-semibold hover:underline" x-text="item.ppnk_attachment_name"></a>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="border-t border-ink-100 px-5 py-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-xs text-ink-500">Berkas dapat berupa PDF atau gambar. Nomor yang sama akan memakai satu dokumen yang sama.</p>
                            <div class="flex justify-end gap-2">
                                <x-button type="button" variant="secondary" x-on:click="closePpnkModal()" class="px-4 py-2.5">Batal</x-button>
                                <x-button type="submit" class="px-4 py-2.5">Simpan PPNK / PPK</x-button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <form x-ref="printForm" method="POST" target="_blank" class="hidden">
            @csrf
        </form>

        <div
            x-show="printTarget"
            x-cloak
            x-transition.opacity.duration.200ms
            class="fixed inset-0 z-[60] flex items-center justify-center bg-ink-900/50 p-4"
        >
            <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl">
                <h3 class="font-display text-lg font-semibold text-ink-900">Konfirmasi Print</h3>
                <p class="mt-3 text-sm text-ink-600">
                    Pastikan data form sudah benar. Cetak pertama akan membuka PDF asli. Cetak berikutnya otomatis menjadi hitam putih dengan watermark <strong>DOCUMENT COPY</strong>.
                </p>
                <div class="mt-5 flex justify-end gap-3">
                    <x-button type="button" variant="secondary" x-on:click="closePrintModal()">Batal</x-button>
                    <x-button type="button" x-on:click="submitPrint()">Print</x-button>
                </div>
            </div>
        </div>

        <div
            x-show="signedPdfTarget"
            x-cloak
            x-transition.opacity.duration.200ms
            x-on:keydown.escape.window="closeSignedPdfModal()"
            class="fixed inset-0 z-[70] flex items-center justify-center bg-ink-900/50 p-4"
        >
            <div class="w-full max-w-lg rounded-3xl bg-white shadow-2xl">
                <div class="flex items-start justify-between gap-4 border-b border-ink-100 px-5 py-4">
                    <div>
                        <h3 class="font-display text-lg font-semibold text-ink-900">Upload Form ICT Full TTD</h3>
                        <p class="mt-1 text-sm text-ink-500" x-text="detailMap[signedPdfTarget]?.subject || ''"></p>
                    </div>
                    <button type="button" x-on:click="closeSignedPdfModal()" class="inline-flex h-8 w-8 items-center justify-center rounded-2xl border border-ink-200 text-ink-600 transition hover:bg-ink-50">
                        <x-heroicon-o-x-mark class="h-4 w-4" />
                    </button>
                </div>

                <form x-ref="signedPdfForm" method="POST" enctype="multipart/form-data" x-on:submit.prevent="submitSignedPdfForm()" class="space-y-4 px-5 py-4">
                    @csrf
                    <input type="hidden" name="action" value="upload_signed_pdf">

                    <label class="block space-y-2">
                        <span class="text-sm font-medium text-ink-700">Tanggal Upload</span>
                        <input
                            type="date"
                            name="signed_date"
                            x-model="signedPdfDate"
                            required
                            class="w-full rounded-2xl border border-ink-200 bg-white px-4 py-3 text-sm text-ink-900 outline-none transition focus:border-brand-500"
                        />
                    </label>

                    <label class="block space-y-2">
                        <span class="text-sm font-medium text-ink-700">Attach File</span>
                        <input
                            type="file"
                            name="signed_pdf"
                            accept="application/pdf"
                            required
                            class="w-full rounded-2xl border border-ink-200 bg-white px-4 py-3 text-sm text-ink-900 outline-none transition file:mr-4 file:rounded-xl file:border-0 file:bg-ink-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-ink-700 hover:file:bg-ink-200 focus:border-brand-500"
                        />
                        <p class="text-xs text-ink-500">Format file wajib PDF.</p>
                    </label>

                    <div class="flex justify-end gap-2 border-t border-ink-100 pt-4">
                        <x-button type="button" variant="secondary" x-on:click="closeSignedPdfModal()">Batal</x-button>
                        <x-button type="submit">Upload</x-button>
                    </div>
                </form>
            </div>
        </div>

        <div
            x-show="ppmTarget"
            x-cloak
            x-transition.opacity.duration.200ms
            x-on:keydown.escape.window="closePpmModal()"
            class="fixed inset-0 z-[70] flex items-center justify-center bg-ink-900/50 p-4"
        >
            <div class="flex max-h-[90vh] w-full max-w-4xl flex-col overflow-hidden rounded-3xl bg-white shadow-2xl">
                <div class="flex items-start justify-between gap-4 border-b border-ink-100 px-5 py-4">
                    <div>
                        <h3 class="font-display text-lg font-semibold text-ink-900">Upload Data PPM</h3>
                        <p class="mt-1 text-sm text-ink-500" x-text="detailMap[ppmTarget]?.subject || ''"></p>
                    </div>
                    <button type="button" x-on:click="closePpmModal()" class="inline-flex h-8 w-8 items-center justify-center rounded-2xl border border-ink-200 text-ink-600 transition hover:bg-ink-50">
                        <x-heroicon-o-x-mark class="h-4 w-4" />
                    </button>
                </div>

                <form x-ref="ppmForm" method="POST" enctype="multipart/form-data" x-on:submit.prevent="submitPpmForm()" class="flex min-h-0 flex-1 flex-col">
                    @csrf

                    <div class="flex-1 overflow-y-auto px-5 py-4">
                        <p class="mb-4 text-xs text-ink-500">Isi data PPM dan PR per unit barang yang disetujui (bukan takeout). Jika nomor PPM sama, cukup upload file pada salah satu baris.</p>

                        <div class="space-y-3">
                            <template x-for="(item, index) in getPpmItems()" :key="`ppm-${item.id}-${item.unitIndex}`">
                                <div class="rounded-2xl border border-ink-100 bg-ink-50/50 p-4">
                                    <div class="font-semibold text-ink-900" x-text="item.item_name"></div>
                                    <div class="mt-1 text-xs text-ink-500">
                                        <span x-text="`Unit ${item.unitIndex + 1} dari ${item.totalQty}`"></span>
                                        <span class="mx-1">•</span>
                                        <span x-text="item.brand_type || item.item_category || '-'"></span>
                                    </div>

                                    <input type="hidden" :name="`items[${index}][item_id]`" :value="item.id" />
                                    <input type="hidden" :name="`items[${index}][unit_index]`" :value="item.unitIndex" />

                                    <div class="mt-3 grid gap-3 md:grid-cols-4">
                                        <div>
                                            <label class="block text-xs font-medium text-ink-600">No. Urut</label>
                                            <input
                                                type="text"
                                                :name="`items[${index}][line_number]`"
                                                placeholder="Contoh: 4"
                                                class="mt-1 w-full rounded-xl border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-900 outline-none transition focus:border-brand-500"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-ink-600">No. PPM</label>
                                            <input
                                                type="text"
                                                :name="`items[${index}][ppm_number]`"
                                                placeholder="PP-JAR-0024-III-2024"
                                                class="mt-1 w-full rounded-xl border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-900 outline-none transition focus:border-brand-500"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-ink-600">No. PR</label>
                                            <input
                                                type="text"
                                                :name="`items[${index}][pr_number]`"
                                                placeholder="3000043632"
                                                class="mt-1 w-full rounded-xl border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-900 outline-none transition focus:border-brand-500"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-ink-600">Upload Berkas</label>
                                            <input
                                                type="file"
                                                :name="`items[${index}][ppm_attachment]`"
                                                accept=".pdf,.jpg,.jpeg,.png,.webp"
                                                class="mt-1 w-full rounded-xl border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-900 outline-none transition file:mr-2 file:rounded-lg file:border-0 file:bg-ink-100 file:px-2 file:py-1.5 file:text-xs file:font-semibold file:text-ink-700 hover:file:bg-ink-200 focus:border-brand-500"
                                            />
                                        </div>
                                    </div>

                                    <template x-if="item.ppm_attachment_name">
                                        <div class="mt-2 text-xs text-amber-700">
                                            File saat ini:
                                            <a :href="item.ppm_attachment_url" target="_blank" class="font-semibold hover:underline" x-text="item.ppm_attachment_name"></a>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="border-t border-ink-100 px-5 py-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-xs text-ink-500">Berkas dapat berupa PDF atau gambar. Setelah submit, status berubah menjadi Progress PO.</p>
                            <div class="flex justify-end gap-2">
                                <x-button type="button" variant="secondary" x-on:click="closePpmModal()" class="px-4 py-2.5">Batal</x-button>
                                <x-button type="submit" class="px-4 py-2.5">Simpan PPM</x-button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div
            x-show="poTarget"
            x-cloak
            x-transition.opacity.duration.200ms
            x-on:keydown.escape.window="closePoModal()"
            class="fixed inset-0 z-[70] flex items-center justify-center bg-ink-900/50 p-4"
        >
            <div class="flex max-h-[90vh] w-full max-w-4xl flex-col overflow-hidden rounded-3xl bg-white shadow-2xl">
                <div class="flex items-start justify-between gap-4 border-b border-ink-100 px-5 py-4">
                    <div>
                        <h3 class="font-display text-lg font-semibold text-ink-900">Upload Data PO</h3>
                        <p class="mt-1 text-sm text-ink-500" x-text="detailMap[poTarget]?.subject || ''"></p>
                    </div>
                    <button type="button" x-on:click="closePoModal()" class="inline-flex h-8 w-8 items-center justify-center rounded-2xl border border-ink-200 text-ink-600 transition hover:bg-ink-50">
                        <x-heroicon-o-x-mark class="h-4 w-4" />
                    </button>
                </div>

                <form x-ref="poForm" method="POST" enctype="multipart/form-data" x-on:submit.prevent="submitPoForm()" class="flex min-h-0 flex-1 flex-col">
                    @csrf

                    <div class="flex-1 overflow-y-auto px-5 py-4">
                        <p class="mb-4 text-xs text-ink-500">Isi nomor PO per unit barang yang disetujui (bukan takeout). Jika nomor sama, cukup upload file pada salah satu baris dengan nomor yang sama.</p>

                        <div class="space-y-3">
                            <template x-for="(item, index) in getPoItems()" :key="`po-${item.id}-${item.unitIndex}`">
                                <div class="rounded-2xl border border-ink-100 bg-ink-50/50 p-4">
                                    <div class="font-semibold text-ink-900" x-text="item.item_name"></div>
                                    <div class="mt-1 text-xs text-ink-500">
                                        <span x-text="`Unit ${item.unitIndex + 1} dari ${item.totalQty}`"></span>
                                        <span class="mx-1">•</span>
                                        <span x-text="item.brand_type || item.item_category || '-'"></span>
                                    </div>

                                    <input type="hidden" :name="`items[${index}][item_id]`" :value="item.id" />
                                    <input type="hidden" :name="`items[${index}][unit_index]`" :value="item.unitIndex" />

                                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                                        <div>
                                            <label class="block text-xs font-medium text-ink-600">No. PO</label>
                                            <input
                                                type="text"
                                                :name="`items[${index}][po_number]`"
                                                placeholder="Contoh: 5001131743"
                                                class="mt-1 w-full rounded-xl border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-900 outline-none transition focus:border-brand-500"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-ink-600">Upload Berkas</label>
                                            <input
                                                type="file"
                                                :name="`items[${index}][po_attachment]`"
                                                accept=".pdf,.jpg,.jpeg,.png,.webp"
                                                class="mt-1 w-full rounded-xl border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-900 outline-none transition file:mr-2 file:rounded-lg file:border-0 file:bg-ink-100 file:px-2 file:py-1.5 file:text-xs file:font-semibold file:text-ink-700 hover:file:bg-ink-200 focus:border-brand-500"
                                            />
                                        </div>
                                    </div>

                                    <template x-if="item.po_attachment_name">
                                        <div class="mt-2 text-xs text-amber-700">
                                            File saat ini:
                                            <a :href="item.po_attachment_url" target="_blank" class="font-semibold hover:underline" x-text="item.po_attachment_name"></a>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="border-t border-ink-100 px-5 py-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-xs text-ink-500">Berkas dapat berupa PDF atau gambar. Setelah submit, status berubah menjadi Progress Menunggu Barang Diterima.</p>
                            <div class="flex justify-end gap-2">
                                <x-button type="button" variant="secondary" x-on:click="closePoModal()" class="px-4 py-2.5">Batal</x-button>
                                <x-button type="submit" class="px-4 py-2.5">Simpan PO</x-button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div
            x-show="auditTarget"
            x-cloak
            x-transition.opacity.duration.200ms
            x-on:keydown.escape.window="closeAuditModal()"
            class="fixed inset-0 z-[70] flex items-center justify-center bg-ink-900/50 p-4"
        >
            <div class="flex max-h-[90vh] w-full max-w-5xl flex-col overflow-hidden rounded-3xl bg-white shadow-2xl">
                <div class="flex items-start justify-between gap-4 border-b border-ink-100 px-5 py-4">
                    <div>
                        <h3 class="font-display text-lg font-semibold text-ink-900">Verifikasi Audit PPNK</h3>
                        <p class="mt-1 text-sm text-ink-500" x-text="detailMap[auditTarget]?.subject || ''"></p>
                    </div>
                    <button type="button" x-on:click="closeAuditModal()" class="inline-flex h-8 w-8 items-center justify-center rounded-2xl border border-ink-200 text-ink-600 transition hover:bg-ink-50">
                        <x-heroicon-o-x-mark class="h-4 w-4" />
                    </button>
                </div>

                <form x-ref="auditForm" method="POST" :action="detailMap[auditTarget]?.verify_audit_url" x-on:submit.prevent="submitAuditForm()" class="flex min-h-0 flex-1 flex-col">
                    @csrf

                    <div class="flex-1 overflow-y-auto px-5 py-4">
                        <p class="mb-4 text-xs text-ink-500">Verifikasi setiap barang: pilih Disetujui atau Takeout. Barang yang di-takeout akan dikeluarkan dari proses.</p>

                        <div class="space-y-3">
                            <template x-for="(item, index) in detailMap[auditTarget]?.items ?? []" :key="`audit-${item.id}`">
                                <div class="rounded-2xl border border-ink-100 bg-ink-50/50 p-4">
                                    <div class="grid gap-4 md:grid-cols-3">
                                        <div class="md:col-span-1">
                                            <div class="font-semibold text-ink-900" x-text="item.item_name"></div>
                                            <div class="mt-1 text-xs text-ink-500">
                                                <span x-text="item.item_category || '-'"></span>
                                                <span>•</span>
                                                <span x-text="`${item.quantity} ${item.unit || ''}`"></span>
                                            </div>
                                            <div class="mt-0.5 text-xs text-ink-400" x-text="item.brand_type || '-'"></div>
                                            <!-- Remaining Qty Info -->
                                            <div class="mt-2" x-show="getAuditState(index, 'audit_status') === 'takeout' && parseInt(item.quantity) > 1">
                                                <span class="text-xs font-semibold text-amber-700">Sisa Qty: </span>
                                                <span class="text-xs font-bold text-ink-900" x-text="getRemainingQty(index) + ' ' + (item.unit || '')"></span>
                                            </div>
                                        </div>

                                        <div class="md:col-span-2">
                                            <input type="hidden" :name="`items[${index}][item_id]`" :value="item.id" />
                                            <input type="hidden" :name="`items[${index}][audit_status]`" :value="getAuditState(index, 'audit_status', 'approved')" />

                                            <label class="block text-xs font-medium text-ink-600">Status Audit</label>
                                            <div class="mt-2 flex gap-4">
                                                <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-green-200 bg-green-50 px-4 py-2.5 transition hover:bg-green-100 has-[:checked]:border-green-500 has-[:checked]:bg-green-100 has-[:checked]:ring-2 has-[:checked]:ring-green-500/30">
                                                    <input type="radio" :name="`items[${index}][audit_status_radio]`" value="approved" :checked="getAuditState(index, 'audit_status') === 'approved'" x-on:change="setAuditState(index, 'audit_status', 'approved')" class="h-4 w-4 border-green-600 text-green-600 focus:ring-green-500" />
                                                    <span class="text-sm font-semibold text-green-700">Disetujui</span>
                                                </label>
                                                <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 transition hover:bg-red-100 has-[:checked]:border-red-500 has-[:checked]:bg-red-100 has-[:checked]:ring-2 has-[:checked]:ring-red-500/30">
                                                    <input type="radio" :name="`items[${index}][audit_status_radio]`" value="takeout" :checked="getAuditState(index, 'audit_status') === 'takeout'" x-on:change="setAuditState(index, 'audit_status', 'takeout')" class="h-4 w-4 border-red-600 text-red-600 focus:ring-red-500" />
                                                    <span class="text-sm font-semibold text-red-700">Takeout</span>
                                                </label>
                                            </div>

                                            <!-- Takeout Qty Input (only shows when status is takeout AND qty > 1) -->
                                            <div class="mt-3" x-show="getAuditState(index, 'audit_status') === 'takeout' && parseInt(item.quantity) > 1" x-cloak>
                                                <label class="block text-xs font-medium text-ink-600">Jumlah Takeout</label>
                                                <input
                                                    type="number"
                                                    min="0"
                                                    :max="item.quantity"
                                                    :name="`items[${index}][takeout_qty]`"
                                                    x-model.number="auditStates[index + '_takeout_qty']"
                                                    x-on:input="validateTakeoutQty(index, item.quantity)"
                                                    x-on:blur="validateTakeoutQty(index, item.quantity)"
                                                    :value="getAuditState(index, 'takeout_qty', item.quantity)"
                                                    placeholder="Masukkan jumlah yang di-takeout"
                                                    class="mt-1 w-full rounded-xl border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-900 outline-none transition focus:border-red-500"
                                                />
                                                <p class="mt-1 text-xs text-amber-600">Masukkan jumlah yang akan di-takeout (max: <span x-text="item.quantity"></span>)</p>
                                            </div>

                                            <label class="mt-3 block">
                                                <span class="block text-xs font-medium text-ink-600">Alasan / Keterangan</span>
                                                <textarea
                                                    :name="`items[${index}][audit_reason]`"
                                                    rows="2"
                                                    placeholder="Alasan takeout atau catatan verifikasi"
                                                    class="mt-1 w-full rounded-xl border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-900 outline-none transition focus:border-brand-500"
                                                ></textarea>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="border-t border-ink-100 px-5 py-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-xs text-ink-500">Setelah submit, status berubah menjadi Progress PPM. Barang yang di-takeout tidak diproses.</p>
                            <div class="flex justify-end gap-2">
                                <x-button type="button" variant="secondary" x-on:click="closeAuditModal()" class="px-4 py-2.5">Batal</x-button>
                                <x-button type="submit" class="px-4 py-2.5">
                                    <x-heroicon-o-check-circle class="mr-2 h-4 w-4" />
                                    Submit Verifikasi
                                </x-button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Goods Receipt Modal -->
        <div
            x-show="goodsReceiptTarget"
            x-cloak
            x-transition.opacity.duration.200ms
            x-on:keydown.escape.window="closeGoodsReceiptModal()"
            class="fixed inset-0 z-[80] flex items-start justify-center bg-ink-900/50 p-4 overflow-y-auto"
        >
            <div class="w-full max-w-6xl rounded-3xl bg-white shadow-2xl my-8">
                <div class="flex items-center justify-between border-b border-ink-100 px-6 py-4">
                    <div>
                        <h2 class="font-display text-xl font-semibold text-ink-900">Penerimaan Barang</h2>
                        <p class="mt-1 text-sm text-ink-500" x-text="detailMap[goodsReceiptTarget]?.subject || ''"></p>
                    </div>
                    <button type="button" x-on:click="closeGoodsReceiptModal()" class="inline-flex h-8 w-8 items-center justify-center rounded-2xl border border-ink-200 text-ink-600 transition hover:bg-ink-50">
                        <x-heroicon-o-x-mark class="h-4 w-4" />
                    </button>
                </div>

                <form x-ref="goodsReceiptForm" method="POST" :action="detailMap[goodsReceiptTarget]?.goods_receipt_url ?? ''" enctype="multipart/form-data" x-on:submit.prevent="submitGoodsReceiptForm()" class="flex min-h-0 flex-1 flex-col">
                    @csrf

                    <div class="flex-1 overflow-y-auto px-5 py-4">
                        <p class="mb-4 text-xs text-ink-500">Pilih apakah barang akan dimasukkan ke list asset atau tidak. Untuk asset otomatis akan dibuatkan Berita Acara Serah Terima.</p>

                        <div class="space-y-4">
                            <template x-for="(item, index) in getGoodsReceiptItems()" :key="`goods-receipt-${item.id}-${item.unitIndex}`">
                                <div class="rounded-2xl border-2 border-brand-200 bg-brand-50/50 p-5">
                                    <div class="mb-4 flex items-center gap-3">
                                        <div class="flex-1">
                                            <div class="font-semibold text-ink-900" x-text="item.item_name"></div>
                                            <div class="mt-1 text-xs text-ink-500">
                                                <span x-text="item.item_category || '-'"></span>
                                                <span class="mx-1">•</span>
                                                <span x-text="`Unit ${item.unitIndex + 1} dari ${item.receivedQty}`"></span>
                                                <span class="mx-1">•</span>
                                                <span x-text="item.brand_type || '-'"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <input type="hidden" :name="`items[${index}][item_id]`" :value="item.id" />
                                    <input type="hidden" :name="`items[${index}][unit_index]`" :value="item.unitIndex" />

                                    <!-- Handover Type Selection -->
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-ink-700 mb-2">Jenis Penerimaan</label>
                                        <div class="flex gap-4">
                                            <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-brand-200 bg-white px-4 py-3 transition hover:bg-brand-50 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-100 has-[:checked]:ring-2 has-[:checked]:ring-brand-500/30">
                                                <input type="radio" :name="`items[${index}][handover_type]`" :value="'asset'" :id="`handover_asset_${index}`" :checked="getHandoverType(index) === 'asset'" x-on:change="setHandoverType(index, 'asset')" class="h-4 w-4 border-brand-600 text-brand-600 focus:ring-brand-500" />
                                                <span class="text-sm font-semibold text-brand-700">Masukkan ke Asset</span>
                                            </label>
                                            <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-ink-200 bg-white px-4 py-3 transition hover:bg-ink-50 has-[:checked]:border-ink-500 has-[:checked]:bg-ink-100 has-[:checked]:ring-2 has-[:checked]:ring-ink-500/30">
                                                <input type="radio" :name="`items[${index}][handover_type]`" :value="'non_asset'" :id="`handover_non_asset_${index}`" :checked="getHandoverType(index) === 'non_asset'" x-on:change="setHandoverType(index, 'non_asset')" class="h-4 w-4 border-ink-600 text-ink-600 focus:ring-ink-500" />
                                                <span class="text-sm font-semibold text-ink-700">Tidak Asset</span>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Non-Asset Fields -->
                                    <template x-if="getHandoverType(index) === 'non_asset'">
                                        <div class="space-y-3">
                                            <div>
                                                <label class="block text-xs font-medium text-ink-600">Keterangan</label>
                                                <textarea
                                                    :name="`items[${index}][description]`"
                                                    rows="3"
                                                    placeholder="Keterangan penerimaan barang"
                                                    class="mt-1 w-full rounded-xl border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-900 outline-none transition focus:border-brand-500"
                                                ></textarea>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-ink-600">Upload Surat Jalan (PDF/Gambar)</label>
                                                <input
                                                    type="file"
                                                    :name="`items[${index}][surat_jalan]`"
                                                    accept=".pdf,.jpg,.jpeg,.png,.webp"
                                                    class="mt-1 w-full rounded-xl border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-900 outline-none transition file:mr-2 file:rounded-lg file:border-0 file:bg-ink-100 file:px-2 file:py-1.5 file:text-xs file:font-semibold file:text-ink-700 hover:file:bg-ink-200 focus:border-brand-500"
                                                />
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Asset Fields -->
                                    <template x-if="getHandoverType(index) === 'asset'">
                                        <div class="space-y-4">
                                        <div class="grid gap-3 md:grid-cols-2">
                                            <div>
                                                <label class="block text-xs font-medium text-ink-600">Dept</label>
                                                <input
                                                    type="text"
                                                    :name="`items[${index}][dept]`"
                                                    placeholder="Contoh: IT Department"
                                                    class="mt-1 w-full rounded-xl border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-900 outline-none transition focus:border-brand-500"
                                                />
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-ink-600">Model / Spesifikasi</label>
                                                <input
                                                    type="text"
                                                    :name="`items[${index}][model_specification]`"
                                                    placeholder="Model atau spesifikasi barang"
                                                    class="mt-1 w-full rounded-xl border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-900 outline-none transition focus:border-brand-500"
                                                />
                                            </div>
                                        </div>
                                        <div class="grid gap-3 md:grid-cols-2">
                                            <div>
                                                <label class="block text-xs font-medium text-ink-600">Serial Number</label>
                                                <input
                                                    type="text"
                                                    :name="`items[${index}][serial_number]`"
                                                    placeholder="Nomor seri barang"
                                                    class="mt-1 w-full rounded-xl border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-900 outline-none transition focus:border-brand-500"
                                                />
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-ink-600">Nomor Asset</label>
                                                <input
                                                    type="text"
                                                    :name="`items[${index}][asset_number]`"
                                                    placeholder="Nomor asset (jika ada)"
                                                    class="mt-1 w-full rounded-xl border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-900 outline-none transition focus:border-brand-500"
                                                />
                                            </div>
                                        </div>

                                        <div class="border-t border-ink-200 pt-4">
                                            <h4 class="mb-3 text-sm font-semibold text-ink-900">Informasi Penerima</h4>
                                            <div class="grid gap-3 md:grid-cols-2">
                                                <div>
                                                    <label class="block text-xs font-medium text-ink-600">Nama Penerima</label>
                                                    <input
                                                        type="text"
                                                        :name="`items[${index}][recipient_name]`"
                                                        placeholder="Nama penerima barang"
                                                        class="mt-1 w-full rounded-xl border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-900 outline-none transition focus:border-brand-500"
                                                    />
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-ink-600">Jabatan Penerima</label>
                                                    <input
                                                        type="text"
                                                        :name="`items[${index}][recipient_position]`"
                                                        placeholder="Jabatan penerima"
                                                        class="mt-1 w-full rounded-xl border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-900 outline-none transition focus:border-brand-500"
                                                    />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="border-t border-ink-200 pt-4">
                                            <h4 class="mb-3 text-sm font-semibold text-ink-900">Atasan Penerima</h4>
                                            <div class="grid gap-3 md:grid-cols-2">
                                                <div>
                                                    <label class="block text-xs font-medium text-ink-600">Nama Atasan</label>
                                                    <input
                                                        type="text"
                                                        :name="`items[${index}][supervisor_name]`"
                                                        placeholder="Nama atasan penerima"
                                                        class="mt-1 w-full rounded-xl border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-900 outline-none transition focus:border-brand-500"
                                                    />
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-ink-600">Jabatan Atasan</label>
                                                    <input
                                                        type="text"
                                                        :name="`items[${index}][supervisor_position]`"
                                                        placeholder="Jabatan atasan"
                                                        class="mt-1 w-full rounded-xl border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-900 outline-none transition focus:border-brand-500"
                                                    />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="border-t border-ink-200 pt-4">
                                            <h4 class="mb-3 text-sm font-semibold text-ink-900">Diserahkan Oleh (HRGA)</h4>
                                            <p class="mb-2 text-xs text-amber-600">Otomatis terisi dari data HRGA terakhir pada unit ini (bisa diedit)</p>
                                            <div class="grid gap-3 md:grid-cols-2">
                                                <div>
                                                    <label class="block text-xs font-medium text-ink-600">Nama HRGA</label>
                                                    <input
                                                        type="text"
                                                        :name="`items[${index}][deliverer_name]`"
                                                        :placeholder="detailMap[goodsReceiptTarget]?.previous_deliverer?.name || 'Nama penyerah dari HRGA'"
                                                        :value="detailMap[goodsReceiptTarget]?.previous_deliverer?.name || ''"
                                                        class="mt-1 w-full rounded-xl border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-900 outline-none transition focus:border-brand-500"
                                                    />
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-ink-600">Jabatan HRGA</label>
                                                    <input
                                                        type="text"
                                                        :name="`items[${index}][deliverer_position]`"
                                                        :placeholder="detailMap[goodsReceiptTarget]?.previous_deliverer?.position || 'Jabatan HRGA'"
                                                        :value="detailMap[goodsReceiptTarget]?.previous_deliverer?.position || ''"
                                                        class="mt-1 w-full rounded-xl border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-900 outline-none transition focus:border-brand-500"
                                                    />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="border-t border-ink-200 pt-4">
                                            <h4 class="mb-3 text-sm font-semibold text-ink-900">Diketahui Oleh (Staff ICT)</h4>
                                            <p class="mb-2 text-xs text-amber-600">Otomatis terisi dari Staff ICT pada unit ini (bisa diedit)</p>
                                            <div class="grid gap-3 md:grid-cols-2">
                                                <div>
                                                    <label class="block text-xs font-medium text-ink-600">Nama (ICT Staff)</label>
                                                    <input
                                                        type="text"
                                                        :name="`items[${index}][witness_name]`"
                                                        :placeholder="detailMap[goodsReceiptTarget]?.staff_ict?.name || 'Nama staff ICT'"
                                                        :value="detailMap[goodsReceiptTarget]?.staff_ict?.name || ''"
                                                        class="mt-1 w-full rounded-xl border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-900 outline-none transition focus:border-brand-500"
                                                    />
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-ink-600">Jabatan</label>
                                                    <input
                                                        type="text"
                                                        :name="`items[${index}][witness_position]`"
                                                        :placeholder="detailMap[goodsReceiptTarget]?.staff_ict?.position || 'Jabatan staff ICT'"
                                                        :value="detailMap[goodsReceiptTarget]?.staff_ict?.position || ''"
                                                        class="mt-1 w-full rounded-xl border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-900 outline-none transition focus:border-brand-500"
                                                    />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="grid gap-3 md:grid-cols-2">
                                            <div>
                                                <label class="block text-xs font-medium text-ink-600">Upload Surat Jalan</label>
                                                <input
                                                    type="file"
                                                    :name="`items[${index}][surat_jalan]`"
                                                    accept=".pdf,.jpg,.jpeg,.png,.webp"
                                                    class="mt-1 w-full rounded-xl border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-900 outline-none transition file:mr-2 file:rounded-lg file:border-0 file:bg-ink-100 file:px-2 file:py-1.5 file:text-xs file:font-semibold file:text-ink-700 hover:file:bg-ink-200 focus:border-brand-500"
                                                />
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-ink-600">Upload Foto Barang</label>
                                                <p class="text-xs text-amber-600 mb-1">Foto ini akan dilampirkan di berkas Berita Acara Serah Terima</p>
                                                <input
                                                    type="file"
                                                    :name="`items[${index}][serah_terima]`"
                                                    accept=".pdf,.jpg,.jpeg,.png,.webp"
                                                    class="mt-1 w-full rounded-xl border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-900 outline-none transition file:mr-2 file:rounded-lg file:border-0 file:bg-ink-100 file:px-2 file:py-1.5 file:text-xs file:font-semibold file:text-ink-700 hover:file:bg-ink-200 focus:border-brand-500"
                                                />
                                            </div>
                                        </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="border-t border-ink-100 px-5 py-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-xs text-ink-500">Setelah submit, status berubah menjadi Barang Sudah Diterima. Asset akan otomatis dibuatkan Berita Acara Serah Terima.</p>
                            <div class="flex justify-end gap-2">
                                <x-button type="button" variant="secondary" x-on:click="closeGoodsReceiptModal()" class="px-4 py-2.5">Batal</x-button>
                                <x-button type="submit" class="px-4 py-2.5">
                                    <x-heroicon-o-check-circle class="mr-2 h-4 w-4" />
                                    Simpan Penerimaan Barang
                                </x-button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
