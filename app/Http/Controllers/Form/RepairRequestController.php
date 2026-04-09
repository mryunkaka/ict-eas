<?php

namespace App\Http\Controllers\Form;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRepairRequestRequest;
use App\Models\Asset;
use App\Models\RepairRequest;
use App\Support\UnitScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RepairRequestController extends Controller
{
    public function index(): View
    {
        $requests = UnitScope::apply(
            RepairRequest::query()->with(['requester', 'asset'])->latest(),
            auth()->user()
        )->paginate(10);

        return view('forms.repair-requests.index', compact('requests'));
    }

    public function create(): View
    {
        $assets = UnitScope::apply(Asset::query()->orderBy('name'), auth()->user())->limit(200)->get();

        return view('forms.repair-requests.create', compact('assets'));
    }

    public function store(StoreRepairRequestRequest $request): RedirectResponse
    {
        $user = $request->user();

        RepairRequest::create([
            'unit_id' => $user->unit_id,
            'requester_id' => $user->id,
            'asset_id' => $request->input('asset_id'),
            'problem_type' => (string) $request->input('problem_type'),
            'problem_summary' => (string) $request->input('problem_summary'),
            'troubleshooting_note' => $request->input('troubleshooting_note'),
            'priority' => (string) $request->input('priority'),
            'status' => 'submitted',
        ]);

        return redirect()->route('forms.repairs.index')->with('status', 'Permohonan perbaikan berhasil disimpan.');
    }
}
