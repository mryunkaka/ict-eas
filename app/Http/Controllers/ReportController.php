<?php

namespace App\Http\Controllers;

use App\Exports\ModuleReportExport;
use App\Exports\MonitoringPpExcelExport;
use App\Imports\MonitoringPpImport;
use App\Models\Asset;
use App\Models\AssetHandover;
use App\Models\EmailRequest;
use App\Models\IctRequest;
use App\Models\IctRequestItem;
use App\Models\IctRequestPoDocument;
use App\Models\IctRequestPpmDocument;
use App\Models\IctRequestPpnkDocument;
use App\Models\IncidentReport;
use App\Models\ProjectRequest;
use App\Models\RepairRequest;
use App\Support\PublicFileUpload;
use App\Models\Unit;
use App\Models\User;
use App\Support\UnitScope;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function monitoringPp(Request $request): View
    {
        $validated = $request->validate([
            'unit_id' => ['nullable', 'exists:units,id'],
            'from' => ['nullable', 'date'],
            'until' => ['nullable', 'date'],
        ]);

        [$canFilterAllUnits, $selectedUnitId] = $this->resolveMonitoringPpScope($request, $validated['unit_id'] ?? null);
        $units = $canFilterAllUnits
            ? Unit::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id')->all()
            : [];

        return view('reports.monitoring-pp', [
            'units' => $units,
            'selectedUnitId' => $selectedUnitId,
            'canFilterAllUnits' => $canFilterAllUnits,
            'canImportMonitoringPp' => $request->user()->isIctAdmin() || $request->user()->isSuperAdmin(),
            'canManageMonitoringPpUploads' => $request->user()->isIctAdmin() || $request->user()->isSuperAdmin(),
            'canBulkDeleteMonitoringPp' => $request->user()->canPermanentDeleteIctRequest(),
        ]);
    }

    public function monitoringPpData(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'draw' => ['nullable', 'integer'],
            'start' => ['nullable', 'integer', 'min:0'],
            'length' => ['nullable', 'integer', 'min:10', 'max:200'],
            'search.value' => ['nullable', 'string'],
            'order' => ['nullable', 'array'],
            'columns' => ['nullable', 'array'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'from' => ['nullable', 'date'],
            'until' => ['nullable', 'date'],
        ]);

        [$canFilterAllUnits, $selectedUnitId] = $this->resolveMonitoringPpScope($request, $validated['unit_id'] ?? null);
        $canManageMonitoringPpUploads = $request->user()->isIctAdmin() || $request->user()->isSuperAdmin();
        $canBulkDeleteMonitoringPp = $request->user()->canPermanentDeleteIctRequest();
        $start = (int) ($validated['start'] ?? 0);
        $length = (int) ($validated['length'] ?? 25);
        $search = trim((string) data_get($validated, 'search.value', ''));
        $draw = (int) ($validated['draw'] ?? 1);

        $baseQuery = DB::table('ict_request_items')
            ->join('ict_requests', 'ict_requests.id', '=', 'ict_request_items.ict_request_id')
            ->join('units', 'units.id', '=', 'ict_requests.unit_id')
            ->leftJoin('ict_request_ppnk_documents', 'ict_request_ppnk_documents.id', '=', 'ict_request_items.ppnk_document_id')
            ->leftJoin('ict_request_ppm_documents', 'ict_request_ppm_documents.id', '=', 'ict_request_items.ppm_document_id')
            ->leftJoin('ict_request_po_documents', 'ict_request_po_documents.id', '=', 'ict_request_items.po_document_id')
            ->leftJoin('asset_handovers', 'asset_handovers.ict_request_item_id', '=', 'ict_request_items.id')
            ->when($selectedUnitId, fn ($query) => $query->where('ict_requests.unit_id', $selectedUnitId))
            ->when($validated['from'] ?? null, fn ($query, $from) => $query->whereDate('ict_requests.created_at', '>=', $from))
            ->when($validated['until'] ?? null, fn ($query, $until) => $query->whereDate('ict_requests.created_at', '<=', $until));

        $recordsTotal = (clone $baseQuery)->count('ict_request_items.id');

        if ($search !== '') {
            $like = '%'.$search.'%';
            $baseQuery->where(function ($query) use ($like) {
                $query
                    ->where('units.name', 'like', $like)
                    ->orWhere('ict_request_items.item_category', 'like', $like)
                    ->orWhere('ict_request_items.item_name', 'like', $like)
                    ->orWhere('ict_request_items.brand_type', 'like', $like)
                    ->orWhere('ict_request_items.notes', 'like', $like)
                    ->orWhere('ict_requests.form_number', 'like', $like)
                    ->orWhere('ict_request_items.ppnk_number', 'like', $like)
                    ->orWhere('ict_request_items.ppm_name', 'like', $like)
                    ->orWhere('ict_request_items.ppm_number', 'like', $like)
                    ->orWhere('ict_request_items.pr_number', 'like', $like)
                    ->orWhere('ict_request_items.po_number', 'like', $like)
                    ->orWhere('asset_handovers.recipient_name', 'like', $like)
                    ->orWhere('asset_handovers.supervisor_name', 'like', $like)
                    ->orWhere('asset_handovers.asset_number', 'like', $like)
                    ->orWhere('asset_handovers.serial_number', 'like', $like)
                    ->orWhere('ict_requests.status', 'like', $like);
            });
        }

        $recordsFiltered = (clone $baseQuery)->count('ict_request_items.id');

        $columns = $canBulkDeleteMonitoringPp
            ? [
                1 => 'units.name',
                2 => 'ict_request_items.item_category',
                3 => 'ict_request_items.item_name',
                4 => 'ict_request_items.brand_type',
                5 => 'ict_request_items.quantity',
                6 => 'ict_request_items.estimated_price',
                10 => 'ict_requests.created_at',
                12 => 'ict_request_items.ppnk_uploaded_at',
                14 => 'ict_request_items.ppnk_number',
                15 => 'ict_request_items.ppm_uploaded_at',
                18 => 'ict_request_items.ppm_number',
                19 => 'ict_request_items.pr_number',
                20 => 'ict_request_items.po_uploaded_at',
                22 => 'ict_request_items.po_number',
                23 => 'asset_handovers.created_at',
                24 => 'asset_handovers.handover_report_generated_at',
                26 => 'asset_handovers.model_specification',
                27 => 'asset_handovers.serial_number',
                28 => 'asset_handovers.asset_number',
                29 => 'asset_handovers.recipient_name',
                30 => 'asset_handovers.recipient_position',
                31 => 'asset_handovers.supervisor_name',
                32 => 'asset_handovers.supervisor_position',
                33 => 'ict_requests.status',
            ]
            : [
                1 => 'units.name',
                2 => 'ict_request_items.item_category',
                3 => 'ict_request_items.item_name',
                4 => 'ict_request_items.brand_type',
                5 => 'ict_request_items.quantity',
                6 => 'ict_request_items.estimated_price',
                10 => 'ict_requests.created_at',
                12 => 'ict_request_items.ppnk_uploaded_at',
                14 => 'ict_request_items.ppnk_number',
                15 => 'ict_request_items.ppm_uploaded_at',
                18 => 'ict_request_items.ppm_number',
                19 => 'ict_request_items.pr_number',
                20 => 'ict_request_items.po_uploaded_at',
                22 => 'ict_request_items.po_number',
                23 => 'asset_handovers.created_at',
                24 => 'asset_handovers.handover_report_generated_at',
                26 => 'asset_handovers.model_specification',
                27 => 'asset_handovers.serial_number',
                28 => 'asset_handovers.asset_number',
                29 => 'asset_handovers.recipient_name',
                30 => 'asset_handovers.recipient_position',
                31 => 'asset_handovers.supervisor_name',
                32 => 'asset_handovers.supervisor_position',
                33 => 'ict_requests.status',
            ];

        $orderColumnIndex = (int) data_get($validated, 'order.0.column', 0);
        $orderDirection = strtolower((string) data_get($validated, 'order.0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $orderColumn = $columns[$orderColumnIndex] ?? 'ict_request_items.id';

        $rows = $baseQuery
            ->select([
                'ict_request_items.id',
                'ict_request_items.item_category',
                'ict_request_items.item_name',
                'ict_request_items.brand_type',
                'ict_request_items.quantity',
                'ict_request_items.estimated_price',
                'ict_request_items.notes',
                'ict_request_items.photo_name',
                'ict_request_items.photo_path',
                'ict_request_items.pr_number',
                'ict_request_items.ppnk_number as imported_ppnk_number',
                'ict_request_items.ppnk_uploaded_at as imported_ppnk_uploaded_at',
                'ict_request_items.ppm_name as imported_ppm_name',
                'ict_request_items.ppm_number as imported_ppm_number',
                'ict_request_items.ppm_uploaded_at as imported_ppm_uploaded_at',
                'ict_request_items.po_number as imported_po_number',
                'ict_request_items.po_uploaded_at as imported_po_uploaded_at',
                'ict_requests.id as ict_request_id',
                'ict_requests.form_number',
                'ict_requests.status',
                'ict_requests.print_count',
                'ict_requests.final_signed_pdf_name',
                'ict_requests.final_signed_pdf_path',
                'ict_requests.created_at as form_created_at',
                'units.name as unit_name',
                'ict_request_ppnk_documents.attachment_name as ppnk_attachment_name',
                'ict_request_ppnk_documents.attachment_path as ppnk_attachment_path',
                'ict_request_ppnk_documents.ppnk_number',
                'ict_request_ppnk_documents.uploaded_at as ppnk_uploaded_at',
                'ict_request_ppm_documents.attachment_name as ppm_attachment_name',
                'ict_request_ppm_documents.attachment_path as ppm_attachment_path',
                'ict_request_ppm_documents.ppm_number',
                'ict_request_ppm_documents.uploaded_at as ppm_uploaded_at',
                'ict_request_po_documents.attachment_name as po_attachment_name',
                'ict_request_po_documents.attachment_path as po_attachment_path',
                'ict_request_po_documents.po_number',
                'ict_request_po_documents.uploaded_at as po_uploaded_at',
                'asset_handovers.id as asset_handover_id',
                'asset_handovers.created_at as received_at',
                'asset_handovers.handover_report_generated_at',
                'asset_handovers.handover_report_name',
                'asset_handovers.serah_terima_name',
                'asset_handovers.serah_terima_path',
                'asset_handovers.model_specification',
                'asset_handovers.serial_number',
                'asset_handovers.asset_number',
                'asset_handovers.recipient_name',
                'asset_handovers.recipient_position',
                'asset_handovers.supervisor_name',
                'asset_handovers.supervisor_position',
            ])
            ->orderBy($orderColumn, $orderDirection)
            ->orderByDesc('ict_request_items.id')
            ->offset($start)
            ->limit($length)
            ->get()
            ->values()
            ->map(function ($row, $index) use ($start, $canManageMonitoringPpUploads, $canBulkDeleteMonitoringPp) {
                $photoUrl = $row->photo_path ? Storage::disk('public')->url($row->photo_path) : null;
                $signedFormUrl = $row->final_signed_pdf_path ? Storage::disk('public')->url($row->final_signed_pdf_path) : null;
                $ppnkUrl = $row->ppnk_attachment_path ? Storage::disk('public')->url($row->ppnk_attachment_path) : null;
                $ppmUrl = $row->ppm_attachment_path ? Storage::disk('public')->url($row->ppm_attachment_path) : null;
                $poUrl = $row->po_attachment_path ? Storage::disk('public')->url($row->po_attachment_path) : null;
                $baUrl = $row->serah_terima_path ? Storage::disk('public')->url($row->serah_terima_path) : null;
                $ppnkNumber = $row->ppnk_number ?: $row->imported_ppnk_number;
                $ppnkUploadedAt = $row->ppnk_uploaded_at ?: $row->imported_ppnk_uploaded_at;
                $ppmName = $row->imported_ppm_name ?: $row->ppm_attachment_name;
                $ppmNumber = $row->ppm_number ?: $row->imported_ppm_number;
                $ppmUploadedAt = $row->ppm_uploaded_at ?: $row->imported_ppm_uploaded_at;
                $poNumber = $row->po_number ?: $row->imported_po_number;
                $poUploadedAt = $row->po_uploaded_at ?: $row->imported_po_uploaded_at;
                $statusLabel = $this->formatIctRequestStatusLabel($row->status, (int) $row->print_count, $row->final_signed_pdf_path);
                $ppnkUploadTrigger = $canManageMonitoringPpUploads
                    ? $this->renderMonitoringUploadTrigger(
                        'Upload',
                        'ppnk',
                        route('reports.monitoring-pp.upload.ppnk', $row->id),
                        [
                            'itemName' => $row->item_name ?: 'Barang',
                            'ppnkNumber' => $ppnkNumber ?: '',
                            'uploadedAt' => $ppnkUploadedAt ? Carbon::parse($ppnkUploadedAt)->format('Y-m-d') : '',
                        ]
                    )
                    : '-';
                $ppmUploadTrigger = $canManageMonitoringPpUploads
                    ? $this->renderMonitoringUploadTrigger(
                        'Upload',
                        'ppm',
                        route('reports.monitoring-pp.upload.ppm', $row->id),
                        [
                            'itemName' => $row->item_name ?: 'Barang',
                            'ppmNumber' => $ppmNumber ?: '',
                            'prNumber' => $row->pr_number ?: '',
                            'uploadedAt' => $ppmUploadedAt ? Carbon::parse($ppmUploadedAt)->format('Y-m-d') : '',
                        ]
                    )
                    : '-';
                $poUploadTrigger = $canManageMonitoringPpUploads
                    ? $this->renderMonitoringUploadTrigger(
                        'Upload',
                        'po',
                        route('reports.monitoring-pp.upload.po', $row->id),
                        [
                            'itemName' => $row->item_name ?: 'Barang',
                            'poNumber' => $poNumber ?: '',
                            'uploadedAt' => $poUploadedAt ? Carbon::parse($poUploadedAt)->format('Y-m-d') : '',
                        ]
                    )
                    : '-';

                $data = [
                    $start + $index + 1,
                    e($row->unit_name ?: '-'),
                    e($row->item_category ?: '-'),
                    e($row->item_name ?: '-'),
                    e($row->brand_type ?: '-'),
                    number_format((int) ($row->quantity ?? 0)),
                    e($this->formatCurrency($row->estimated_price)),
                    e($this->formatCurrency(((float) ($row->estimated_price ?? 0)) * ((int) ($row->quantity ?? 0)))),
                    e($row->notes ?: '-'),
                    $photoUrl
                        ? '<a href="'.e($photoUrl).'" target="_blank"><img src="'.e($photoUrl).'" alt="'.e($row->photo_name ?: $row->item_name ?: 'Barang').'" class="h-16 w-16 rounded-xl border border-ink-200 object-cover"></a>'
                        : ($canManageMonitoringPpUploads
                            ? $this->renderMonitoringUploadTrigger(
                                'Upload',
                                'photo',
                                route('reports.monitoring-pp.upload.photo', $row->id),
                                ['itemName' => $row->item_name ?: 'Barang']
                            )
                            : '-'),
                    e($this->formatDate($row->form_created_at)),
                    $signedFormUrl
                        ? '<a href="'.e($signedFormUrl).'" target="_blank" class="font-medium text-brand-700 hover:text-brand-800">'.e($row->final_signed_pdf_name ?: $row->form_number ?: 'Lihat Form').'</a>'
                        : ($canManageMonitoringPpUploads
                            ? $this->renderMonitoringUploadTrigger(
                                'Upload',
                                'signed-form',
                                route('reports.monitoring-pp.upload.signed-form', $row->ict_request_id),
                                [
                                    'itemName' => $row->form_number ?: 'Form ICT',
                                    'formNumber' => $row->form_number ?: '',
                                ]
                            )
                            : '-'),
                    $ppnkUploadedAt ? e($this->formatDate($ppnkUploadedAt)) : $ppnkUploadTrigger,
                    $ppnkUrl
                        ? '<a href="'.e($ppnkUrl).'" target="_blank" class="font-medium text-brand-700 hover:text-brand-800">Lihat Berkas</a>'
                        : $ppnkUploadTrigger,
                    $ppnkNumber ? e($ppnkNumber) : $ppnkUploadTrigger,
                    e($this->formatDate($ppmUploadedAt)),
                    $ppmUrl
                        ? '<a href="'.e($ppmUrl).'" target="_blank" class="font-medium text-brand-700 hover:text-brand-800">Lihat Berkas</a>'
                        : $ppmUploadTrigger,
                    e($ppmName ?: '-'),
                    e($ppmNumber ?: '-'),
                    e($row->pr_number ?: '-'),
                    e($this->formatDate($poUploadedAt)),
                    $poUrl
                        ? '<a href="'.e($poUrl).'" target="_blank" class="font-medium text-brand-700 hover:text-brand-800">Lihat Berkas</a>'
                        : $poUploadTrigger,
                    e($poNumber ?: '-'),
                    e($this->formatDate($row->received_at)),
                    e($this->formatDate($row->handover_report_generated_at)),
                    $baUrl
                        ? '<a href="'.e($baUrl).'" target="_blank" class="font-medium text-brand-700 hover:text-brand-800">'.e($row->serah_terima_name ?: 'Lihat BA').'</a>'
                        : ($canManageMonitoringPpUploads && $row->asset_handover_id
                            ? $this->renderMonitoringUploadTrigger(
                                'Upload',
                                'ba',
                                route('reports.monitoring-pp.upload.ba', $row->asset_handover_id),
                                ['itemName' => $row->item_name ?: 'Barang']
                            )
                            : '-'),
                    e($row->model_specification ?: '-'),
                    e($row->serial_number ?: '-'),
                    e($row->asset_number ?: '-'),
                    e($row->recipient_name ?: '-'),
                    e($row->recipient_position ?: '-'),
                    e($row->supervisor_name ?: '-'),
                    e($row->supervisor_position ?: '-'),
                    $this->renderStatusBadge($row->status, $statusLabel),
                ];

                if ($canBulkDeleteMonitoringPp) {
                    array_unshift(
                        $data,
                        '<input type="checkbox" class="monitoring-pp-request-checkbox rounded border-ink-300 text-brand-700 focus:ring-brand-500" value="'.e((string) $row->ict_request_id).'" />'
                    );
                }

                return $data;
            });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows,
            'canFilterAllUnits' => $canFilterAllUnits,
        ]);
    }

    public function monitoringPpUploadPhoto(Request $request, IctRequestItem $item): RedirectResponse
    {
        $ictRequest = $item->ictRequest()->firstOrFail();
        abort_unless($this->canAccessMonitoringRequest($request, $ictRequest), 403);
        abort_unless($request->user()->isIctAdmin() || $request->user()->isSuperAdmin(), 403);

        $validated = $request->validate([
            'photo' => ['required', 'image', 'max:5120'],
        ]);

        if ($item->photo_path) {
            Storage::disk('public')->delete($item->photo_path);
        }

        /** @var UploadedFile $photo */
        $photo = $validated['photo'];
        $stored = PublicFileUpload::storeStableOrReuse($photo, 'ict-request-items', 15);

        $item->update([
            'photo_name' => $stored['name'],
            'photo_path' => $stored['path'],
            'photo_size' => $stored['size'],
        ]);

        return back()->with('status', 'Foto barang berhasil diupload.');
    }

    public function monitoringPpUploadSignedForm(Request $request, IctRequest $ictRequest): RedirectResponse
    {
        abort_unless($this->canAccessMonitoringRequest($request, $ictRequest), 403);
        abort_unless($request->user()->isIctAdmin() || $request->user()->isSuperAdmin(), 403);

        $validated = $request->validate([
            'signed_pdf' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ]);

        if ($ictRequest->final_signed_pdf_path) {
            Storage::disk('public')->delete($ictRequest->final_signed_pdf_path);
        }

        /** @var UploadedFile $file */
        $file = $validated['signed_pdf'];
        $stored = PublicFileUpload::storeStableOrReuse($file, 'ict-request-signed', 255);

        $ictRequest->update([
            'final_signed_pdf_name' => $stored['name'],
            'final_signed_pdf_path' => $stored['path'],
            'final_signed_pdf_uploaded_by' => $request->user()->id,
            'final_signed_pdf_uploaded_at' => now(),
        ]);

        return back()->with('status', 'Form ICT full TTD berhasil diupload.');
    }

    public function monitoringPpUploadPpnk(Request $request, IctRequestItem $item): RedirectResponse
    {
        $ictRequest = $item->ictRequest()->firstOrFail();
        abort_unless($this->canAccessMonitoringRequest($request, $ictRequest), 403);
        abort_unless($request->user()->isIctAdmin() || $request->user()->isSuperAdmin(), 403);

        $validated = $request->validate([
            'ppnk_number' => ['required', 'string', 'max:255'],
            'uploaded_at' => ['nullable', 'date'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $document = $item->ppnkDocument ?: IctRequestPpnkDocument::query()->where([
            'ict_request_id' => $ictRequest->id,
            'ppnk_number' => $validated['ppnk_number'],
        ])->first();

        $payload = [
            'ict_request_id' => $ictRequest->id,
            'ppnk_number' => $validated['ppnk_number'],
            'uploaded_by' => $request->user()->id,
            'uploaded_at' => $validated['uploaded_at'] ?? now(),
        ];

        if (($validated['attachment'] ?? null) instanceof UploadedFile) {
            if ($document?->attachment_path) {
                Storage::disk('public')->delete($document->attachment_path);
            }

            /** @var UploadedFile $attachment */
            $attachment = $validated['attachment'];
            $stored = PublicFileUpload::storeStableOrReuse($attachment, 'ict-request-ppnk', 255);
            $payload = array_merge($payload, [
                'attachment_name' => $stored['name'],
                'attachment_path' => $stored['path'],
                'attachment_size' => $stored['size'],
                'attachment_mime' => $stored['mime'],
            ]);
        } elseif ($document) {
            $payload = array_merge($payload, [
                'attachment_name' => $document->attachment_name,
                'attachment_path' => $document->attachment_path,
                'attachment_size' => $document->attachment_size,
                'attachment_mime' => $document->attachment_mime,
            ]);
        }

        $document = IctRequestPpnkDocument::updateOrCreate(
            ['ict_request_id' => $ictRequest->id, 'ppnk_number' => $validated['ppnk_number']],
            $payload
        );

        $item->update([
            'ppnk_document_id' => $document->id,
            'ppnk_number' => $validated['ppnk_number'],
            'ppnk_uploaded_at' => $validated['uploaded_at'] ?? now(),
        ]);

        return back()->with('status', 'Dokumen PPNK/PPK berhasil diupload.');
    }

    public function monitoringPpUploadPpm(Request $request, IctRequestItem $item): RedirectResponse
    {
        $ictRequest = $item->ictRequest()->firstOrFail();
        abort_unless($this->canAccessMonitoringRequest($request, $ictRequest), 403);
        abort_unless($request->user()->isIctAdmin() || $request->user()->isSuperAdmin(), 403);

        $validated = $request->validate([
            'ppm_number' => ['required', 'string', 'max:255'],
            'pr_number' => ['nullable', 'string', 'max:255'],
            'uploaded_at' => ['nullable', 'date'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $document = $item->ppmDocument ?: IctRequestPpmDocument::query()->where([
            'ict_request_id' => $ictRequest->id,
            'ppm_number' => $validated['ppm_number'],
        ])->first();

        $payload = [
            'ict_request_id' => $ictRequest->id,
            'ppm_number' => $validated['ppm_number'],
            'uploaded_by' => $request->user()->id,
            'uploaded_at' => $validated['uploaded_at'] ?? now(),
        ];

        if (($validated['attachment'] ?? null) instanceof UploadedFile) {
            if ($document?->attachment_path) {
                Storage::disk('public')->delete($document->attachment_path);
            }

            /** @var UploadedFile $attachment */
            $attachment = $validated['attachment'];
            $stored = PublicFileUpload::storeStableOrReuse($attachment, 'ict-request-ppm', 255);
            $payload = array_merge($payload, [
                'attachment_name' => $stored['name'],
                'attachment_path' => $stored['path'],
                'attachment_size' => $stored['size'],
                'attachment_mime' => $stored['mime'],
            ]);
        } elseif ($document) {
            $payload = array_merge($payload, [
                'attachment_name' => $document->attachment_name,
                'attachment_path' => $document->attachment_path,
                'attachment_size' => $document->attachment_size,
                'attachment_mime' => $document->attachment_mime,
            ]);
        }

        $document = IctRequestPpmDocument::updateOrCreate(
            ['ict_request_id' => $ictRequest->id, 'ppm_number' => $validated['ppm_number']],
            $payload
        );

        $item->update([
            'ppm_document_id' => $document->id,
            'ppm_uploaded_at' => $validated['uploaded_at'] ?? now(),
            'ppm_number' => $validated['ppm_number'],
            'pr_number' => $validated['pr_number'] ?? $item->pr_number,
        ]);

        return back()->with('status', 'Dokumen PPM berhasil diupload.');
    }

    public function monitoringPpUploadPo(Request $request, IctRequestItem $item): RedirectResponse
    {
        $ictRequest = $item->ictRequest()->firstOrFail();
        abort_unless($this->canAccessMonitoringRequest($request, $ictRequest), 403);
        abort_unless($request->user()->isIctAdmin() || $request->user()->isSuperAdmin(), 403);

        $validated = $request->validate([
            'po_number' => ['required', 'string', 'max:255'],
            'uploaded_at' => ['nullable', 'date'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $document = $item->poDocument ?: IctRequestPoDocument::query()->where([
            'ict_request_id' => $ictRequest->id,
            'po_number' => $validated['po_number'],
        ])->first();

        $payload = [
            'ict_request_id' => $ictRequest->id,
            'po_number' => $validated['po_number'],
            'uploaded_by' => $request->user()->id,
            'uploaded_at' => $validated['uploaded_at'] ?? now(),
        ];

        if (($validated['attachment'] ?? null) instanceof UploadedFile) {
            if ($document?->attachment_path) {
                Storage::disk('public')->delete($document->attachment_path);
            }

            /** @var UploadedFile $attachment */
            $attachment = $validated['attachment'];
            $stored = PublicFileUpload::storeStableOrReuse($attachment, 'ict-request-po', 255);
            $payload = array_merge($payload, [
                'attachment_name' => $stored['name'],
                'attachment_path' => $stored['path'],
                'attachment_size' => $stored['size'],
                'attachment_mime' => $stored['mime'],
            ]);
        } elseif ($document) {
            $payload = array_merge($payload, [
                'attachment_name' => $document->attachment_name,
                'attachment_path' => $document->attachment_path,
                'attachment_size' => $document->attachment_size,
                'attachment_mime' => $document->attachment_mime,
            ]);
        }

        $document = IctRequestPoDocument::updateOrCreate(
            ['ict_request_id' => $ictRequest->id, 'po_number' => $validated['po_number']],
            $payload
        );

        $item->update([
            'po_document_id' => $document->id,
            'po_uploaded_at' => $validated['uploaded_at'] ?? now(),
            'po_number' => $validated['po_number'],
        ]);

        return back()->with('status', 'Dokumen PO berhasil diupload.');
    }

    public function monitoringPpUploadBa(Request $request, AssetHandover $assetHandover): RedirectResponse
    {
        $ictRequest = $assetHandover->ictRequest()->firstOrFail();
        abort_unless($this->canAccessMonitoringRequest($request, $ictRequest), 403);
        abort_unless($request->user()->isIctAdmin() || $request->user()->isSuperAdmin(), 403);

        $validated = $request->validate([
            'attachment' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ]);

        if ($assetHandover->serah_terima_path) {
            Storage::disk('public')->delete($assetHandover->serah_terima_path);
        }

        /** @var UploadedFile $attachment */
        $attachment = $validated['attachment'];
        $stored = PublicFileUpload::storeStableOrReuse($attachment, 'ict-handover-documents', 255);

        $assetHandover->update([
            'serah_terima_name' => $stored['name'],
            'serah_terima_path' => $stored['path'],
            'serah_terima_size' => $stored['size'],
            'serah_terima_mime' => $stored['mime'],
        ]);

        return back()->with('status', 'BA serah terima berhasil diupload.');
    }

    public function monitoringPpBulkDelete(Request $request): RedirectResponse
    {
        abort_unless($request->user()->canPermanentDeleteIctRequest(), 403);

        $validated = $request->validate([
            'request_ids' => ['required', 'array', 'min:1'],
            'request_ids.*' => ['integer'],
        ]);

        $requests = IctRequest::query()
            ->with(['items', 'ppnkDocuments', 'ppmDocuments', 'poDocuments', 'assetHandovers'])
            ->whereIn('id', array_unique(array_map('intval', $validated['request_ids'])))
            ->get();

        foreach ($requests as $ictRequest) {
            $this->deleteMonitoringPpRequest($ictRequest);
        }

        return back()->with('status', 'Data Monitoring PP terpilih berhasil dihapus.');
    }

    public function monitoringPpExportExcel(Request $request): BinaryFileResponse
    {
        $validated = $request->validate([
            'unit_id' => ['nullable', 'exists:units,id'],
            'from' => ['nullable', 'date'],
            'until' => ['nullable', 'date'],
        ]);

        [, $selectedUnitId] = $this->resolveMonitoringPpScope($request, $validated['unit_id'] ?? null);
        $hyperlinks = [];

        $rows = $this->monitoringPpQuery($selectedUnitId, $validated)
            ->select([
                'ict_request_items.photo_path',
                'units.name as unit_name',
                'ict_request_items.item_category',
                'ict_request_items.item_name',
                'ict_request_items.brand_type',
                'ict_request_items.quantity',
                'ict_request_items.estimated_price',
                'ict_request_items.notes',
                'ict_request_items.photo_name',
                'ict_request_items.pr_number',
                'ict_request_items.ppnk_number as imported_ppnk_number',
                'ict_request_items.ppnk_uploaded_at as imported_ppnk_uploaded_at',
                'ict_request_items.ppm_name as imported_ppm_name',
                'ict_request_items.ppm_number as imported_ppm_number',
                'ict_request_items.ppm_uploaded_at as imported_ppm_uploaded_at',
                'ict_request_items.po_number as imported_po_number',
                'ict_request_items.po_uploaded_at as imported_po_uploaded_at',
                'ict_requests.id as ict_request_id',
                'ict_requests.form_number',
                'ict_requests.status',
                'ict_requests.print_count',
                'ict_requests.final_signed_pdf_name',
                'ict_requests.final_signed_pdf_path',
                'ict_requests.created_at as form_created_at',
                'ict_request_ppnk_documents.attachment_name as ppnk_attachment_name',
                'ict_request_ppnk_documents.attachment_path as ppnk_attachment_path',
                'ict_request_ppnk_documents.ppnk_number',
                'ict_request_ppnk_documents.uploaded_at as ppnk_uploaded_at',
                'ict_request_ppm_documents.attachment_name as ppm_attachment_name',
                'ict_request_ppm_documents.attachment_path as ppm_attachment_path',
                'ict_request_ppm_documents.ppm_number',
                'ict_request_ppm_documents.uploaded_at as ppm_uploaded_at',
                'ict_request_po_documents.attachment_name as po_attachment_name',
                'ict_request_po_documents.attachment_path as po_attachment_path',
                'ict_request_po_documents.po_number',
                'ict_request_po_documents.uploaded_at as po_uploaded_at',
                'asset_handovers.created_at as received_at',
                'asset_handovers.id as asset_handover_id',
                'asset_handovers.serah_terima_name',
                'asset_handovers.serah_terima_path',
                'asset_handovers.handover_report_path',
                'asset_handovers.handover_report_generated_at',
                'asset_handovers.handover_report_name',
                'asset_handovers.model_specification',
                'asset_handovers.serial_number',
                'asset_handovers.asset_number',
                'asset_handovers.recipient_name',
                'asset_handovers.recipient_position',
                'asset_handovers.supervisor_name',
                'asset_handovers.supervisor_position',
            ])
            ->orderByDesc('ict_request_items.id')
            ->get()
            ->flatMap(function ($row) {
                return collect(range(1, max((int) ($row->quantity ?? 1), 1)))->map(fn () => clone $row);
            })
            ->values()
            ->map(function ($row, $index) use (&$hyperlinks) {
                $unitPrice = (float) ($row->estimated_price ?? 0);
                $excelRow = $index + 2;
                $photoUrl = $row->photo_path ? Storage::disk('public')->url($row->photo_path) : '';
                $formUrl = $row->final_signed_pdf_path ? Storage::disk('public')->url($row->final_signed_pdf_path) : '';
                $ppnkUrl = $row->ppnk_attachment_path ? Storage::disk('public')->url($row->ppnk_attachment_path) : '';
                $ppmUrl = $row->ppm_attachment_path ? Storage::disk('public')->url($row->ppm_attachment_path) : '';
                $poUrl = $row->po_attachment_path ? Storage::disk('public')->url($row->po_attachment_path) : '';
                $baUrl = $row->serah_terima_path ? Storage::disk('public')->url($row->serah_terima_path) : '';
                $ppnkNumber = $row->ppnk_number ?: $row->imported_ppnk_number;
                $ppnkUploadedAt = $row->ppnk_uploaded_at ?: $row->imported_ppnk_uploaded_at;
                $ppmName = $row->imported_ppm_name ?: $row->ppm_attachment_name;
                $ppmNumber = $row->ppm_number ?: $row->imported_ppm_number;
                $ppmUploadedAt = $row->ppm_uploaded_at ?: $row->imported_ppm_uploaded_at;
                $poNumber = $row->po_number ?: $row->imported_po_number;
                $poUploadedAt = $row->po_uploaded_at ?: $row->imported_po_uploaded_at;

                $hyperlinks[$excelRow] = array_filter([
                    'I' => $photoUrl,
                    'K' => $formUrl,
                    'M' => $ppnkUrl,
                    'P' => $ppmUrl,
                    'U' => $poUrl,
                    'Y' => $baUrl,
                ]);

                return [
                    $index + 1,
                    $row->unit_name ?: '-',
                    $row->item_category ?: '-',
                    $row->item_name ?: '-',
                    $row->brand_type ?: '-',
                    $this->formatCurrency($unitPrice),
                    $this->formatCurrency($unitPrice),
                    $row->notes ?: '-',
                    $photoUrl ? ($row->photo_name ?: 'Lihat Foto') : '-',
                    $this->formatDate($row->form_created_at),
                    $formUrl ? ($row->final_signed_pdf_name ?: $row->form_number ?: 'Lihat Form') : ($row->form_number ?: '-'),
                    $this->formatDate($ppnkUploadedAt),
                    $ppnkUrl ? ($row->ppnk_attachment_name ?: 'Lihat Berkas') : ($row->ppnk_attachment_name ?: '-'),
                    $ppnkNumber ?: '-',
                    $this->formatDate($ppmUploadedAt),
                    $ppmUrl ? ($row->ppm_attachment_name ?: 'Lihat Berkas') : ($row->ppm_attachment_name ?: '-'),
                    $ppmName ?: '-',
                    $ppmNumber ?: '-',
                    $row->pr_number ?: '-',
                    $this->formatDate($poUploadedAt),
                    $poUrl ? ($row->po_attachment_name ?: 'Lihat Berkas') : ($row->po_attachment_name ?: '-'),
                    $poNumber ?: '-',
                    $this->formatDate($row->received_at),
                    $this->formatDate($row->handover_report_generated_at),
                    $baUrl ? ($row->serah_terima_name ?: 'Lihat BA') : ($row->handover_report_name ?: '-'),
                    $row->model_specification ?: '-',
                    $row->serial_number ?: '-',
                    $row->asset_number ?: '-',
                    $row->recipient_name ?: '-',
                    $row->recipient_position ?: '-',
                    $row->supervisor_name ?: '-',
                    $row->supervisor_position ?: '-',
                    $this->formatIctRequestStatusLabel($row->status, (int) $row->print_count, $row->final_signed_pdf_path),
                ];
            })
            ->all();

        return Excel::download(
            new MonitoringPpExcelExport($this->monitoringPpExportHeadings(), $rows, $hyperlinks),
            'monitoring-pp-'.now()->format('Ymd-His').'.xlsx'
        );
    }

    public function monitoringPpExampleExcel(): BinaryFileResponse
    {
        return Excel::download(
            new MonitoringPpExcelExport($this->monitoringPpImportHeadings(), [$this->monitoringPpExampleRow()]),
            'example-import-monitoring-pp.xlsx'
        );
    }

    public function monitoringPpImportExcel(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isIctAdmin() || $request->user()->isSuperAdmin(), 403);

        $validated = $request->validate([
            'import_file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $import = new MonitoringPpImport();
        Excel::import($import, $validated['import_file']);

        if ($import->rows->isEmpty()) {
            return back()->withErrors(['import_file' => 'File import kosong atau tidak memiliki baris data.']);
        }

        DB::transaction(function () use ($import, $request) {
            $groupedRows = $import->rows
                ->map(fn ($row) => collect($row)->map(fn ($value) => is_string($value) ? trim($value) : $value))
                ->groupBy(fn ($row, $index) => (string) ($this->rowValue($row, 'form_ict', 'formict') ?: 'IMPORT-ROW-'.($index + 1)));

            foreach ($groupedRows as $formNumber => $rows) {
                $firstRow = $rows->first();
                $unit = $request->user()->unit()->firstOrFail();
                $requester = $request->user();
                $formDate = $this->parseImportDate($this->rowValue($firstRow, 'tanggal_form_ict', 'tanggal_formict')) ?? now();
                $requestCategory = $this->normalizeRequestCategory($this->rowValue($firstRow, 'jenis_barang', 'jenisbarang'));
                $priority = 'normal';
                $status = $this->normalizeImportedStatus($this->rowValue($firstRow, 'status'));
                $firstItemName = (string) ($this->rowValue($firstRow, 'nama_barang', 'namabarang') ?: 'Item');

                $formIdentifier = $this->buildMonitoringPpFormIdentifier(
                    (string) ($unit->code ?? 'UNIT'),
                    $this->resolveNextMonitoringPpFormSequence((int) $unit->id)
                );

                $ictRequest = IctRequest::create([
                    'unit_id' => $unit->id,
                    'requester_id' => $requester->id,
                    'form_number' => $formIdentifier,
                    'subject' => $formIdentifier,
                    'request_category' => $requestCategory,
                    'priority' => $priority,
                    'status' => $status,
                    'needed_at' => $formDate->toDateString(),
                    'quotation_mode' => 'global',
                    'is_pta_request' => false,
                    'justification' => 'Imported from Monitoring PP',
                    'created_at' => $formDate,
                    'updated_at' => $formDate,
                ]);

                $groupedItems = $rows->values()->groupBy(function ($row) {
                    return Str::lower(trim((string) ($this->rowValue($row, 'nama_barang', 'namabarang') ?: 'item')));
                });

                foreach ($groupedItems->values() as $lineIndex => $itemRows) {
                    $row = $itemRows->first();
                    $itemCreatedAt = $this->parseImportDate($this->rowValue($row, 'tanggal_form_ict', 'tanggal_formict')) ?? $formDate;
                    $item = IctRequestItem::create([
                        'ict_request_id' => $ictRequest->id,
                        'line_number' => $lineIndex + 1,
                        'item_name' => (string) ($this->rowValue($row, 'nama_barang', 'namabarang') ?: 'Item'),
                        'item_category' => (string) ($this->rowValue($row, 'jenis_barang', 'jenisbarang') ?: $requestCategory),
                        'brand_type' => $this->nullableString($this->rowValue($row, 'merk')),
                        'quantity' => max($itemRows->count(), 1),
                        'estimated_price' => $this->parseImportMoney($this->rowValue($row, 'harga')),
                        'notes' => $this->nullableString($this->rowValue($row, 'keterangan')),
                        'created_at' => $itemCreatedAt,
                        'updated_at' => $itemCreatedAt,
                        'ppnk_document_id' => null,
                        'ppnk_uploaded_at' => $this->parseImportDate($this->rowValue($row, 'tanggal_ppnk_ppk', 'tanggal_ppnkppk')),
                        'ppnk_number' => $this->nullableString($this->rowValue($row, 'no_ppnk_ppk', 'no_ppnkppk')),
                        'ppm_document_id' => null,
                        'ppm_uploaded_at' => $this->parseImportDate($this->rowValue($row, 'tanggal_ppm_pr', 'tanggal_ppmpr')),
                        'ppm_name' => $this->nullableString($this->rowValue($row, 'nama_ppm', 'namappm')),
                        'ppm_number' => $this->nullableString($this->rowValue($row, 'no_ppm', 'noppm')),
                        'po_document_id' => null,
                        'po_uploaded_at' => $this->parseImportDate($this->rowValue($row, 'tanggal_po', 'tanggalpo')),
                        'po_number' => $this->nullableString($this->rowValue($row, 'no_po', 'nopo')),
                    ]);

                    $item->pr_number = $this->nullableString($this->rowValue($row, 'no_pr', 'nopr'));
                    $item->save();

                    $this->createImportedAssetAndHandover($ictRequest, $item, $request->user(), $row);
                }
            }
        });

        return back()->with('status', 'Import Monitoring PP berhasil diproses. Data otomatis masuk ke Permintaan ICT, Asset, dan BA Serah Terima.');
    }

    public function index(Request $request): View
    {
        $module = $request->string('module')->toString() ?: 'ict_requests';
        $status = $request->string('status')->toString();

        [$query, $headings, $rows] = $this->buildReport($request, $module, $status);

        return view('reports.index', [
            'module' => $module,
            'status' => $status,
            'rows' => $rows,
            'headings' => $headings,
            'summary' => [
                'total' => $query->count(),
                'moduleLabel' => str($module)->replace('_', ' ')->title(),
            ],
        ]);
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $module = $request->string('module')->toString() ?: 'ict_requests';
        $status = $request->string('status')->toString();

        [, $headings, $rows] = $this->buildReport($request, $module, $status);

        return Excel::download(
            new ModuleReportExport($headings, $rows->all()),
            'report-'.Str::slug($module).'.xlsx'
        );
    }

    public function exportPdf(Request $request): Response
    {
        $module = $request->string('module')->toString() ?: 'ict_requests';
        $status = $request->string('status')->toString();

        [, $headings, $rows] = $this->buildReport($request, $module, $status);

        return Pdf::loadView('reports.pdf', [
            'title' => 'Report '.str($module)->replace('_', ' ')->title(),
            'headings' => $headings,
            'rows' => $rows,
            'generatedAt' => now(),
        ])->download('report-'.Str::slug($module).'.pdf');
    }

    /**
     * @return array{0: Builder, 1: array<int, string>, 2: Collection<int, array<int, string|null>>}
     */
    protected function buildReport(Request $request, string $module, string $status): array
    {
        $user = $request->user();
        $from = $request->date('from');
        $until = $request->date('until');

        [$query, $headings, $map] = match ($module) {
            'email_requests' => [
                UnitScope::apply(EmailRequest::query()->with(['unit', 'requester']), $user),
                ['Tanggal', 'Pemohon', 'Unit', 'Email', 'Akses', 'Status'],
                fn (EmailRequest $row) => [
                    optional($row->created_at)->format('Y-m-d H:i'),
                    $row->employee_name,
                    $row->unit?->name,
                    $row->requested_email,
                    strtoupper($row->access_level),
                    strtoupper($row->status),
                ],
            ],
            'repair_requests' => [
                UnitScope::apply(RepairRequest::query()->with(['unit', 'requester', 'asset']), $user),
                ['Tanggal', 'Pemohon', 'Unit', 'Ringkasan', 'Prioritas', 'Status'],
                fn (RepairRequest $row) => [
                    optional($row->created_at)->format('Y-m-d H:i'),
                    $row->requester?->name,
                    $row->unit?->name,
                    $row->problem_summary,
                    strtoupper($row->priority),
                    strtoupper($row->status),
                ],
            ],
            'incident_reports' => [
                UnitScope::apply(IncidentReport::query()->with(['unit', 'reporter']), $user),
                ['Tanggal', 'Pelapor', 'Unit', 'Jenis', 'Judul', 'Status'],
                fn (IncidentReport $row) => [
                    optional($row->occurred_at)->format('Y-m-d H:i'),
                    $row->reporter?->name,
                    $row->unit?->name,
                    strtoupper($row->incident_type),
                    $row->title,
                    strtoupper($row->status),
                ],
            ],
            'project_requests' => [
                UnitScope::apply(ProjectRequest::query()->with(['unit', 'requester']), $user),
                ['Tanggal', 'Pemohon', 'Unit', 'Project', 'Prioritas', 'Status'],
                fn (ProjectRequest $row) => [
                    optional($row->created_at)->format('Y-m-d H:i'),
                    $row->requester?->name,
                    $row->unit?->name,
                    $row->title,
                    strtoupper($row->priority),
                    strtoupper($row->status),
                ],
            ],
            default => [
                UnitScope::apply(IctRequest::query()->with(['unit', 'requester']), $user),
                ['Tanggal', 'Pemohon', 'Unit', 'Subjek', 'Prioritas', 'Status'],
                fn (IctRequest $row) => [
                    optional($row->created_at)->format('Y-m-d H:i'),
                    $row->requester?->name,
                    $row->unit?->name,
                    $row->subject,
                    strtoupper($row->priority),
                    strtoupper($row->status),
                ],
            ],
        };

        $query
            ->when($status !== '', fn ($builder) => $builder->where('status', $status))
            ->when($from, fn ($builder) => $builder->whereDate('created_at', '>=', $from))
            ->when($until, fn ($builder) => $builder->whereDate('created_at', '<=', $until));

        $rows = $query->latest()->get()->map($map);

        return [$query, $headings, $rows];
    }

    protected function resolveMonitoringPpScope(Request $request, ?string $requestedUnitId): array
    {
        $user = $request->user();
        $canFilterAllUnits = $user->isSuperAdmin() || $user->isAsmenIct() || $user->isManagerIct();
        $selectedUnitId = $canFilterAllUnits ? $requestedUnitId : $user->unit_id;

        return [$canFilterAllUnits, $selectedUnitId];
    }

    protected function monitoringPpQuery(?string $selectedUnitId, array $filters)
    {
        return DB::table('ict_request_items')
            ->join('ict_requests', 'ict_requests.id', '=', 'ict_request_items.ict_request_id')
            ->join('units', 'units.id', '=', 'ict_requests.unit_id')
            ->leftJoin('ict_request_ppnk_documents', 'ict_request_ppnk_documents.id', '=', 'ict_request_items.ppnk_document_id')
            ->leftJoin('ict_request_ppm_documents', 'ict_request_ppm_documents.id', '=', 'ict_request_items.ppm_document_id')
            ->leftJoin('ict_request_po_documents', 'ict_request_po_documents.id', '=', 'ict_request_items.po_document_id')
            ->leftJoin('asset_handovers', 'asset_handovers.ict_request_item_id', '=', 'ict_request_items.id')
            ->when($selectedUnitId, fn ($query) => $query->where('ict_requests.unit_id', $selectedUnitId))
            ->when($filters['from'] ?? null, fn ($query, $from) => $query->whereDate('ict_requests.created_at', '>=', $from))
            ->when($filters['until'] ?? null, fn ($query, $until) => $query->whereDate('ict_requests.created_at', '<=', $until));
    }

    protected function monitoringPpExportHeadings(): array
    {
        return [
            'No',
            'Unit',
            'Jenis Barang',
            'Nama Barang',
            'Merk',
            'Harga',
            'Total',
            'Keterangan',
            'Gambar Barang',
            'Tanggal Form ICT',
            'Form ICT',
            'Tanggal PPNK/PPK',
            'Berkas PPNK/PPK',
            'No PPNK/PPK',
            'Tanggal PPM/PR',
            'Berkas PPM',
            'Nama PPM',
            'No PPM',
            'No PR',
            'Tanggal PO',
            'Berkas PO',
            'No PO',
            'Tanggal Diterima',
            'Tanggal Pembuatan BA',
            'BA Serah Terima',
            'Model/Spesifikasi',
            'Serial Number',
            'No Asset',
            'Pemakai',
            'Jabatan',
            'Atasan Pemakai',
            'Jabatan Atasan',
            'Status',
        ];
    }

    protected function monitoringPpImportHeadings(): array
    {
        return [
            'No',
            'Jenis Barang',
            'Nama Barang',
            'Merk',
            'Harga',
            'Keterangan',
            'Tanggal Form ICT',
            'Form ICT',
            'Tanggal PPNK/PPK',
            'No PPNK/PPK',
            'Tanggal PPM/PR',
            'Nama PPM',
            'No PPM',
            'No PR',
            'Tanggal PO',
            'No PO',
            'Tanggal Diterima',
            'Tanggal Pembuatan BA',
            'Model/Spesifikasi',
            'Serial Number',
            'No Asset',
            'Pemakai',
            'Jabatan',
            'Atasan Pemakai',
            'Jabatan Atasan',
            'Nama HRGA',
            'Jabatan HRGA',
            'Status',
            'Contoh Status ICT',
        ];
    }

    protected function monitoringPpExampleRow(): array
    {
        return [
            1,
            'Laptop/Notebook',
            'Notebook Corei5-Gen12 (3.3 Ghz Up to 4.8 Ghz Cache 18MB), RAM 16GB, SSD 512GB, VGA Shared, Layar 14/15, Non OS, Garansi > 1 Thn',
            'Msi/Dell',
            'Rp15,250,000',
            'U/ Penunjang Pekerjaan Staff IT, Kasie Mill, Kasie SMGE dikarenakan saat ini menggunakan Laptop pribadi',
            '2024-08-12',
            'ICT-IMP-001',
            '2024-08-12',
            '0006/JAR/ICT/PPNK/IV/2024',
            '2024-08-12',
            '081224-PP-JAR-0062-VIII-2024 (PAGE 6)',
            '176',
            '3000046402',
            '2024-08-23',
            '5000985653',
            '2024-10-11',
            '2024-10-11',
            'Modern 14 C7M-279ID-CBAR530U16GXXDX11EMH, Classic Black 14, FHD, Anti-Glare, IPS-level, 60hz AMD Ryzen 5 7530U Processor AMD Radeon Graphics 512GB SSD DDR4 16GB Windows 11 Home',
            'K2407N0102616',
            '370000252',
            'Terry',
            'Kasie Mill',
            'Dedi Irawan',
            'Manager Mill',
            'Fitri Rahma',
            'Asmen GA',
            'completed',
            'drafted / ttd_in_progress / checked_by_asmen / progress_ppnk / progress_verifikasi_audit / progress_ppm / progress_po / progress_waiting_goods / completed / needs_revision / rejected',
        ];
    }

    protected function normalizeRequestCategory(mixed $value): string
    {
        $normalized = Str::of((string) $value)->lower()->snake()->toString();

        return in_array($normalized, ['hardware', 'software', 'accessories', 'peripheral', 'network'], true)
            ? $normalized
            : 'hardware';
    }

    protected function normalizePriority(mixed $value): string
    {
        $normalized = Str::of((string) $value)->lower()->trim()->toString();

        return in_array($normalized, ['low', 'normal', 'high', 'urgent'], true)
            ? $normalized
            : 'normal';
    }

    protected function normalizeImportedStatus(mixed $value): string
    {
        $normalized = Str::of((string) $value)->lower()->snake()->toString();

        $map = [
            'drafted' => 'drafted',
            'ttd_in_progress' => 'ttd_in_progress',
            'checked_by_asmen' => 'checked_by_asmen',
            'progress_ppnk' => 'progress_ppnk',
            'progress_verifikasi_audit' => 'progress_verifikasi_audit',
            'progress_ppm' => 'progress_ppm',
            'progress_po' => 'progress_po',
            'progress_waiting_goods' => 'progress_waiting_goods',
            'approved_by_manager' => 'approved_by_manager',
            'completed' => 'completed',
            'barang_sudah_diterima' => 'completed',
            'needs_revision' => 'needs_revision',
            'rejected' => 'rejected',
        ];

        return $map[$normalized] ?? 'completed';
    }

    protected function resolveUniqueFormNumber(string $formNumber): string
    {
        $candidate = trim($formNumber) !== '' ? trim($formNumber) : 'ICT-IMP-'.now()->format('YmdHis');

        if (! IctRequest::query()->where('form_number', $candidate)->exists()) {
            return $candidate;
        }

        return $candidate.'-'.Str::upper(Str::random(4));
    }

    protected function buildImportSubject(string $category, string $itemName): string
    {
        $categoryLabel = match ($category) {
            'software' => 'Software',
            'accessories' => 'Accessories',
            'peripheral' => 'Peripheral',
            'network' => 'Network',
            default => 'Hardware',
        };

        return Str::limit(trim($categoryLabel.' - '.$itemName), 255, '');
    }

    protected function buildMonitoringPpFormIdentifier(string $unitCode, int $sequence): string
    {
        $normalizedUnitCode = Str::upper(trim($unitCode)) !== '' ? Str::upper(trim($unitCode)) : 'UNIT';

        return sprintf('%s-FORM ICT-%03d', $normalizedUnitCode, $sequence);
    }

    protected function resolveNextMonitoringPpFormSequence(int $unitId): int
    {
        $usedSequences = IctRequest::query()
            ->where('unit_id', $unitId)
            ->pluck('form_number')
            ->map(fn ($formNumber) => $this->extractMonitoringPpFormSequence((string) $formNumber))
            ->filter(fn ($sequence) => $sequence !== null)
            ->sort()
            ->values();

        $expectedSequence = 1;

        foreach ($usedSequences as $sequence) {
            if ($sequence > $expectedSequence) {
                break;
            }

            if ($sequence === $expectedSequence) {
                $expectedSequence++;
            }
        }

        return $expectedSequence;
    }

    protected function extractMonitoringPpFormSequence(string $formNumber): ?int
    {
        if (! preg_match('/FORM ICT-(\d+)$/', Str::upper(trim($formNumber)), $matches)) {
            return null;
        }

        return (int) $matches[1];
    }

    protected function parseImportDate(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value));
        }

        $normalized = Str::of((string) $value)
            ->replace(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'], ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'])
            ->replace(['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'], ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'])
            ->toString();

        return Carbon::parse($normalized);
    }

    protected function parseImportMoney(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $digitsOnly = preg_replace('/[^\d-]/', '', (string) $value) ?? '';

        return is_numeric($digitsOnly) ? (float) $digitsOnly : null;
    }

    protected function nullableString(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;

        return $value === null || $value === '' ? null : (string) $value;
    }

    protected function createImportedAssetAndHandover(IctRequest $ictRequest, IctRequestItem $item, User $user, \Illuminate\Support\Collection $row): void
    {
        $receivedAt = $this->parseImportDate($this->rowValue($row, 'tanggal_diterima', 'tanggalditerima')) ?? now();
        $reportGeneratedAt = $this->parseImportDate($this->rowValue($row, 'tanggal_pembuatan_ba', 'tanggal_pembuatanba')) ?? $receivedAt;
        $modelSpecification = $this->nullableString($this->rowValue($row, 'model_spesifikasi', 'modelspesifikasi', 'model_spesification'));

        $asset = Asset::create([
            'unit_id' => $ictRequest->unit_id,
            'assigned_user_id' => null,
            'uuid' => Str::uuid()->toString(),
            'asset_number' => $this->nullableString($this->rowValue($row, 'no_asset', 'noasset')),
            'category' => $item->item_category ?? 'hardware',
            'name' => $item->item_name,
            'brand' => $item->brand_type,
            'model' => $modelSpecification,
            'serial_number' => $this->nullableString($this->rowValue($row, 'serial_number', 'serialnumber')),
            'specification' => $modelSpecification ? ['description' => $modelSpecification] : null,
            'location' => $ictRequest->unit?->name,
            'purchase_date' => $receivedAt->toDateString(),
            'condition_status' => 'good',
            'lifecycle_status' => 'active',
        ]);

        $handover = AssetHandover::create([
            'ict_request_id' => $ictRequest->id,
            'ict_request_item_id' => $item->id,
            'asset_id' => $asset->id,
            'handover_type' => 'asset',
            'dept' => $ictRequest->unit?->name,
            'model_specification' => $modelSpecification,
            'serial_number' => $this->nullableString($this->rowValue($row, 'serial_number', 'serialnumber')),
            'asset_number' => $this->nullableString($this->rowValue($row, 'no_asset', 'noasset')),
            'recipient_name' => $this->nullableString($this->rowValue($row, 'pemakai')),
            'recipient_position' => $this->nullableString($this->rowValue($row, 'jabatan')),
            'supervisor_name' => $this->nullableString($this->rowValue($row, 'atasan_pemakai', 'atasanpemakai')),
            'supervisor_position' => $this->nullableString($this->rowValue($row, 'jabatan_atasan', 'jabatanatasan')),
            'witness_name' => $user->name,
            'witness_position' => $user->job_title ?: $user->role?->label(),
            'deliverer_name' => $this->nullableString($this->rowValue($row, 'nama_hrga', 'namahrga')) ?: $user->name,
            'deliverer_position' => $this->nullableString($this->rowValue($row, 'jabatan_hrga', 'jabatanhrga')) ?: ($user->job_title ?: $user->role?->label()),
            'created_by' => $user->id,
            'created_at' => $receivedAt,
            'updated_at' => $receivedAt,
        ]);

        $pdf = Pdf::loadView('forms.ict-requests.handover-report', [
            'handover' => $handover,
            'ictRequest' => $ictRequest,
            'item' => $item,
        ])->setPaper('a4', 'portrait');

        $fileName = 'berita-acara-'.$handover->id.'-'.Str::slug(substr((string) $item->item_name, 0, 30)).'.pdf';
        $filePath = 'ict-handover-reports/'.$fileName;

        Storage::disk('public')->put($filePath, $pdf->output());

        $handover->update([
            'handover_report_path' => $filePath,
            'handover_report_name' => $fileName,
            'handover_report_generated_at' => $reportGeneratedAt,
        ]);
    }

    protected function canAccessMonitoringRequest(Request $request, IctRequest $ictRequest): bool
    {
        [$canFilterAllUnits, $selectedUnitId] = $this->resolveMonitoringPpScope($request, null);

        return $canFilterAllUnits || (string) $ictRequest->unit_id === (string) $selectedUnitId;
    }

    protected function deleteMonitoringPpRequest(IctRequest $ictRequest): void
    {
        foreach ($ictRequest->items as $item) {
            if ($item->photo_path) {
                Storage::disk('public')->delete($item->photo_path);
            }
        }

        foreach ($ictRequest->ppnkDocuments as $document) {
            if ($document->attachment_path) {
                Storage::disk('public')->delete($document->attachment_path);
            }
        }

        foreach ($ictRequest->ppmDocuments as $document) {
            if ($document->attachment_path) {
                Storage::disk('public')->delete($document->attachment_path);
            }
        }

        foreach ($ictRequest->poDocuments as $document) {
            if ($document->attachment_path) {
                Storage::disk('public')->delete($document->attachment_path);
            }
        }

        foreach ($ictRequest->assetHandovers as $handover) {
            foreach ([
                $handover->serah_terima_path,
                $handover->surat_jalan_path,
                $handover->handover_report_path,
            ] as $path) {
                if ($path) {
                    Storage::disk('public')->delete($path);
                }
            }

            if ($handover->asset_id) {
                Asset::query()->whereKey($handover->asset_id)->delete();
            }
        }

        if ($ictRequest->final_signed_pdf_path) {
            Storage::disk('public')->delete($ictRequest->final_signed_pdf_path);
        }

        if ($ictRequest->revision_attachment_path) {
            Storage::disk('public')->delete($ictRequest->revision_attachment_path);
        }

        $ictRequest->assetHandovers()->delete();
        $ictRequest->items()->delete();
        $ictRequest->ppnkDocuments()->delete();
        $ictRequest->ppmDocuments()->delete();
        $ictRequest->poDocuments()->delete();
        $ictRequest->quotations()->delete();
        $ictRequest->reviewHistories()->delete();
        $ictRequest->delete();
    }

    protected function renderMonitoringUploadTrigger(string $label, string $type, string $action, array $payload = []): string
    {
        $attributes = [
            'type' => 'button',
            'class' => 'font-medium text-brand-700 hover:text-brand-800 hover:underline',
            'data-monitoring-upload' => 'true',
            'data-upload-type' => $type,
            'data-upload-action' => $action,
        ];

        foreach ($payload as $key => $value) {
            $attributes['data-'.Str::kebab($key)] = (string) $value;
        }

        $attributeString = collect($attributes)
            ->map(fn ($value, $key) => $key.'="'.e($value).'"')
            ->implode(' ');

        return '<button '.$attributeString.'>'.e($label).'</button>';
    }

    protected function rowValue(Collection $row, string ...$keys): mixed
    {
        foreach ($keys as $key) {
            if ($row->has($key)) {
                return $row->get($key);
            }
        }

        return null;
    }

    protected function formatDate($value): string
    {
        return $value ? \Illuminate\Support\Carbon::parse($value)->format('d M Y') : '-';
    }

    protected function formatCurrency($value): string
    {
        return $value !== null ? 'Rp '.number_format((float) $value, 0, ',', '.') : '-';
    }

    protected function formatIctRequestStatusLabel(?string $status, int $printCount = 0, ?string $finalSignedPdfPath = null): string
    {
        if ($status === 'checked_by_asmen' && $printCount > 0 && ! $finalSignedPdfPath) {
            return 'Progress TTD';
        }

        return IctRequest::STATUS_LABELS[$status]
            ?? str((string) $status)->replace('_', ' ')->title()->toString();
    }

    protected function renderStatusBadge(?string $status, string $label): string
    {
        $classes = match ($status) {
            'drafted' => 'bg-slate-100 text-slate-700 ring-slate-200',
            'ttd_in_progress', 'checked_by_asmen' => 'bg-sky-100 text-sky-700 ring-sky-200',
            'progress_ppnk', 'progress_verifikasi_audit', 'progress_ppm', 'progress_po', 'progress_waiting_goods' => 'bg-amber-100 text-amber-800 ring-amber-200',
            'approved_by_manager', 'completed' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
            'needs_revision' => 'bg-orange-100 text-orange-700 ring-orange-200',
            'rejected' => 'bg-rose-100 text-rose-700 ring-rose-200',
            default => 'bg-ink-100 text-ink-700 ring-ink-200',
        };

        return '<span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold ring-1 '.$classes.'">'.e($label).'</span>';
    }
}
