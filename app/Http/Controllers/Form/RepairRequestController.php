<?php

namespace App\Http\Controllers\Form;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRepairRequestRequest;
use App\Models\Asset;
use App\Models\RepairRequest;
use App\Support\UnitScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RepairRequestController extends Controller
{
    public function index(Request $request): View
    {
        $sort = in_array($request->string('sort')->toString(), ['problem_summary', 'priority', 'status', 'created_at'], true) ? $request->string('sort')->toString() : 'created_at';
        $direction = $request->string('direction')->toString() === 'asc' ? 'asc' : 'desc';
        $perPage = in_array((int) $request->integer('per_page', 10), [10, 20, 30, 50, 100], true) ? (int) $request->integer('per_page', 10) : 10;
        $search = $request->string('search')->toString();

        $requests = UnitScope::apply(
            RepairRequest::query()
                ->select(['id', 'unit_id', 'requester_id', 'asset_id', 'problem_type', 'priority', 'status', 'created_at'])
                ->with(['requester:id,name', 'asset:id,name'])
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($inner) use ($search) {
                        $inner->where('problem_summary', 'like', "%{$search}%")
                            ->orWhere('problem_type', 'like', "%{$search}%")
                            ->orWhere('status', 'like', "%{$search}%");
                    });
                }),
            auth()->user()
        )->orderBy($sort, $direction)->paginate($perPage)->withQueryString();

        return view('forms.repair-requests.index', compact('requests', 'sort', 'direction', 'perPage', 'search'));
    }

    public function create(): View
    {
        $assets = UnitScope::apply(
            Asset::query()->select(['id', 'unit_id', 'name'])->orderBy('name'),
            auth()->user()
        )->limit(200)->get();

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
