<?php

namespace App\Http\Controllers\Form;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetHandover;
use App\Models\IctRequest;
use App\Support\PublicFileUpload;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
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

        $search = $request->string('search')->toString();
        $perPage = max(10, (int) $request->input('per_page', 20));

        $handovers = AssetHandover::query()
            ->select('asset_handovers.*')
            ->join('ict_requests', 'ict_requests.id', '=', 'asset_handovers.ict_request_id')
            ->with(['ictRequest.unit', 'ictRequestItem', 'asset', 'creator'])
            ->when(! $request->user()->isSuperAdmin(), fn (Builder $q) => $q->where('ict_requests.unit_id', $request->user()->unit_id))
            ->when($search !== '', function (Builder $q) use ($search): void {
                $q->where(function (Builder $inner) use ($search): void {
                    $inner
                        ->where('ict_requests.subject', 'like', "%{$search}%")
                        ->orWhere('asset_handovers.asset_number', 'like', "%{$search}%")
                        ->orWhere('asset_handovers.serial_number', 'like', "%{$search}%")
                        ->orWhere('asset_handovers.recipient_name', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('asset_handovers.id')
            ->paginate($perPage)
            ->withQueryString();

        return view('forms.asset-handovers.index', [
            'handovers' => $handovers,
            'perPage' => $perPage,
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

        abort_unless(
            $request->user()->isAsmenIct() || $request->user()->isManagerIct() || $request->user()->isSuperAdmin() || $assetHandover->ictRequest?->unit_id === $request->user()->unit_id,
            403
        );

        abort_unless($assetHandover->ictRequest && $assetHandover->ictRequestItem, 404);

        $pdf = Pdf::loadView('forms.ict-requests.handover-report', [
            'handover' => $assetHandover,
            'ictRequest' => $assetHandover->ictRequest,
            'item' => $assetHandover->ictRequestItem,
        ])->setPaper('a4', 'portrait');

        $fileName = $assetHandover->handover_report_name ?: 'serah-terima-fasilitas-ict-'.$assetHandover->id.'.pdf';

        return $pdf->stream($fileName);
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
}
