<?php

namespace App\Http\Controllers\Form;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRepairRequestRequest;
use App\Models\Asset;
use App\Models\RepairRequest;
use App\Support\UnitScope;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RepairRequestController extends Controller
{
    public function index(Request $request): View
    {
        $currentYear = now()->year;
        $isFirstSemester = now()->month <= 6;
        $defaultStartDate = Carbon::create($currentYear, $isFirstSemester ? 1 : 7, 1)->startOfDay();
        $defaultEndDate = Carbon::create($currentYear, $isFirstSemester ? 6 : 12, 1)->endOfMonth()->endOfDay();

        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'until' => ['nullable', 'date', 'after_or_equal:from'],
            'sort' => ['nullable', 'in:problem_summary,priority,status,created_at'],
            'direction' => ['nullable', 'in:asc,desc'],
        ]);

        $startDate = isset($validated['from']) && $validated['from'] !== ''
            ? Carbon::parse($validated['from'])->startOfDay()
            : $defaultStartDate;
        $endDate = isset($validated['until']) && $validated['until'] !== ''
            ? Carbon::parse($validated['until'])->endOfDay()
            : $defaultEndDate;
        $sort = $validated['sort'] ?? 'created_at';
        $direction = $validated['direction'] ?? 'desc';

        $requests = UnitScope::apply(
            RepairRequest::query()
                ->select([
                    'id',
                    'unit_id',
                    'requester_id',
                    'asset_id',
                    'problem_type',
                    'problem_summary',
                    'priority',
                    'status',
                    'created_at',
                ])
                ->with(['requester:id,name', 'asset:id,name'])
                ->whereBetween('created_at', [$startDate, $endDate]),
            auth()->user()
        )
            ->orderBy($sort, $direction)
            ->orderByDesc('id')
            ->get();

        return view('forms.repair-requests.index', [
            'requests' => $requests,
            'sort' => $sort,
            'direction' => $direction,
            'filters' => [
                'from' => $startDate->toDateString(),
                'until' => $endDate->toDateString(),
            ],
        ]);
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
