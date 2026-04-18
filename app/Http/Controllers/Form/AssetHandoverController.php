<?php

namespace App\Http\Controllers\Form;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetHandover;
use App\Models\IctRequest;
use App\Support\PublicFileUpload;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AssetHandoverController extends Controller
{
    public function index(Request $request): View
    {
        if (! $request->user()?->canAccessAssetHandovers()) {
            abort(403);
        }

        $currentYear = now()->year;
        $isFirstSemester = now()->month <= 6;
        $defaultStartDate = Carbon::create($currentYear, $isFirstSemester ? 1 : 7, 1)->startOfDay();
        $defaultEndDate = Carbon::create($currentYear, $isFirstSemester ? 6 : 12, 1)->endOfMonth()->endOfDay();

        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'until' => ['nullable', 'date', 'after_or_equal:from'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'sort' => ['nullable', 'in:created_at,subject,item_name,recipient_name,asset_number'],
            'direction' => ['nullable', 'in:asc,desc'],
            'sort_by' => ['nullable', 'in:created_at,subject,item_name,recipient_name,asset_number'],
            'sort_dir' => ['nullable', 'in:asc,desc'],
        ]);

        $fromInput = $validated['from'] ?? ($validated['start_date'] ?? null);
        $untilInput = $validated['until'] ?? ($validated['end_date'] ?? null);

        $startDate = $fromInput
            ? Carbon::parse($fromInput)->startOfDay()
            : $defaultStartDate;
        $endDate = $untilInput
            ? Carbon::parse($untilInput)->endOfDay()
            : $defaultEndDate;

        if ($endDate->lt($startDate)) {
            $endDate = (clone $startDate)->endOfDay();
        }

        $sortBy = $validated['sort'] ?? ($validated['sort_by'] ?? 'created_at');
        $sortDir = $validated['direction'] ?? ($validated['sort_dir'] ?? 'desc');

        $sortColumnMap = [
            'created_at' => 'asset_handovers.created_at',
            'subject' => 'ict_requests.subject',
            'item_name' => 'ict_request_items.item_name',
            'recipient_name' => 'asset_handovers.recipient_name',
            'asset_number' => 'asset_handovers.asset_number',
        ];

        $handovers = AssetHandover::query()
            ->select('asset_handovers.*')
            ->join('ict_requests', 'ict_requests.id', '=', 'asset_handovers.ict_request_id')
            ->leftJoin('ict_request_items', 'ict_request_items.id', '=', 'asset_handovers.ict_request_item_id')
            ->with(['ictRequest.unit', 'ictRequestItem', 'asset', 'creator'])
            ->when(! $request->user()->isSuperAdmin(), fn (Builder $q) => $q->where('ict_requests.unit_id', $request->user()->unit_id))
            ->whereBetween('asset_handovers.created_at', [$startDate, $endDate])
            ->orderBy($sortColumnMap[$sortBy], $sortDir)
            ->orderByDesc('asset_handovers.id')
            ->get();

        return view('forms.asset-handovers.index', [
            'handovers' => $handovers,
            'filters' => [
                'from' => $startDate->toDateString(),
                'until' => $endDate->toDateString(),
            ],
            'sort' => $sortBy,
            'direction' => $sortDir,
        ]);
    }

    public function create(Request $request): View
    {
        if (! $request->user()?->canAccessAssetHandovers()) {
            abort(403);
        }

        $ictRequests = IctRequest::query()
            ->select(['id', 'unit_id', 'subject', 'status', 'created_at'])
            ->with(['unit'])
            ->when(! $request->user()->isSuperAdmin(), fn (Builder $q) => $q->where('unit_id', $request->user()->unit_id))
            ->orderByDesc('id')
            ->limit(75)
            ->get();

        $selectedRequest = null;
        $items = collect();

        $selectedId = (int) $request->input('ict_request_id', 0);
        if ($selectedId > 0) {
            $selectedRequest = IctRequest::query()
                ->with('items')
                ->when(! $request->user()->isSuperAdmin(), fn (Builder $q) => $q->where('unit_id', $request->user()->unit_id))
                ->findOrFail($selectedId);

            $items = $selectedRequest->items;
        }

        return view('forms.asset-handovers.create', [
            'ictRequests' => $ictRequests,
            'selectedRequest' => $selectedRequest,
            'items' => $items,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (! $request->user()?->canAccessAssetHandovers()) {
            abort(403);
        }

        $validated = $request->validate([
            'ict_request_id' => ['required', 'integer'],
            'ict_request_item_id' => ['required', 'integer'],
            'handover_type' => ['required', 'in:asset,non_asset'],
            'description' => ['nullable', 'string', 'max:1000'],
            'serah_terima' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
            'surat_jalan' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
            'dept' => ['nullable', 'string', 'max:255'],
            'model_specification' => ['nullable', 'string', 'max:500'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'asset_number' => ['nullable', 'string', 'max:255'],
            'recipient_name' => ['nullable', 'string', 'max:255'],
            'recipient_position' => ['nullable', 'string', 'max:255'],
            'supervisor_name' => ['nullable', 'string', 'max:255'],
            'supervisor_position' => ['nullable', 'string', 'max:255'],
            'witness_name' => ['nullable', 'string', 'max:255'],
            'witness_position' => ['nullable', 'string', 'max:255'],
            'deliverer_name' => ['nullable', 'string', 'max:255'],
            'deliverer_position' => ['nullable', 'string', 'max:255'],
        ]);

        $ictRequest = IctRequest::query()
            ->with(['items', 'unit'])
            ->when(! $request->user()->isSuperAdmin(), fn (Builder $q) => $q->where('unit_id', $request->user()->unit_id))
            ->findOrFail((int) $validated['ict_request_id']);

        $item = $ictRequest->items->firstWhere('id', (int) $validated['ict_request_item_id']);
        abort_unless($item, 422);

        $handoverData = [
            'ict_request_id' => $ictRequest->id,
            'ict_request_item_id' => $item->id,
            'handover_type' => $validated['handover_type'],
            'created_by' => $request->user()->id,
        ];

        if (($validated['serah_terima'] ?? null) instanceof UploadedFile) {
            $stored = $this->storeHandoverAttachment($validated['serah_terima'], 'serah_terima');
            $handoverData['serah_terima_path'] = $stored['path'];
            $handoverData['serah_terima_name'] = $stored['name'];
            $handoverData['serah_terima_size'] = $stored['size'];
            $handoverData['serah_terima_mime'] = $stored['mime'];
        }

        if (($validated['surat_jalan'] ?? null) instanceof UploadedFile) {
            $stored = $this->storeHandoverAttachment($validated['surat_jalan'], 'surat_jalan');
            $handoverData['surat_jalan_path'] = $stored['path'];
            $handoverData['surat_jalan_name'] = $stored['name'];
            $handoverData['surat_jalan_size'] = $stored['size'];
            $handoverData['surat_jalan_mime'] = $stored['mime'];
        }

        if ($validated['handover_type'] === 'asset') {
            $handoverData = array_merge($handoverData, [
                'dept' => $validated['dept'] ?? null,
                'model_specification' => $validated['model_specification'] ?? null,
                'serial_number' => $validated['serial_number'] ?? null,
                'asset_number' => $validated['asset_number'] ?? null,
                'recipient_name' => $validated['recipient_name'] ?? null,
                'recipient_position' => $validated['recipient_position'] ?? null,
                'supervisor_name' => $validated['supervisor_name'] ?? null,
                'supervisor_position' => $validated['supervisor_position'] ?? null,
                'witness_name' => $validated['witness_name'] ?? null,
                'witness_position' => $validated['witness_position'] ?? null,
                'deliverer_name' => $validated['deliverer_name'] ?? null,
                'deliverer_position' => $validated['deliverer_position'] ?? null,
            ]);

            $asset = Asset::create([
                'unit_id' => $ictRequest->unit_id,
                'assigned_user_id' => null,
                'uuid' => Str::uuid()->toString(),
                'asset_number' => $validated['asset_number'] ?? null,
                'category' => $item->item_category ?? 'hardware',
                'name' => $item->item_name,
                'brand' => $item->brand_type,
                'model' => $validated['model_specification'] ?? null,
                'serial_number' => $validated['serial_number'] ?? null,
                'specification' => ($validated['model_specification'] ?? null) ? ['description' => $validated['model_specification']] : null,
                'location' => $validated['dept'] ?? null,
                'purchase_date' => now(),
                'condition_status' => 'good',
                'lifecycle_status' => 'active',
            ]);

            $handoverData['asset_id'] = $asset->id;
        } else {
            $handoverData['description'] = $validated['description'] ?? null;
        }

        $handover = AssetHandover::create($handoverData);

        if ($validated['handover_type'] === 'asset') {
            $this->generateHandoverReport($handover, $ictRequest, $item);
        }

        return redirect()
            ->route('forms.asset-handovers.index')
            ->with('status', 'Berita Acara Serah Terima berhasil dibuat.');
    }

    public function pdf(Request $request, AssetHandover $assetHandover): BinaryFileResponse|Response
    {
        if (! $request->user()?->canAccessAssetHandovers()) {
            abort(403);
        }

        $assetHandover->loadMissing(['ictRequest', 'ictRequestItem']);

        abort_unless($this->canAccessHandover($request, $assetHandover), 403);

        abort_unless($assetHandover->ictRequest && $assetHandover->ictRequestItem, 404);

        $pdf = Pdf::loadView('forms.ict-requests.handover-report', [
            'handover' => $assetHandover,
            'ictRequest' => $assetHandover->ictRequest,
            'item' => $assetHandover->ictRequestItem,
        ])->setPaper('a4', 'portrait');

        $fileName = $assetHandover->handover_report_name ?: 'serah-terima-fasilitas-ict-'.$assetHandover->id.'.pdf';

        return $pdf->stream($fileName);
    }

    public function uploadTtd(Request $request, AssetHandover $assetHandover): RedirectResponse
    {
        if (! $request->user()?->canAccessAssetHandovers()) {
            abort(403);
        }

        $assetHandover->loadMissing(['ictRequest']);
        abort_unless($this->canAccessHandover($request, $assetHandover), 403);

        $validated = $request->validate([
            'ttd_date' => ['required', 'date'],
            'ttd_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
            'from' => ['nullable', 'date'],
            'until' => ['nullable', 'date'],
            'sort' => ['nullable', 'in:created_at,subject,item_name,recipient_name,asset_number'],
            'direction' => ['nullable', 'in:asc,desc'],
        ]);

        $stored = PublicFileUpload::store($validated['ttd_file'], 'ict-handover-documents', 255, 'full-ttd-'.$assetHandover->id);

        $oldPath = (string) ($assetHandover->full_ttd_path ?? '');
        if ($oldPath !== '' && $oldPath !== $stored['path']) {
            Storage::disk('public')->delete($oldPath);
        }

        $assetHandover->update([
            'full_ttd_signed_at' => Carbon::parse($validated['ttd_date'])->endOfDay(),
            'full_ttd_path' => $stored['path'],
            'full_ttd_name' => $stored['name'],
            'full_ttd_size' => $stored['size'],
            'full_ttd_mime' => $stored['mime'],
        ]);

        $query = collect([
            'from' => $validated['from'] ?? null,
            'until' => $validated['until'] ?? null,
            'sort' => $validated['sort'] ?? null,
            'direction' => $validated['direction'] ?? null,
        ])->filter(fn ($value) => filled($value))->all();

        return redirect()
            ->route('forms.asset-handovers.index', $query)
            ->with('status', 'Dokumen TTD berhasil diupload. Status berubah menjadi TTD Lengkap.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        if (! $request->user()?->canAccessAssetHandovers()) {
            abort(403);
        }
        abort_unless($request->user()->isIctAdmin() || $request->user()->isSuperAdmin(), 403);

        $validated = $request->validate([
            'selected_ids' => ['required', 'array', 'min:1'],
            'selected_ids.*' => ['integer'],
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:10', 'max:100'],
        ]);

        $ids = collect($validated['selected_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return back()->with('status', 'Tidak ada data yang dipilih.');
        }

        $baseQuery = AssetHandover::query()
            ->select('asset_handovers.*')
            ->join('ict_requests', 'ict_requests.id', '=', 'asset_handovers.ict_request_id')
            ->whereIn('asset_handovers.id', $ids);

        if (! $request->user()->isSuperAdmin()) {
            $baseQuery->where('ict_requests.unit_id', $request->user()->unit_id);
        }

        $handovers = $baseQuery->with('asset')->get();

        if ($handovers->isEmpty()) {
            return back()->with('status', 'Data tidak ditemukan atau tidak punya akses untuk menghapus data ini.');
        }

        DB::transaction(function () use ($handovers): void {
            foreach ($handovers as $handover) {
                foreach (['serah_terima_path', 'full_ttd_path', 'surat_jalan_path', 'handover_report_path'] as $fileKey) {
                    $path = (string) ($handover->{$fileKey} ?? '');
                    if ($path !== '') {
                        Storage::disk('public')->delete($path);
                    }
                }

                if ($handover->asset_id) {
                    Asset::query()->whereKey($handover->asset_id)->delete();
                }

                $handover->delete();
            }
        });

        return back()->with('status', $handovers->count().' data serah terima berhasil dihapus.');
    }

    protected function generateHandoverReport(AssetHandover $handover, IctRequest $ictRequest, $item): void
    {
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
            'handover_report_generated_at' => now(),
        ]);
    }

    protected function storeHandoverAttachment(UploadedFile $file, string $prefix): array
    {
        return PublicFileUpload::storeStableOrReuse($file, 'ict-handover-documents', 255);
    }

    protected function canAccessHandover(Request $request, AssetHandover $assetHandover): bool
    {
        return $request->user()->isAsmenIct()
            || $request->user()->isManagerIct()
            || $request->user()->isSuperAdmin()
            || $assetHandover->ictRequest?->unit_id === $request->user()->unit_id;
    }
}
