<?php

namespace App\Http\Controllers;

use App\Models\EmailRequest;
use App\Models\IctRequest;
use App\Models\IctRequestReviewHistory;
use App\Support\PublicFileUpload;
use App\Support\UnitScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        abort_unless($user->canProcessApprovals(), 403);

        $ictRequests = UnitScope::apply(
            IctRequest::query()
                ->select(['id', 'unit_id', 'requester_id', 'subject', 'status', 'created_at', 'final_signed_pdf_name', 'final_signed_pdf_path'])
                ->with([
                    'requester:id,name',
                    'unit:id,name',
                    'reviewHistories' => fn ($query) => $query
                        ->select([
                            'id',
                            'ict_request_id',
                            'action',
                            'note',
                            'attachment_name',
                            'attachment_path',
                            'attachment_mime',
                            'reviewed_by',
                            'reviewed_at',
                        ])
                        ->with('reviewer:id,name'),
                ])
                ->whereIn('status', $this->pendingIctStatuses($user))
                ->latest(),
            $user
        )->simplePaginate(10, ['*'], 'ict_page')->withQueryString();

        $emailRequests = UnitScope::apply(
            EmailRequest::query()
                ->select(['id', 'unit_id', 'requester_id', 'employee_name', 'requested_email', 'status', 'created_at'])
                ->with(['requester:id,name', 'unit:id,name'])
                ->where('status', $this->pendingEmailStatus($user))
                ->latest(),
            $user
        )->simplePaginate(10, ['*'], 'email_page')->withQueryString();

        return view('approvals.index', compact('ictRequests', 'emailRequests'));
    }

    public function updateIct(Request $request, IctRequest $ictRequest): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:approve,reject,revise,upload_signed_pdf'],
            'signed_pdf' => ['nullable', 'required_if:action,upload_signed_pdf', 'file', 'mimes:pdf', 'max:10240'],
            'review_note' => ['nullable', 'string'],
            'revision_attachment' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $user = $request->user();

        if ($validated['action'] === 'reject') {
            $request->validate([
                'review_note' => ['required', 'string'],
            ]);
        }

        if ($validated['action'] === 'revise') {
            $request->validate([
                'review_note' => ['nullable', 'required_without:revision_attachment', 'string'],
                'revision_attachment' => ['nullable', 'required_without:review_note', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            ]);
        }

        if ($validated['action'] === 'reject') {
            abort_unless($this->canRejectIct($user, $ictRequest), 403);

            $this->deleteRevisionAttachment($ictRequest);

            $ictRequest->update([
                'status' => 'rejected',
                'rejected_reason' => trim((string) $request->input('review_note')),
                'rejected_by' => $user->id,
                'rejected_at' => now(),
                'revision_note' => null,
                'revision_attachment_name' => null,
                'revision_attachment_path' => null,
                'revision_attachment_size' => null,
                'revision_attachment_mime' => null,
                'revision_requested_by' => null,
                'revision_requested_at' => null,
            ]);

            $this->storeReviewHistory($ictRequest, [
                'action' => 'reject',
                'note' => trim((string) $request->input('review_note')) ?: null,
                'attachment_name' => null,
                'attachment_path' => null,
                'attachment_size' => null,
                'attachment_mime' => null,
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
            ]);

            return back()->with('status', 'Permintaan ICT ditolak.');
        }

        if ($validated['action'] === 'revise') {
            abort_unless($this->canRequestRevisionIct($user, $ictRequest), 403);

            $attachment = $validated['revision_attachment'] ?? null;
            $compressedAttachment = $attachment instanceof UploadedFile
                ? $this->storeCompressedRevisionAttachment($attachment)
                : null;

            $this->deleteRevisionAttachment($ictRequest);

            $ictRequest->update([
                'status' => 'needs_revision',
                'rejected_reason' => null,
                'rejected_by' => null,
                'rejected_at' => null,
                'revision_note' => trim((string) $request->input('review_note')) ?: null,
                'revision_attachment_name' => $compressedAttachment['name'] ?? null,
                'revision_attachment_path' => $compressedAttachment['path'] ?? null,
                'revision_attachment_size' => $compressedAttachment['size'] ?? null,
                'revision_attachment_mime' => $compressedAttachment['mime'] ?? null,
                'revision_requested_by' => $user->id,
                'revision_requested_at' => now(),
            ]);

            $this->storeReviewHistory($ictRequest, [
                'action' => 'revise',
                'note' => trim((string) $request->input('review_note')) ?: null,
                'attachment_name' => $compressedAttachment['name'] ?? null,
                'attachment_path' => $compressedAttachment['path'] ?? null,
                'attachment_size' => $compressedAttachment['size'] ?? null,
                'attachment_mime' => $compressedAttachment['mime'] ?? null,
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
            ]);

            return back()->with('status', 'Permintaan ICT dikembalikan untuk revisi.');
        }

        if ($validated['action'] === 'upload_signed_pdf') {
            abort_unless($this->canUploadSignedPdf($user, $ictRequest), 403);

            $signedPdf = $validated['signed_pdf'];

            if ($signedPdf instanceof UploadedFile) {
                [
                    'name' => $signedPdfName,
                    'path' => $signedPdfPath,
                ] = $this->resolveSignedPdfStorage($signedPdf);
            } else {
                $signedPdfName = null;
                $signedPdfPath = null;
            }

            $ictRequest->update([
                'status' => 'progress_ppnk',
                'final_signed_pdf_name' => $signedPdfName,
                'final_signed_pdf_path' => $signedPdfPath,
                'final_signed_pdf_uploaded_by' => $user->id,
                'final_signed_pdf_uploaded_at' => now(),
            ]);

            return back()->with('status', 'PDF TTD lengkap berhasil diunggah. Lanjutkan upload data PPNK per barang.');
        }

        abort_unless($this->canHandleIct($user, $ictRequest), 403);

        if ($ictRequest->status === 'drafted') {
            $ictRequest->update([
                'status' => 'ttd_in_progress',
                'staff_validated_by' => $user->id,
                'staff_validated_at' => now(),
            ]);
        } elseif ($ictRequest->status === 'ttd_in_progress') {
            $ictRequest->update([
                'status' => 'checked_by_asmen',
                'asmen_checked_by' => $user->id,
                'asmen_checked_at' => now(),
            ]);
        }

        return back()->with('status', 'Approval permintaan ICT diperbarui.');
    }

    public function updateEmail(Request $request, EmailRequest $emailRequest): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:approve,reject'],
        ]);

        $user = $request->user();

        abort_unless($this->canHandleEmail($user, $emailRequest), 403);

        if ($validated['action'] === 'reject') {
            $emailRequest->update(['status' => 'rejected']);

            return back()->with('status', 'Permohonan email ditolak.');
        }

        if ($emailRequest->status === 'submitted') {
            $emailRequest->update([
                'status' => 'manager_approved',
                'manager_approved_by' => $user->id,
                'manager_approved_at' => now(),
            ]);
        } elseif ($emailRequest->status === 'manager_approved') {
            $emailRequest->update([
                'status' => 'hrga_verified',
                'hrga_verified_by' => $user->id,
                'hrga_verified_at' => now(),
            ]);
        } elseif ($emailRequest->status === 'hrga_verified') {
            $emailRequest->update([
                'status' => 'completed',
                'ict_processed_by' => $user->id,
                'ict_processed_at' => now(),
            ]);
        }

        return back()->with('status', 'Approval permohonan email diperbarui.');
    }

    protected function canHandleIct($user, IctRequest $request): bool
    {
        return match ($request->status) {
            'drafted' => $user->isStaffIct(),
            'ttd_in_progress' => $user->isAsmenIct(),
            default => false,
        };
    }

    protected function canRejectIct($user, IctRequest $request): bool
    {
        return $this->canHandleIct($user, $request) || $this->canUploadSignedPdf($user, $request);
    }

    protected function canRequestRevisionIct($user, IctRequest $request): bool
    {
        return $this->canHandleIct($user, $request) || $this->canUploadSignedPdf($user, $request);
    }

    protected function canUploadSignedPdf($user, IctRequest $request): bool
    {
        return $request->status === 'checked_by_asmen' && $user->isIctAdmin();
    }

    protected function pendingIctStatuses($user): array
    {
        return match (true) {
            $user->isSuperAdmin() => ['drafted', 'ttd_in_progress'],
            $user->isIctAdmin() => [],
            $user->isAsmenIct() => ['ttd_in_progress'],
            $user->isStaffIct() => ['drafted'],
            default => ['drafted'],
        };
    }

    protected function canHandleEmail($user, EmailRequest $request): bool
    {
        return match ($request->status) {
            'submitted' => $user->isStaffIct(),
            'manager_approved' => $user->isAsmenIct(),
            'hrga_verified' => $user->isManagerIct(),
            default => false,
        };
    }

    protected function pendingEmailStatus($user): string
    {
        return match (true) {
            $user->isManagerIct() => 'hrga_verified',
            $user->isAsmenIct() => 'manager_approved',
            default => 'submitted',
        };
    }

    protected function storeCompressedRevisionAttachment(UploadedFile $attachment): array
    {
        return PublicFileUpload::store($attachment, 'ict-request-revisions', 255, 'revision');
    }

    protected function deleteRevisionAttachment(IctRequest $ictRequest): void
    {
        if ($ictRequest->revision_attachment_path) {
            Storage::disk('public')->delete($ictRequest->revision_attachment_path);
        }
    }

    protected function storeReviewHistory(IctRequest $ictRequest, array $payload): IctRequestReviewHistory
    {
        if (! empty($payload['attachment_path']) && Storage::disk('public')->exists($payload['attachment_path'])) {
            $historyAttachment = $this->storeHistoryAttachmentCopy(
                (string) $payload['attachment_path'],
                (string) ($payload['attachment_name'] ?? ''),
                (string) ($payload['attachment_mime'] ?? 'application/octet-stream')
            );

            $payload['attachment_name'] = $historyAttachment['name'];
            $payload['attachment_path'] = $historyAttachment['path'];
            $payload['attachment_size'] = $historyAttachment['size'];
            $payload['attachment_mime'] = $historyAttachment['mime'];
        }

        return $ictRequest->reviewHistories()->create($payload);
    }

    protected function storeHistoryAttachmentCopy(string $sourcePath, string $originalName, string $mime): array
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION) ?: 'jpg';
        $historyFileName = Str::uuid()->toString().'-review-history.'.$extension;
        $historyPath = 'ict-request-review-history/'.$historyFileName;

        Storage::disk('public')->copy($sourcePath, $historyPath);

        return [
            'name' => $originalName !== '' ? $originalName : $historyFileName,
            'path' => $historyPath,
            'size' => Storage::disk('public')->size($historyPath),
            'mime' => $mime,
        ];
    }

    protected function resolveSignedPdfStorage(UploadedFile $pdf): array
    {
        $originalName = $pdf->getClientOriginalName();

        // Cek apakah PDF dengan nama yang sama sudah ada di database
        $existingPdf = IctRequest::query()
            ->where('final_signed_pdf_name', $originalName)
            ->whereNotNull('final_signed_pdf_path')
            ->latest('id')
            ->first(['final_signed_pdf_name', 'final_signed_pdf_path']);

        // Jika file sudah ada di storage dan masih referenced di database, gunakan file yang sama
        if ($existingPdf && $existingPdf->final_signed_pdf_path && Storage::disk('public')->exists($existingPdf->final_signed_pdf_path)) {
            return [
                'name' => (string) $existingPdf->final_signed_pdf_name,
                'path' => (string) $existingPdf->final_signed_pdf_path,
            ];
        }

        $storedPath = $pdf->storeAs('ict-request-signed', $originalName, 'public');

        return [
            'name' => $originalName,
            'path' => $storedPath,
        ];
    }
}
