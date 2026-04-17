<?php

namespace App\Http\Controllers\Form;

use App\Enums\UserRole;
use App\Exports\IctRequestExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIctRequestRequest;
use App\Models\IctRequest;
use App\Models\IctRequestItem;
use App\Models\IctRequestPoDocument;
use App\Models\IctRequestPpmDocument;
use App\Models\IctRequestPpnkDocument;
use App\Models\IctRequestQuotation;
use App\Models\Asset;
use App\Models\AssetHandover;
use App\Support\PublicFileUpload;
use App\Support\UnitScope;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class IctRequestController extends Controller
{
    public function index(Request $request): View
    {
        $sort = $this->resolveSort($request);
        $direction = $this->resolveDirection($request);
        $dateFilters = $this->resolveDateFilters($request);

        $requests = $this->buildIndexQuery($request)
            ->orderBy($sort, $direction)
            ->get();

        $requests->load([
            'requester:id,name',
            'unit:id,name',
            'items.quotations',
            'items.ppnkDocument',
            'items.ppmDocument',
            'items.poDocument',
            'items.assetHandover',
            'quotations',
        ]);

        return view('forms.ict-requests.index', [
            'requests' => $requests,
            'sort' => $sort,
            'direction' => $direction,
            'filters' => [
                'from' => $dateFilters['from']->toDateString(),
                'until' => $dateFilters['until']->toDateString(),
            ],
            'activeFilterRangeLabel' => $dateFilters['label'],
        ]);
    }

    public function create(): View
    {
        /** @var \App\Models\User $user */
        $user = request()->user();
        abort_unless($user->canCreateIctRequest(), 403);
        return view('forms.ict-requests.create', $this->buildFormViewData());
    }

    public function edit(Request $request, IctRequest $ictRequest): View
    {
        abort_unless($request->user()->canCreateIctRequest(), 403);
        abort_unless($this->canAccessRequest($request, $ictRequest), 403);
        abort_unless($this->canModifyRequest($ictRequest), 403);

        $ictRequest->load(['items.quotations', 'quotations']);

        return view('forms.ict-requests.create', $this->buildFormViewData($ictRequest));
    }

    public function store(StoreIctRequestRequest $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->canCreateIctRequest(), 403);

        $items = $request->validated('items');
        $quotationMode = (string) $request->input('quotation_mode', 'global');
        $unitCode = $user->unit?->code ?? 'UNIT';
        $neededAt = Carbon::parse((string) $request->input('needed_at', now()->toDateString()));
        $yearSuffix = $neededAt->format('y');
        $formIdentifier = $this->buildFormIdentifier((string) $unitCode, $this->resolveNextFormSequence((int) $user->unit_id, $neededAt), $yearSuffix);

        $ictRequest = IctRequest::create([
            'unit_id' => $user->unit_id,
            'requester_id' => $user->id,
            'requester_name' => $request->input('requester_name'),
            'department_name' => $request->input('department_name'),
            'form_number' => $formIdentifier,
            'subject' => trim((string) $request->input('subject')) !== '' ? trim((string) $request->input('subject')) : $formIdentifier,
            'request_category' => (string) $request->input('request_category'),
            'priority' => (string) $request->input('priority'),
            'status' => 'drafted',
            'needed_at' => $request->input('needed_at'),
            'quotation_mode' => $quotationMode,
            'is_pta_request' => $request->boolean('is_pta_request'),
            'justification' => (string) $request->input('justification'),
            'additional_budget_reason' => $request->input('additional_budget_reason'),
            'pta_budget_not_listed_reason' => $request->input('pta_budget_not_listed_reason'),
            'pta_additional_budget_reason' => $request->input('pta_additional_budget_reason'),
            'drafted_by_name' => $request->input('drafted_by_name'),
            'drafted_by_title' => $request->input('drafted_by_title'),
            'acknowledged_by_name' => $request->input('acknowledged_by_name'),
            'acknowledged_by_title' => $request->input('acknowledged_by_title'),
            'approved_1_name' => $request->input('approved_1_name'),
            'approved_1_title' => $request->input('approved_1_title'),
            'approved_2_name' => $request->input('approved_2_name'),
            'approved_2_title' => $request->input('approved_2_title'),
            'approved_3_name' => $request->input('approved_3_name'),
            'approved_3_title' => $request->input('approved_3_title'),
            'approved_4_name' => $request->input('approved_4_name'),
            'approved_4_title' => $request->input('approved_4_title'),
        ]);

        $this->syncRequestDetails($ictRequest, $items, $request->validated('global_quotations') ?? [], $quotationMode);

        return redirect()->route('forms.ict-requests.index')->with('status', 'Permintaan fasilitas ICT berhasil disimpan.');
    }

    public function update(StoreIctRequestRequest $request, IctRequest $ictRequest): RedirectResponse
    {
        abort_unless($request->user()->canCreateIctRequest(), 403);
        abort_unless($this->canAccessRequest($request, $ictRequest), 403);
        abort_unless($this->canModifyRequest($ictRequest), 403);

        $items = $request->validated('items');
        $quotationMode = (string) $request->input('quotation_mode', 'global');
        $shouldResetRevisionFeedback = $ictRequest->status === 'needs_revision';

        if ($shouldResetRevisionFeedback && $ictRequest->revision_attachment_path) {
            Storage::disk('public')->delete($ictRequest->revision_attachment_path);
        }

        $subjectInput = trim((string) $request->input('subject'));
        $neededAt = Carbon::parse((string) ($request->input('needed_at') ?: optional($ictRequest->needed_at)->toDateString() ?: now()->toDateString()));
        $yearSuffix = $neededAt->format('y');
        $fallbackIdentifier = $ictRequest->form_number ?: $this->buildFormIdentifier(
            (string) ($ictRequest->unit?->code ?? 'UNIT'),
            $this->resolveNextFormSequence((int) $ictRequest->unit_id, $neededAt),
            $yearSuffix
        );

        $ictRequest->update([
            'requester_name' => $request->input('requester_name'),
            'department_name' => $request->input('department_name'),
            'subject' => $subjectInput !== '' ? $subjectInput : ($ictRequest->subject ?: $fallbackIdentifier),
            'form_number' => $ictRequest->form_number ?: $fallbackIdentifier,
            'request_category' => (string) $request->input('request_category'),
            'priority' => (string) $request->input('priority'),
            'needed_at' => $request->input('needed_at'),
            'quotation_mode' => $quotationMode,
            'is_pta_request' => $request->boolean('is_pta_request'),
            'justification' => (string) $request->input('justification'),
            'additional_budget_reason' => $request->input('additional_budget_reason'),
            'pta_budget_not_listed_reason' => $request->input('pta_budget_not_listed_reason'),
            'pta_additional_budget_reason' => $request->input('pta_additional_budget_reason'),
            'drafted_by_name' => $request->input('drafted_by_name'),
            'drafted_by_title' => $request->input('drafted_by_title'),
            'acknowledged_by_name' => $request->input('acknowledged_by_name'),
            'acknowledged_by_title' => $request->input('acknowledged_by_title'),
            'approved_1_name' => $request->input('approved_1_name'),
            'approved_1_title' => $request->input('approved_1_title'),
            'approved_2_name' => $request->input('approved_2_name'),
            'approved_2_title' => $request->input('approved_2_title'),
            'approved_3_name' => $request->input('approved_3_name'),
            'approved_3_title' => $request->input('approved_3_title'),
            'approved_4_name' => $request->input('approved_4_name'),
            'approved_4_title' => $request->input('approved_4_title'),
            'revision_number' => ((int) $ictRequest->revision_number) + 1,
            'status' => $shouldResetRevisionFeedback ? 'drafted' : $ictRequest->status,
            'revision_note' => $shouldResetRevisionFeedback ? null : $ictRequest->revision_note,
            'revision_attachment_name' => $shouldResetRevisionFeedback ? null : $ictRequest->revision_attachment_name,
            'revision_attachment_path' => $shouldResetRevisionFeedback ? null : $ictRequest->revision_attachment_path,
            'revision_attachment_size' => $shouldResetRevisionFeedback ? null : $ictRequest->revision_attachment_size,
            'revision_attachment_mime' => $shouldResetRevisionFeedback ? null : $ictRequest->revision_attachment_mime,
            'revision_requested_by' => $shouldResetRevisionFeedback ? null : $ictRequest->revision_requested_by,
            'revision_requested_at' => $shouldResetRevisionFeedback ? null : $ictRequest->revision_requested_at,
        ]);

        $this->syncRequestDetails($ictRequest, $items, $request->validated('global_quotations') ?? [], $quotationMode);

        return redirect()->route('forms.ict-requests.index')->with('status', 'Permintaan fasilitas ICT berhasil diperbarui.');
    }

    public function export(Request $request): BinaryFileResponse
    {
        $rows = [];
        $hyperlinks = [];
        $lineNumber = 1;

        $requests = $this->buildIndexQuery($request)
            ->with(['unit:id,name', 'items.quotations', 'quotations'])
            ->orderBy($this->resolveSort($request), $this->resolveDirection($request))
            ->get();

        foreach ($requests as $requestRow) {
            $globalQuotations = $requestRow->quotations
                ->whereNull('ict_request_item_id')
                ->sortBy('line_number')
                ->values();

            foreach ($requestRow->items->sortBy('line_number')->values() as $item) {
                $itemQuotations = $requestRow->quotation_mode === 'per_item'
                    ? $item->quotations->sortBy('line_number')->values()
                    : $globalQuotations;

                $total = ((float) ($item->estimated_price ?? 0)) * ((int) ($item->quantity ?? 0));
                $excelRowNumber = count($rows) + 2;
                $photoText = $item->photo_name ?: '-';
                $photoUrl = $item->photo_path ? Storage::url($item->photo_path) : '';

                $vendorTexts = [];
                $vendorUrls = [];

                foreach (range(0, 2) as $vendorIndex) {
                    $quotation = $itemQuotations->get($vendorIndex);
                    $vendorTexts[] = $quotation?->vendor_name ?: '-';
                    $vendorUrls[] = $quotation?->attachment_path ? Storage::url($quotation->attachment_path) : '';
                }

                $rows[] = [
                    $lineNumber,
                    $item->item_category,
                    $item->item_name,
                    $item->brand_type,
                    trim(((string) $item->quantity).' '.((string) $item->unit)),
                    $item->estimated_price ? 'Rp '.number_format((float) $item->estimated_price, 0, ',', '.') : '-',
                    $total > 0 ? 'Rp '.number_format($total, 0, ',', '.') : '-',
                    $item->notes,
                    $requestRow->unit?->name,
                    $photoText,
                    $vendorTexts[0],
                    $vendorTexts[1],
                    $vendorTexts[2],
                ];

                $hyperlinks[$excelRowNumber] = [
                    'J' => $photoUrl,
                    'K' => $vendorUrls[0],
                    'L' => $vendorUrls[1],
                    'M' => $vendorUrls[2],
                ];

                $lineNumber += 1;
            }
        }

        return Excel::download(
            new IctRequestExport($rows, $hyperlinks),
            'ict-requests.xlsx'
        );
    }

    public function pdf(Request $request, IctRequest $ictRequest): Response
    {
        $ictRequest->load([
            'unit:id,code,name',
            'requester:id,name',
            'items.quotations',
        ]);

        abort_unless($this->canAccessRequest($request, $ictRequest), 403);

        $isCopy = $request->boolean('copy');

        $items = $ictRequest->items
            ->sortBy('line_number')
            ->values()
            ->map(function (IctRequestItem $item, int $index) use ($isCopy) {
                return [
                    'number' => $index + 1,
                    'item_name' => (string) $item->item_name,
                    'brand_type' => (string) ($item->brand_type ?? '-'),
                    'quantity_label' => trim(((string) $item->quantity).' '.((string) ($item->unit ?? ''))),
                    'estimated_price' => (float) ($item->estimated_price ?? 0),
                    'total_price' => ((float) ($item->estimated_price ?? 0)) * ((int) ($item->quantity ?? 0)),
                    'notes' => (string) ($item->notes ?? ''),
                    'photo_title' => (string) ($item->photo_name ?: $item->item_name),
                    'photo_data_uri' => $this->resolveItemImageDataUri($item, $isCopy),
                ];
            });

        $pdf = Pdf::loadView('forms.ict-requests.pdf', [
            'ictRequest' => $ictRequest,
            'items' => $items,
            'totalEstimatedPrice' => $items->sum('total_price'),
            'logoDataUri' => $this->fileToDataUri(public_path('images/eas-new.png'), $isCopy),
            'requestDate' => optional($ictRequest->needed_at)->format('d F Y'),
            'revisionLabel' => 'REV-'.(int) $ictRequest->revision_number,
            'isCopy' => $isCopy,
            'companyLabel' => str((string) ($ictRequest->unit?->code ?? 'EAS'))->before('-')->upper()->toString(),
            'departmentLabel' => $ictRequest->departmentDisplayName(),
            'requesterLabel' => $ictRequest->requesterDisplayName(),
            'signatureBlocks' => [
                ['label' => 'Dibuat oleh', 'name' => $ictRequest->drafted_by_name, 'title' => $ictRequest->drafted_by_title],
                ['label' => 'Diketahui oleh', 'name' => $ictRequest->acknowledged_by_name, 'title' => $ictRequest->acknowledged_by_title],
                ['label' => 'Disetujui oleh', 'name' => $ictRequest->approved_1_name, 'title' => $ictRequest->approved_1_title],
                ['label' => 'Disetujui oleh', 'name' => $ictRequest->approved_2_name, 'title' => $ictRequest->approved_2_title],
            ],
            'ptaApprovals' => [
                ['label' => 'Disetujui oleh', 'name' => $ictRequest->approved_3_name, 'title' => $ictRequest->approved_3_title],
                ['label' => 'Disetujui oleh', 'name' => $ictRequest->approved_4_name, 'title' => $ictRequest->approved_4_title],
            ],
        ]);

        return $pdf->stream('form-ict-'.$ictRequest->id.'.pdf');
    }

    public function print(Request $request, IctRequest $ictRequest): Response
    {
        abort_unless($this->canAccessRequest($request, $ictRequest), 403);
        abort_unless($this->canPrintRequest($request->user(), $ictRequest), 403);

        $isCopy = ((int) $ictRequest->print_count) > 0;

        $ictRequest->increment('print_count');
        $ictRequest->forceFill(['last_printed_at' => now()])->save();

        $request->query->set('copy', $isCopy ? '1' : '0');

        return $this->pdf($request, $ictRequest->fresh());
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()->canCreateIctRequest(), 403);

        $validated = $request->validate([
            'selected_ids' => ['nullable', 'array'],
            'selected_ids.*' => ['integer'],
            'select_all_matching' => ['nullable', 'boolean'],
            'search' => ['nullable', 'string'],
        ]);

        $query = $this->buildIndexQuery($request);
        $query->whereNotIn('status', ['checked_by_asmen', 'progress_ppnk', 'progress_goods_arrived', 'completed']);

        if (($validated['select_all_matching'] ?? false) === true) {
            $deleted = (clone $query)->delete();
        } else {
            $selectedIds = collect($validated['selected_ids'] ?? [])
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->values();

            if ($selectedIds->isEmpty()) {
                return back()->with('status', 'Tidak ada data yang dipilih untuk dihapus.');
            }

            $deleted = (clone $query)->whereIn('id', $selectedIds)->delete();
        }

        return back()->with('status', "{$deleted} permintaan ICT berhasil dihapus.");
    }

    public function permanentDestroy(Request $request, IctRequest $ictRequest): RedirectResponse
    {
        abort_unless($request->user()->canPermanentDeleteIctRequest(), 403);

        $this->deleteIctRequestFiles($ictRequest);

        $ictRequest->delete();

        return back()->with('status', 'Permintaan ICT berhasil dihapus secara permanen.');
    }

    public function storePpnk(Request $request, IctRequest $ictRequest): RedirectResponse
    {
        abort_unless($this->canAccessRequest($request, $ictRequest), 403);
        abort_unless($request->user()->isIctAdmin(), 403);
        abort_unless($ictRequest->status === 'progress_ppnk', 403);

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'ppnk_date' => ['required', 'date'],
            'items.*.item_id' => ['required', 'integer'],
            'items.*.ppnk_number' => ['required', 'string', 'max:255'],
            'items.*.ppnk_attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ]);
        $ppnkDate = Carbon::parse($validated['ppnk_date'])->startOfDay();

        $ictRequest->loadMissing(['items', 'ppnkDocuments']);

        $rows = collect($validated['items'])
            ->map(fn ($item) => [
                'item_id' => (int) $item['item_id'],
                'ppnk_number' => trim((string) $item['ppnk_number']),
                'ppnk_attachment' => $item['ppnk_attachment'] ?? null,
            ]);

        $itemsById = $ictRequest->items->keyBy('id');
        $documentsByNumber = $ictRequest->ppnkDocuments->keyBy('ppnk_number');
        $processedDocuments = [];

        foreach ($rows as $row) {
            abort_unless($itemsById->has($row['item_id']), 422);
        }

        foreach ($rows->groupBy('ppnk_number') as $ppnkNumber => $numberRows) {
            $uploadedFile = collect($numberRows)->pluck('ppnk_attachment')->first(fn ($file) => $file instanceof UploadedFile);
            $document = $documentsByNumber->get($ppnkNumber);

            if (! $document && ! ($uploadedFile instanceof UploadedFile)) {
                return back()
                    ->withErrors(['items' => "File PPNK/PPK untuk nomor {$ppnkNumber} wajib diupload minimal sekali."])
                    ->withInput();
            }

            if ($uploadedFile instanceof UploadedFile) {
                if ($document && $document->attachment_path) {
                    Storage::disk('public')->delete($document->attachment_path);
                }

                $stored = $this->storePpnkAttachment($uploadedFile);

                $document = IctRequestPpnkDocument::updateOrCreate(
                    [
                        'ict_request_id' => $ictRequest->id,
                        'ppnk_number' => $ppnkNumber,
                    ],
                    [
                        'attachment_name' => $stored['name'],
                        'attachment_path' => $stored['path'],
                        'attachment_size' => $stored['size'],
                        'attachment_mime' => $stored['mime'],
                        'uploaded_by' => $request->user()->id,
                        'uploaded_at' => $ppnkDate,
                    ]
                );
            }

            $processedDocuments[$ppnkNumber] = $document;
        }

        foreach ($rows as $row) {
            $document = $processedDocuments[$row['ppnk_number']] ?? $documentsByNumber->get($row['ppnk_number']);
            $itemsById[$row['item_id']]->update([
                'ppnk_document_id' => $document?->id,
                'ppnk_uploaded_at' => $ppnkDate,
            ]);
        }

        // Update status ke progress_verifikasi_audit setelah PPNK disimpan
        $ictRequest->status = 'progress_verifikasi_audit';
        $ictRequest->save();

        return back()->with('status', 'Data PPNK/PPK berhasil disimpan. Status: Progress Verifikasi Audit.');
    }

    public function verifyAuditPpnk(Request $request, IctRequest $ictRequest): RedirectResponse
    {
        abort_unless($request->user()->isIctAdmin(), 403);
        abort_unless($ictRequest->status === 'progress_verifikasi_audit', 403);

        $validated = $request->validate([
            'audit_date' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'integer'],
            'items.*.audit_status' => ['required', 'in:takeout,approved'],
            'items.*.audit_reason' => ['nullable', 'string', 'max:1000'],
            'items.*.takeout_qty' => ['nullable', 'integer', 'min:0'],
        ]);
        $auditDate = Carbon::parse($validated['audit_date'])->startOfDay();

        $ictRequest->loadMissing(['items']);

        $itemsById = $ictRequest->items->keyBy('id');
        $takeoutCount = 0;
        $approvedCount = 0;
        $totalTakeoutQty = 0;

        foreach ($validated['items'] as $itemData) {
            $item = $itemsById->get($itemData['item_id']);

            if (! $item) {
                continue;
            }

            $updateData = [
                'audit_status' => $itemData['audit_status'],
                'audit_reason' => trim($itemData['audit_reason'] ?? ''),
            ];

            // Handle partial takeout logic
            if ($itemData['audit_status'] === 'takeout') {
                $takeoutQty = (int) ($itemData['takeout_qty'] ?? $item->quantity);
                
                // Validate takeout_qty doesn't exceed item quantity
                if ($takeoutQty > $item->quantity) {
                    return back()
                        ->withErrors(['items' => "Jumlah takeout untuk {$item->item_name} tidak boleh lebih dari jumlah barang ({$item->quantity})"])
                        ->withInput();
                }
                
                // If takeout_qty is provided and less than full quantity
                if ($takeoutQty > 0 && $takeoutQty < $item->quantity) {
                    // Partial takeout: reduce quantity, keep item active
                    $newQty = $item->quantity - $takeoutQty;
                    $updateData['quantity'] = $newQty;
                    $updateData['takeout_qty'] = $takeoutQty;
                    $totalTakeoutQty += $takeoutQty;
                    // Item remains approved since there's still quantity left
                    $updateData['audit_status'] = 'approved';
                    $approvedCount++;
                } else {
                    // Full takeout: remove all quantity
                    $updateData['takeout_qty'] = $takeoutQty ?: $item->quantity;
                    $totalTakeoutQty += $updateData['takeout_qty'];
                    $takeoutCount++;
                }
            } else {
                $approvedCount++;
            }

            $item->update($updateData);
        }

        // Update status ke progress_ppm setelah semua item diverifikasi
        $ictRequest->update([
            'status' => 'progress_ppm',
            'audit_verified_at' => $auditDate,
        ]);

        $message = "Verifikasi audit selesai. {$approvedCount} barang disetujui, {$takeoutCount} barang di-takeout.";
        if ($totalTakeoutQty > 0) {
            $message .= " Total qty di-takeout: {$totalTakeoutQty} unit.";
        }

        return back()->with('status', $message);
    }

    public function storePpm(Request $request, IctRequest $ictRequest): RedirectResponse
    {
        abort_unless($this->canAccessRequest($request, $ictRequest), 403);
        abort_unless($request->user()->isIctAdmin(), 403);
        abort_unless($ictRequest->status === 'progress_ppm', 403);

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'ppm_date' => ['required', 'date'],
            'items.*.item_id' => ['required', 'integer'],
            'items.*.line_number' => ['nullable', 'integer'],
            'items.*.ppm_number' => ['required', 'string', 'max:255'],
            'items.*.pr_number' => ['required', 'string', 'max:255'],
            'items.*.ppm_attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ]);
        $ppmDate = Carbon::parse($validated['ppm_date'])->startOfDay();

        $ictRequest->loadMissing(['items', 'ppmDocuments']);

        $rows = collect($validated['items'])
            ->map(fn ($item) => [
                'item_id' => (int) $item['item_id'],
                'line_number' => (int) ($item['line_number'] ?? 0),
                'ppm_number' => trim((string) $item['ppm_number']),
                'pr_number' => trim((string) $item['pr_number']),
                'ppm_attachment' => $item['ppm_attachment'] ?? null,
            ]);

        $itemsById = $ictRequest->items->keyBy('id');
        $documentsByNumber = $ictRequest->ppmDocuments->keyBy('ppm_number');
        $processedDocuments = [];

        foreach ($rows as $row) {
            abort_unless($itemsById->has($row['item_id']), 422);
        }

        foreach ($rows->groupBy('ppm_number') as $ppmNumber => $numberRows) {
            $uploadedFile = collect($numberRows)->pluck('ppm_attachment')->first(fn ($file) => $file instanceof UploadedFile);
            $document = $documentsByNumber->get($ppmNumber);

            if (! $document && ! ($uploadedFile instanceof UploadedFile)) {
                return back()
                    ->withErrors(['items' => "File PPM untuk nomor {$ppmNumber} wajib diupload minimal sekali."])
                    ->withInput();
            }

            if ($uploadedFile instanceof UploadedFile) {
                if ($document && $document->attachment_path) {
                    Storage::disk('public')->delete($document->attachment_path);
                }

                $stored = $this->storePpmAttachment($uploadedFile);

                $document = IctRequestPpmDocument::updateOrCreate(
                    [
                        'ict_request_id' => $ictRequest->id,
                        'ppm_number' => $ppmNumber,
                    ],
                    [
                        'attachment_name' => $stored['name'],
                        'attachment_path' => $stored['path'],
                        'attachment_size' => $stored['size'],
                        'attachment_mime' => $stored['mime'],
                        'uploaded_by' => $request->user()->id,
                        'uploaded_at' => $ppmDate,
                    ]
                );
            }

            $processedDocuments[$ppmNumber] = $document;
        }

        foreach ($rows as $row) {
            $document = $processedDocuments[$row['ppm_number']] ?? $documentsByNumber->get($row['ppm_number']);
            $itemsById[$row['item_id']]->update([
                'ppm_document_id' => $document?->id,
                'ppm_uploaded_at' => $ppmDate,
                'pr_number' => $row['pr_number'],
                'line_number' => $row['line_number'],
            ]);
        }

        // Update status ke progress_po setelah PPM disimpan
        $ictRequest->status = 'progress_po';
        $ictRequest->save();

        return back()->with('status', 'Data PPM berhasil disimpan. Status: Progress PO.');
    }

    public function storePo(Request $request, IctRequest $ictRequest): RedirectResponse
    {
        abort_unless($this->canAccessRequest($request, $ictRequest), 403);
        abort_unless($request->user()->isIctAdmin(), 403);
        abort_unless($ictRequest->status === 'progress_po', 403);

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'po_date' => ['required', 'date'],
            'items.*.item_id' => ['required', 'integer'],
            'items.*.po_number' => ['required', 'string', 'max:255'],
            'items.*.po_attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ]);
        $poDate = Carbon::parse($validated['po_date'])->startOfDay();

        $ictRequest->loadMissing(['items', 'poDocuments']);

        $rows = collect($validated['items'])
            ->map(fn ($item) => [
                'item_id' => (int) $item['item_id'],
                'po_number' => trim((string) $item['po_number']),
                'po_attachment' => $item['po_attachment'] ?? null,
            ]);

        $itemsById = $ictRequest->items->keyBy('id');
        $documentsByNumber = $ictRequest->poDocuments->keyBy('po_number');
        $processedDocuments = [];

        foreach ($rows as $row) {
            abort_unless($itemsById->has($row['item_id']), 422);
        }

        foreach ($rows->groupBy('po_number') as $poNumber => $numberRows) {
            $uploadedFile = collect($numberRows)->pluck('po_attachment')->first(fn ($file) => $file instanceof UploadedFile);
            $document = $documentsByNumber->get($poNumber);

            if (! $document && ! ($uploadedFile instanceof UploadedFile)) {
                return back()
                    ->withErrors(['items' => "File PO untuk nomor {$poNumber} wajib diupload minimal sekali."])
                    ->withInput();
            }

            if ($uploadedFile instanceof UploadedFile) {
                if ($document && $document->attachment_path) {
                    Storage::disk('public')->delete($document->attachment_path);
                }

                $stored = $this->storePoAttachment($uploadedFile);

                $document = IctRequestPoDocument::updateOrCreate(
                    [
                        'ict_request_id' => $ictRequest->id,
                        'po_number' => $poNumber,
                    ],
                    [
                        'attachment_name' => $stored['name'],
                        'attachment_path' => $stored['path'],
                        'attachment_size' => $stored['size'],
                        'attachment_mime' => $stored['mime'],
                        'uploaded_by' => $request->user()->id,
                        'uploaded_at' => $poDate,
                    ]
                );
            }

            $processedDocuments[$poNumber] = $document;
        }

        foreach ($rows as $row) {
            $document = $processedDocuments[$row['po_number']] ?? $documentsByNumber->get($row['po_number']);
            $itemsById[$row['item_id']]->update([
                'po_document_id' => $document?->id,
                'po_uploaded_at' => $poDate,
            ]);
        }

        // Update status ke progress_waiting_goods setelah PO disimpan
        $ictRequest->status = 'progress_waiting_goods';
        $ictRequest->save();

        return back()->with('status', 'Data PO berhasil disimpan. Status: Progress Menunggu Barang Diterima.');
    }

    protected function buildFormIdentifier(string $unitCode, int $sequence, string $yearSuffix): string
    {
        $normalizedUnitCode = Str::upper(trim($unitCode)) !== '' ? Str::upper(trim($unitCode)) : 'UNIT';

        return sprintf('%s-FORM ICT-%03d-%s', $normalizedUnitCode, $sequence, $yearSuffix);
    }

    protected function resolveNextFormSequence(int $unitId, Carbon $neededAt): int
    {
        $year = (int) $neededAt->format('Y');

        $rows = IctRequest::query()
            ->where('unit_id', $unitId)
            ->where(function ($query) use ($year) {
                $query
                    ->where(function ($q) use ($year) {
                        $q->whereNotNull('needed_at')->whereYear('needed_at', $year);
                    })
                    ->orWhere(function ($q) use ($year) {
                        $q->whereNull('needed_at')->whereYear('created_at', $year);
                    });
            })
            ->get(['form_number', 'subject']);

        $max = 0;

        foreach ($rows as $row) {
            $candidates = [
                (string) ($row->form_number ?? ''),
                (string) ($row->subject ?? ''),
            ];

            foreach ($candidates as $value) {
                $sequence = $this->extractFormSequence($value);
                if ($sequence !== null) {
                    $max = max($max, $sequence);
                }
            }
        }

        return $max + 1;
    }

    protected function extractFormSequence(string $value): ?int
    {
        if (! preg_match('/FORM ICT-(\d{1,3})/i', (string) $value, $matches)) {
            return null;
        }

        return (int) $matches[1];
    }

    public function nextIdentifier(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user && $user->canCreateIctRequest(), 403);

        $validated = $request->validate([
            'needed_at' => ['required', 'date'],
        ]);

        $neededAt = Carbon::parse($validated['needed_at']);
        $yearSuffix = $neededAt->format('y');
        $unitCode = $user->unit?->code ?? 'UNIT';
        $sequence = $this->resolveNextFormSequence((int) $user->unit_id, $neededAt);
        $identifier = $this->buildFormIdentifier((string) $unitCode, $sequence, $yearSuffix);

        return response()->json([
            'year' => $yearSuffix,
            'sequence' => $sequence,
            'identifier' => $identifier,
        ]);
    }

    protected function buildIndexQuery(Request $request): Builder
    {
        $search = $request->string('search')->toString();
        $dateFilters = $this->resolveDateFilters($request);

        return UnitScope::apply(
            IctRequest::query()
                ->select([
                    'id',
                    'unit_id',
                    'requester_id',
                    'form_number',
                    'subject',
                    'priority',
                    'status',
                    'justification',
                    'created_at',
                    'quotation_mode',
                    'revision_number',
                    'print_count',
                    'last_printed_at',
                    'final_signed_pdf_name',
                    'final_signed_pdf_path',
                    'rejected_reason',
                    'revision_note',
                    'revision_attachment_name',
                    'revision_attachment_path',
                ])
                ->with([
                    'requester:id,name',
                    'unit:id,name',
                    'items.ppnkDocument:id,ict_request_id,ppnk_number,attachment_name,attachment_path,attachment_mime',
                ])
                ->whereBetween('created_at', [
                    $dateFilters['from']->copy()->startOfDay(),
                    $dateFilters['until']->copy()->endOfDay(),
                ])
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($inner) use ($search) {
                        $inner->where('subject', 'like', "%{$search}%")
                            ->orWhere('status', 'like', "%{$search}%")
                            ->orWhere('priority', 'like', "%{$search}%")
                            ->orWhereHas('unit', fn ($unitQuery) => $unitQuery->where('name', 'like', "%{$search}%"))
                            ->orWhereHas('requester', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
                    });
                }),
            $request->user()
        );
    }

    protected function resolveDateFilters(Request $request): array
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'until' => ['nullable', 'date'],
        ]);

        $nowWita = now('Asia/Makassar');
        $defaultFrom = $nowWita->month <= 6
            ? $nowWita->copy()->startOfYear()
            : $nowWita->copy()->month(7)->startOfMonth();
        $defaultUntil = $nowWita->month <= 6
            ? $nowWita->copy()->month(6)->endOfMonth()
            : $nowWita->copy()->endOfYear();

        $from = isset($validated['from']) && $validated['from']
            ? Carbon::parse($validated['from'], 'Asia/Makassar')
            : $defaultFrom;
        $until = isset($validated['until']) && $validated['until']
            ? Carbon::parse($validated['until'], 'Asia/Makassar')
            : $defaultUntil;

        if ($from->gt($until)) {
            [$from, $until] = [$until, $from];
        }

        return [
            'from' => $from,
            'until' => $until,
            'label' => sprintf(
                '%s s/d %s',
                $from->locale('id')->translatedFormat('l, d F Y'),
                $until->locale('id')->translatedFormat('l, d F Y')
            ),
        ];
    }

    protected function resolvePerPage(Request $request): int
    {
        $perPage = (int) $request->integer('per_page', 10);

        return in_array($perPage, [10, 20, 30, 50, 100], true) ? $perPage : 10;
    }

    protected function resolveSort(Request $request): string
    {
        $sort = $request->string('sort')->toString();

        return in_array($sort, ['subject', 'priority', 'status', 'created_at'], true) ? $sort : 'created_at';
    }

    protected function resolveDirection(Request $request): string
    {
        return $request->string('direction')->toString() === 'asc' ? 'asc' : 'desc';
    }

    protected function storeQuotations(IctRequest $request, array $quotations, ?IctRequestItem $item = null): void
    {
        foreach ($quotations as $index => $quotation) {
            $attachment = $quotation['attachment'] ?? null;
            $vendorName = trim((string) ($quotation['vendor_name'] ?? ''));
            $attachmentName = null;
            $attachmentPath = null;
            $attachmentSize = null;
            $attachmentMime = null;

            if ($attachment instanceof UploadedFile) {
                [
                    'name' => $attachmentName,
                    'path' => $attachmentPath,
                    'size' => $attachmentSize,
                    'mime' => $attachmentMime,
                ] = $this->resolveQuotationAttachmentStorage($attachment);
            } elseif (filled($quotation['current_attachment_path'] ?? null)) {
                $attachmentPath = (string) $quotation['current_attachment_path'];
                $attachmentName = (string) ($quotation['current_attachment_name'] ?? null);
                $attachmentMime = (string) ($quotation['current_attachment_mime'] ?? null);
            }

            if (! $attachmentPath) {
                continue;
            }

            $request->quotations()->create([
                'ict_request_item_id' => $item?->id,
                'line_number' => $index + 1,
                'vendor_name' => $vendorName !== '' ? $vendorName : null,
                'attachment_name' => $attachmentName,
                'attachment_path' => $attachmentPath,
                'attachment_size' => $attachmentSize,
                'attachment_mime' => $attachmentMime,
            ]);
        }
    }

    protected function resolveQuotationAttachmentStorage(UploadedFile $attachment): array
    {
        return PublicFileUpload::store($attachment, 'ict-request-quotations', 255, 'quotation');
    }

    protected function resolveItemPhotoStorage(UploadedFile $photo): array
    {
        return PublicFileUpload::store($photo, 'ict-request-items', 15, 'photo');
    }

    protected function syncRequestDetails(IctRequest $ictRequest, array $items, array $globalQuotations, string $quotationMode): void
    {
        $ictRequest->loadMissing(['items.quotations', 'quotations']);

        $ictRequest->quotations()->delete();
        $ictRequest->items()->delete();

        foreach ($items as $index => $item) {
            $photo = $item['photo'] ?? null;
            $photoPath = null;
            $photoSize = null;
            $photoName = null;

            if ($photo instanceof UploadedFile) {
                [
                    'name' => $photoName,
                    'path' => $photoPath,
                    'size' => $photoSize,
                ] = $this->resolveItemPhotoStorage($photo);
            } elseif (filled($item['current_photo_path'] ?? null)) {
                $photoPath = (string) $item['current_photo_path'];
                $photoName = $item['current_photo_name'] ?? null;
            }

            $createdItem = $ictRequest->items()->create([
                'line_number' => $index + 1,
                'item_name' => (string) $item['item_name'],
                'item_category' => $item['item_category'] ?? null,
                'brand_type' => $item['brand_type'] ?? null,
                'unit' => $item['unit'] ?? null,
                'quantity' => (int) $item['quantity'],
                'estimated_price' => $item['estimated_price'] ?? null,
                'notes' => $item['item_notes'] ?? null,
                'photo_name' => $item['photo_name'] ?? $photoName,
                'photo_path' => $photoPath,
                'photo_size' => $photoSize,
            ]);

            if ($quotationMode === 'per_item') {
                $this->storeQuotations($ictRequest, $item['quotations'] ?? [], $createdItem);
            }
        }

        if ($quotationMode === 'global') {
            $this->storeQuotations($ictRequest, $globalQuotations);
        }
    }

    protected function buildFormViewData(?IctRequest $ictRequest = null): array
    {
        /** @var \App\Models\User $user */
        $user = request()->user();
        $defaultStaffIct = $user->unit_id
            ? \App\Models\User::query()
                ->where('unit_id', $user->unit_id)
                ->where('role', UserRole::StaffIct)
                ->where('is_active', true)
                ->orderBy('id')
                ->first(['name', 'job_title', 'role'])
            : null;

        $latestPtaProfile = IctRequest::query()
            ->where('requester_id', $user->id)
            ->where(function ($query) {
                $query->where('is_pta_request', true)
                    ->orWhereNotNull('drafted_by_name')
                    ->orWhereNotNull('acknowledged_by_name')
                    ->orWhereNotNull('approved_1_name')
                    ->orWhereNotNull('approved_2_name')
                    ->orWhereNotNull('approved_3_name')
                    ->orWhereNotNull('approved_4_name');
            })
            ->latest('id')
            ->first([
                'is_pta_request',
                'additional_budget_reason',
                'pta_budget_not_listed_reason',
                'pta_additional_budget_reason',
                'drafted_by_name',
                'drafted_by_title',
                'acknowledged_by_name',
                'acknowledged_by_title',
                'approved_1_name',
                'approved_1_title',
                'approved_2_name',
                'approved_2_title',
                'approved_3_name',
                'approved_3_title',
                'approved_4_name',
                'approved_4_title',
            ]);

        $latestApprovalProfile = IctRequest::query()
            ->where('unit_id', $user->unit_id)
            ->where(function ($query) {
                $query->whereNotNull('drafted_by_name')
                    ->orWhereNotNull('drafted_by_title')
                    ->orWhereNotNull('acknowledged_by_name')
                    ->orWhereNotNull('acknowledged_by_title')
                    ->orWhereNotNull('approved_1_name')
                    ->orWhereNotNull('approved_1_title')
                    ->orWhereNotNull('approved_2_name')
                    ->orWhereNotNull('approved_2_title');
            })
            ->latest('id')
            ->first([
                'drafted_by_name',
                'drafted_by_title',
                'acknowledged_by_name',
                'acknowledged_by_title',
                'approved_1_name',
                'approved_1_title',
                'approved_2_name',
                'approved_2_title',
            ]);

        $blankQuotations = collect(range(1, 3))->map(fn () => [
            'vendor_name' => '',
            'attachment_label' => '',
            'attachment_size_label' => '',
            'current_attachment_name' => '',
            'current_attachment_path' => '',
            'current_attachment_mime' => '',
        ])->all();

        $initialGlobalQuotations = $ictRequest
            ? $ictRequest->quotations
                ->whereNull('ict_request_item_id')
                ->sortBy('line_number')
                ->values()
                ->map(fn ($quotation) => [
                    'vendor_name' => (string) ($quotation->vendor_name ?? ''),
                    'attachment_label' => (string) ($quotation->attachment_name ?? ''),
                    'attachment_size_label' => '',
                    'current_attachment_name' => (string) ($quotation->attachment_name ?? ''),
                    'current_attachment_path' => (string) ($quotation->attachment_path ?? ''),
                    'current_attachment_mime' => (string) ($quotation->attachment_mime ?? ''),
                ])
                ->pad(3, [
                    'vendor_name' => '',
                    'attachment_label' => '',
                    'attachment_size_label' => '',
                    'current_attachment_name' => '',
                    'current_attachment_path' => '',
                    'current_attachment_mime' => '',
                ])->values()->all()
            : $blankQuotations;

        $initialItems = $ictRequest
            ? $ictRequest->items
                ->sortBy('line_number')
                ->values()
                ->map(function (IctRequestItem $item) use ($blankQuotations, $ictRequest) {
                    $quotations = $ictRequest->quotation_mode === 'per_item'
                        ? $item->quotations
                            ->sortBy('line_number')
                            ->values()
                            ->map(fn ($quotation) => [
                                'vendor_name' => (string) ($quotation->vendor_name ?? ''),
                                'attachment_label' => (string) ($quotation->attachment_name ?? ''),
                                'attachment_size_label' => '',
                                'current_attachment_name' => (string) ($quotation->attachment_name ?? ''),
                                'current_attachment_path' => (string) ($quotation->attachment_path ?? ''),
                                'current_attachment_mime' => (string) ($quotation->attachment_mime ?? ''),
                            ])
                            ->pad(3, $blankQuotations[0])
                            ->values()
                            ->all()
                        : $blankQuotations;

                    return [
                        'id' => $item->id,
                        'item_name' => (string) $item->item_name,
                        'item_category' => (string) ($item->item_category ?? ''),
                        'brand_type' => (string) ($item->brand_type ?? ''),
                        'quantity' => (int) $item->quantity,
                        'unit' => (string) ($item->unit ?? ''),
                        'estimated_price' => $item->estimated_price,
                        'item_notes' => (string) ($item->notes ?? ''),
                        'photo_name' => (string) ($item->photo_name ?? ''),
                        'photo_label' => (string) ($item->photo_name ?? ''),
                        'photo_size_label' => '',
                        'current_photo_name' => (string) ($item->photo_name ?? ''),
                        'current_photo_path' => (string) ($item->photo_path ?? ''),
                        'quotations' => $quotations,
                    ];
                })
                ->all()
            : [[
                'item_name' => '',
                'item_category' => '',
                'brand_type' => '',
                'quantity' => 1,
                'unit' => '',
                'estimated_price' => '',
                'item_notes' => '',
                'photo_name' => '',
                'photo_label' => '',
                'photo_size_label' => '',
                'current_photo_name' => '',
                'current_photo_path' => '',
                'quotations' => $blankQuotations,
            ]];

        $defaultDrafter = [
            'drafted_by_name' => $defaultStaffIct?->name ?: $user->name,
            'drafted_by_title' => $defaultStaffIct?->job_title ?: ($defaultStaffIct?->role?->label() ?: ($user->job_title ?: $user->role?->label())),
        ];

        $approvalProfile = $ictRequest
            ? $ictRequest
            : (object) array_merge(
                $defaultDrafter,
                [
                    'acknowledged_by_name' => $latestApprovalProfile?->acknowledged_by_name,
                    'acknowledged_by_title' => $latestApprovalProfile?->acknowledged_by_title,
                    'approved_1_name' => $latestApprovalProfile?->approved_1_name,
                    'approved_1_title' => $latestApprovalProfile?->approved_1_title,
                    'approved_2_name' => $latestApprovalProfile?->approved_2_name,
                    'approved_2_title' => $latestApprovalProfile?->approved_2_title,
                ]
            );

        $unitCode = $user->unit?->code ?? 'UNIT';
        $defaultSubject = $ictRequest
            ? ($ictRequest->subject ?: $ictRequest->form_number)
            : $this->buildFormIdentifier((string) $unitCode, $this->resolveNextFormSequence((int) $user->unit_id, now()), now()->format('y'));

        return [
            'ptaProfile' => $latestPtaProfile,
            'approvalProfile' => $approvalProfile,
            'itemCategories' => $this->itemCategories(),
            'ictRequest' => $ictRequest,
            'formMode' => $ictRequest ? 'edit' : 'create',
            'formAction' => $ictRequest
                ? route('forms.ict-requests.update', $ictRequest)
                : route('forms.ict-requests.store'),
            'formKey' => $ictRequest ? 'ict-request-edit-'.$ictRequest->id : 'ict-request-create',
            'initialQuotationMode' => old('quotation_mode', $ictRequest?->quotation_mode ?? 'global'),
            'initialPtaEnabled' => old('is_pta_request', ($ictRequest?->is_pta_request ?? $latestPtaProfile?->is_pta_request) ? '1' : '0') === '1',
            'initialItems' => old('items', $initialItems),
            'initialGlobalQuotations' => old('global_quotations', $initialGlobalQuotations),
            'defaultSubject' => old('subject', $defaultSubject),
            'requesterName' => old('requester_name', $ictRequest?->requester_name ?? $user->name ?? '-'),
            'departmentName' => old('department_name', $ictRequest?->department_name ?? $user->unit?->name ?? '-'),
            'neededAt' => old('needed_at', optional($ictRequest?->needed_at)->toDateString() ?? now()->toDateString()),
        ];
    }

    protected function itemCategories(): array
    {
        return [
            'Apps',
            'BTS',
            'Body Hardnest',
            'CCTV',
            'Domain',
            'Fingerprint',
            'Flashdisk',
            'Fotocopy',
            'Grounding',
            'HDD',
            'Hosting',
            'Hosting & Domain',
            'HTB',
            'Installasi FO',
            'Internet',
            'Jaringan',
            'Kabel',
            'Kabel Power',
            'Keyboard',
            'Laptop/Notebook',
            'Lisensi',
            'Modem',
            'Monitor',
            'Mouse',
            'Multitester',
            'Other',
            'PC',
            'Penguat Sinyal',
            'Printer',
            'Proyektor',
            'PSU',
            'Rak Server',
            'RAM',
            'Scoreboard',
            'SSD',
            'Stavol',
            'Stop Kontak',
            'Switch',
            'Tangga',
            'Tools',
            'Tower',
            'TV',
            'UPS',
            'Wi-Fi',
        ];
    }

    protected function canAccessRequest(Request $request, IctRequest $ictRequest): bool
    {
        $user = $request->user();

        return $user->isSuperAdmin() || $ictRequest->unit_id === $user->unit_id;
    }

    protected function resolveItemImageDataUri(IctRequestItem $item, bool $grayscale = false): ?string
    {
        if ($item->photo_path) {
            return $this->storagePathToDataUri($item->photo_path, $grayscale);
        }

        $quotationImagePath = $item->quotations
            ->first(fn ($quotation) => str_starts_with((string) $quotation->attachment_mime, 'image/'))
            ?->attachment_path;

        return $quotationImagePath ? $this->storagePathToDataUri($quotationImagePath, $grayscale) : null;
    }

    protected function storagePathToDataUri(string $path, bool $grayscale = false): ?string
    {
        return $this->fileToDataUri(Storage::disk('public')->path($path), $grayscale);
    }

    protected function fileToDataUri(?string $path, bool $grayscale = false): ?string
    {
        if (! $path || ! is_file($path)) {
            return null;
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            return null;
        }

        $mimeType = mime_content_type($path) ?: 'application/octet-stream';

        if ($grayscale && function_exists('imagecreatefromstring') && str_starts_with($mimeType, 'image/')) {
            $grayscaleDataUri = $this->imagePathToGrayscaleDataUri($path, $mimeType);

            if ($grayscaleDataUri) {
                return $grayscaleDataUri;
            }
        }

        return 'data:'.$mimeType.';base64,'.base64_encode($contents);
    }

    protected function imagePathToGrayscaleDataUri(string $path, string $mimeType): ?string
    {
        $contents = file_get_contents($path);

        if ($contents === false) {
            return null;
        }

        $image = @imagecreatefromstring($contents);

        if ($image === false) {
            return null;
        }

        imagefilter($image, IMG_FILTER_GRAYSCALE);

        ob_start();

        $written = match ($mimeType) {
            'image/png' => imagepng($image),
            'image/gif' => imagegif($image),
            default => imagejpeg($image, null, 90),
        };

        $binary = ob_get_clean();
        imagedestroy($image);

        if (! $written || $binary === false) {
            return null;
        }

        $outputMimeType = $mimeType === 'image/gif' ? 'image/gif' : ($mimeType === 'image/png' ? 'image/png' : 'image/jpeg');

        return 'data:'.$outputMimeType.';base64,'.base64_encode($binary);
    }

    protected function canPrintRequest($user, IctRequest $ictRequest): bool
    {
        return $ictRequest->status === 'checked_by_asmen' && ($user->isIctAdmin() || $user->isStaffIct());
    }

    protected function canModifyRequest(IctRequest $ictRequest): bool
    {
        return ! in_array($ictRequest->status, ['checked_by_asmen', 'progress_ppnk', 'progress_ppm', 'progress_po', 'progress_waiting_goods', 'progress_goods_arrived', 'completed'], true);
    }

    protected function storePpnkAttachment(UploadedFile $attachment): array
    {
        return PublicFileUpload::store($attachment, 'ict-request-ppnk', 255, 'ppnk');
    }

    protected function storePpmAttachment(UploadedFile $attachment): array
    {
        return PublicFileUpload::store($attachment, 'ict-request-ppm', 255, 'ppm');
    }

    protected function storePoAttachment(UploadedFile $attachment): array
    {
        return PublicFileUpload::store($attachment, 'ict-request-po', 255, 'po');
    }

    protected function deleteIctRequestFiles(IctRequest $ictRequest): void
    {
        $ictRequest->load(['items', 'quotations', 'ppnkDocuments']);

        // Hapus final signed PDF
        if ($ictRequest->final_signed_pdf_path) {
            Storage::disk('public')->delete($ictRequest->final_signed_pdf_path);
        }

        // Hapus revision attachment
        if ($ictRequest->revision_attachment_path) {
            Storage::disk('public')->delete($ictRequest->revision_attachment_path);
        }

        // Hapus foto dan file dari items
        foreach ($ictRequest->items as $item) {
            if ($item->photo_path) {
                Storage::disk('public')->delete($item->photo_path);
            }
        }

        // Hapus attachment dari quotations
        foreach ($ictRequest->quotations as $quotation) {
            if ($quotation->attachment_path) {
                Storage::disk('public')->delete($quotation->attachment_path);
            }
        }

        // Hapus attachment dari ppnk documents
        foreach ($ictRequest->ppnkDocuments as $ppnkDoc) {
            if ($ppnkDoc->attachment_path) {
                Storage::disk('public')->delete($ppnkDoc->attachment_path);
            }
        }

        // Hapus items dan quotations (akan terhapus via cascade)
        $ictRequest->items()->delete();
        $ictRequest->quotations()->delete();
        $ictRequest->ppnkDocuments()->delete();
        $ictRequest->reviewHistories()->delete();
    }

    public function confirmGoodsArrival(Request $request, IctRequest $ictRequest): RedirectResponse
    {
        abort_unless($this->canAccessRequest($request, $ictRequest), 403);
        abort_unless($request->user()->isIctAdmin(), 403);
        abort_unless($ictRequest->status === 'progress_waiting_goods', 403);

        $validated = $request->validate([
            'goods_arrived_date' => ['required', 'date'],
        ]);

        $ictRequest->update([
            'status' => 'progress_goods_arrived',
            'goods_arrived_at' => Carbon::parse($validated['goods_arrived_date'])->startOfDay(),
        ]);

        return back()->with('status', 'Barang dikonfirmasi sudah datang. Lanjutkan proses penerimaan barang.');
    }

    public function storeGoodsReceipt(Request $request, IctRequest $ictRequest): RedirectResponse
    {
        abort_unless($this->canAccessRequest($request, $ictRequest), 403);
        abort_unless($request->user()->isIctAdmin(), 403);
        abort_unless($ictRequest->status === 'progress_goods_arrived', 403);

        $validated = $request->validate([
            'goods_receipt_date' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'integer'],
            'items.*.handover_type' => ['required', 'in:asset,non_asset'],
            
            // Common fields
            'items.*.description' => ['nullable', 'string', 'max:1000'],
            'items.*.serah_terima' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
            'items.*.surat_jalan' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
            
            // Asset-specific fields
            'items.*.dept' => ['nullable', 'string', 'max:255'],
            'items.*.model_specification' => ['nullable', 'string', 'max:500'],
            'items.*.serial_number' => ['nullable', 'string', 'max:255'],
            'items.*.asset_number' => ['nullable', 'string', 'max:255'],
            'items.*.recipient_name' => ['nullable', 'string', 'max:255'],
            'items.*.recipient_position' => ['nullable', 'string', 'max:255'],
            'items.*.supervisor_name' => ['nullable', 'string', 'max:255'],
            'items.*.supervisor_position' => ['nullable', 'string', 'max:255'],
            'items.*.witness_name' => ['nullable', 'string', 'max:255'],
            'items.*.witness_position' => ['nullable', 'string', 'max:255'],
            'items.*.deliverer_name' => ['nullable', 'string', 'max:255'],
            'items.*.deliverer_position' => ['nullable', 'string', 'max:255'],
        ]);
        $goodsReceiptDate = Carbon::parse($validated['goods_receipt_date'])->startOfDay();

        $ictRequest->loadMissing(['items', 'unit']);

        foreach ($validated['items'] as $itemData) {
            $ictRequestItem = $ictRequest->items->firstWhere('id', $itemData['item_id']);
            abort_unless($ictRequestItem, 422);

            $handoverData = [
                'ict_request_id' => $ictRequest->id,
                'ict_request_item_id' => $ictRequestItem->id,
                'handover_type' => $itemData['handover_type'],
                'received_at' => $goodsReceiptDate,
                'created_by' => $request->user()->id,
            ];

            // Handle file uploads for non-asset or common documents
            if (isset($itemData['serah_terima']) && $itemData['serah_terima'] instanceof UploadedFile) {
                $stored = $this->storeHandoverAttachment($itemData['serah_terima'], 'serah_terima');
                $handoverData['serah_terima_path'] = $stored['path'];
                $handoverData['serah_terima_name'] = $stored['name'];
                $handoverData['serah_terima_size'] = $stored['size'];
                $handoverData['serah_terima_mime'] = $stored['mime'];
            }

            if (isset($itemData['surat_jalan']) && $itemData['surat_jalan'] instanceof UploadedFile) {
                $stored = $this->storeHandoverAttachment($itemData['surat_jalan'], 'surat_jalan');
                $handoverData['surat_jalan_path'] = $stored['path'];
                $handoverData['surat_jalan_name'] = $stored['name'];
                $handoverData['surat_jalan_size'] = $stored['size'];
                $handoverData['surat_jalan_mime'] = $stored['mime'];
            }

            // Handle asset-specific data
            if ($itemData['handover_type'] === 'asset') {
                $handoverData = array_merge($handoverData, [
                    'dept' => $itemData['dept'] ?? null,
                    'model_specification' => $itemData['model_specification'] ?? null,
                    'serial_number' => $itemData['serial_number'] ?? null,
                    'asset_number' => $itemData['asset_number'] ?? null,
                    'recipient_name' => $itemData['recipient_name'] ?? null,
                    'recipient_position' => $itemData['recipient_position'] ?? null,
                    'supervisor_name' => $itemData['supervisor_name'] ?? null,
                    'supervisor_position' => $itemData['supervisor_position'] ?? null,
                    'witness_name' => $itemData['witness_name'] ?? null,
                    'witness_position' => $itemData['witness_position'] ?? null,
                    'deliverer_name' => $itemData['deliverer_name'] ?? null,
                    'deliverer_position' => $itemData['deliverer_position'] ?? null,
                ]);

                // Create asset record
                $asset = Asset::create([
                    'unit_id' => $ictRequest->unit_id,
                    'assigned_user_id' => null,
                    'uuid' => Str::uuid()->toString(),
                    'asset_number' => $itemData['asset_number'] ?? null,
                    'category' => $ictRequestItem->item_category ?? 'hardware',
                    'name' => $ictRequestItem->item_name,
                    'brand' => $ictRequestItem->brand_type,
                    'model' => $itemData['model_specification'] ?? null,
                    'serial_number' => $itemData['serial_number'] ?? null,
                    'specification' => $itemData['model_specification'] ? ['description' => $itemData['model_specification']] : null,
                    'location' => $itemData['dept'] ?? null,
                    'purchase_date' => $goodsReceiptDate,
                    'condition_status' => 'good',
                    'lifecycle_status' => 'active',
                ]);

                $handoverData['asset_id'] = $asset->id;
            } else {
                // Non-asset: only description
                $handoverData['description'] = $itemData['description'] ?? null;
            }

            $handover = AssetHandover::create($handoverData);

            // Generate handover report (Berita Acara) for asset type
            if ($itemData['handover_type'] === 'asset') {
                $this->generateHandoverReport($handover, $ictRequest, $ictRequestItem);
            }
        }

        // Update status to completed after all items processed
        $ictRequest->update([
            'status' => 'completed',
            'goods_delivered_at' => $goodsReceiptDate,
        ]);

        return back()->with('status', 'Penerimaan barang berhasil disimpan. Status: Barang Telah Diserahkan.');
    }

    protected function generateHandoverReport(AssetHandover $handover, IctRequest $ictRequest, IctRequestItem $item): void
    {
        $pdf = Pdf::loadView('forms.ict-requests.handover-report', [
            'handover' => $handover,
            'ictRequest' => $ictRequest,
            'item' => $item,
        ])->setPaper('a4', 'portrait');

        $fileName = 'berita-acara-' . $handover->id . '-' . Str::slug(substr($item->item_name, 0, 30)) . '.pdf';
        $filePath = 'ict-handover-reports/' . $fileName;

        // Save to storage
        Storage::disk('public')->put($filePath, $pdf->output());

        // Update handover with report info
        $handover->update([
            'handover_report_path' => $filePath,
            'handover_report_name' => $fileName,
            'handover_report_generated_at' => now(),
        ]);
    }

    protected function storeHandoverAttachment(UploadedFile $file, string $prefix): array
    {
        return PublicFileUpload::store($file, 'ict-handover-documents', 255, $prefix);
    }

    public function handoverReportPdf(IctRequest $ictRequest, AssetHandover $assetHandover): BinaryFileResponse|Response
    {
        $request = request();
        abort_unless($this->canAccessRequest($request, $ictRequest), 403);
        abort_unless($assetHandover->ict_request_id === $ictRequest->id, 404);
        $assetHandover->loadMissing('ictRequestItem');
        abort_unless($assetHandover->ictRequestItem, 404);

        $pdf = Pdf::loadView('forms.ict-requests.handover-report', [
            'handover' => $assetHandover,
            'ictRequest' => $ictRequest,
            'item' => $assetHandover->ictRequestItem,
        ])->setPaper('a4', 'portrait');

        $fileName = $assetHandover->handover_report_name ?: 'serah-terima-fasilitas-ict-'.$assetHandover->id.'.pdf';

        return $pdf->stream($fileName);
    }
}
