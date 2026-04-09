<?php

namespace App\Http\Controllers;

use App\Models\EmailRequest;
use App\Models\IctRequest;
use App\Support\UnitScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        abort_unless($user->canProcessApprovals(), 403);

        $ictRequests = UnitScope::apply(
            IctRequest::query()->with(['requester', 'unit'])->latest(),
            $user
        )->get()->filter(fn (IctRequest $request) => $this->canHandleIct($user, $request));

        $emailRequests = UnitScope::apply(
            EmailRequest::query()->with(['requester', 'unit'])->latest(),
            $user
        )->get()->filter(fn (EmailRequest $request) => $this->canHandleEmail($user, $request));

        return view('approvals.index', compact('ictRequests', 'emailRequests'));
    }

    public function updateIct(Request $request, IctRequest $ictRequest): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:approve,reject'],
        ]);

        $user = $request->user();

        abort_unless($this->canHandleIct($user, $ictRequest), 403);

        if ($validated['action'] === 'reject') {
            $ictRequest->update(['status' => 'rejected']);

            return back()->with('status', 'Permintaan ICT ditolak.');
        }

        if ($ictRequest->status === 'submitted') {
            $ictRequest->update([
                'status' => 'manager_approved',
                'manager_approved_by' => $user->id,
                'manager_approved_at' => now(),
            ]);
        } elseif ($ictRequest->status === 'manager_approved') {
            $ictRequest->update([
                'status' => 'ga_approved',
                'ga_approved_by' => $user->id,
                'ga_approved_at' => now(),
            ]);
        } elseif ($ictRequest->status === 'ga_approved') {
            $ictRequest->update([
                'status' => 'ict_approved',
                'ict_approved_by' => $user->id,
                'ict_approved_at' => now(),
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
            'submitted' => $user->isUnitAdmin(),
            'manager_approved' => $user->isHrgaApprover(),
            'ga_approved' => $user->isIctAdmin(),
            default => false,
        };
    }

    protected function canHandleEmail($user, EmailRequest $request): bool
    {
        return match ($request->status) {
            'submitted' => $user->isUnitAdmin(),
            'manager_approved' => $user->isHrgaApprover(),
            'hrga_verified' => $user->isIctAdmin(),
            default => false,
        };
    }
}
