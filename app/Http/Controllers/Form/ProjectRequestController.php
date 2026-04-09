<?php

namespace App\Http\Controllers\Form;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequestRequest;
use App\Models\ProjectRequest;
use App\Support\UnitScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProjectRequestController extends Controller
{
    public function index(): View
    {
        $projects = UnitScope::apply(
            ProjectRequest::query()->with(['requester', 'unit'])->latest(),
            auth()->user()
        )->paginate(10);

        return view('forms.project-requests.index', compact('projects'));
    }

    public function create(): View
    {
        return view('forms.project-requests.create');
    }

    public function store(StoreProjectRequestRequest $request): RedirectResponse
    {
        $user = $request->user();

        ProjectRequest::create([
            'unit_id' => $user->unit_id,
            'requester_id' => $user->id,
            'title' => (string) $request->input('title'),
            'background' => (string) $request->input('background'),
            'scope' => (string) $request->input('scope'),
            'expected_outcome' => (string) $request->input('expected_outcome'),
            'priority' => (string) $request->input('priority'),
            'status' => 'submitted',
            'target_date' => $request->input('target_date'),
        ]);

        return redirect()->route('forms.projects.index')->with('status', 'Pengajuan project berhasil disimpan.');
    }
}
