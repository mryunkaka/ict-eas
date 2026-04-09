<?php

namespace App\Http\Controllers\Form;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIncidentReportRequest;
use App\Models\Asset;
use App\Models\IncidentReport;
use App\Support\UnitScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class IncidentReportController extends Controller
{
    public function index(): View
    {
        $reports = UnitScope::apply(
            IncidentReport::query()->with(['reporter', 'asset'])->latest(),
            auth()->user()
        )->paginate(10);

        return view('forms.incident-reports.index', compact('reports'));
    }

    public function create(): View
    {
        $assets = UnitScope::apply(Asset::query()->orderBy('name'), auth()->user())->limit(200)->get();

        return view('forms.incident-reports.create', compact('assets'));
    }

    public function store(StoreIncidentReportRequest $request): RedirectResponse
    {
        $user = $request->user();

        IncidentReport::create([
            'unit_id' => $user->unit_id,
            'reported_by_id' => $user->id,
            'asset_id' => $request->input('asset_id'),
            'incident_type' => (string) $request->input('incident_type'),
            'title' => (string) $request->input('title'),
            'description' => (string) $request->input('description'),
            'follow_up' => $request->input('follow_up'),
            'repairable' => match ($request->input('repairable')) {
                'yes' => true,
                'no' => false,
                default => null,
            },
            'status' => 'open',
            'occurred_at' => $request->input('occurred_at'),
        ]);

        return redirect()->route('forms.incidents.index')->with('status', 'Berita acara / insiden berhasil disimpan.');
    }
}
