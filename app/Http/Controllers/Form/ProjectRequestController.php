<?php

namespace App\Http\Controllers\Form;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequestRequest;
use App\Models\ProjectRequest;
use App\Support\UnitScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectRequestController extends Controller
{
    public function index(Request $request): View
    {
        $sort = in_array($request->string('sort')->toString(), ['title', 'priority', 'status', 'target_date', 'created_at'], true) ? $request->string('sort')->toString() : 'created_at';
        $direction = $request->string('direction')->toString() === 'asc' ? 'asc' : 'desc';
        $perPage = in_array((int) $request->integer('per_page', 10), [10, 20, 30, 50, 100], true) ? (int) $request->integer('per_page', 10) : 10;
        $search = $request->string('search')->toString();

        $projects = UnitScope::apply(
            ProjectRequest::query()
                ->select(['id', 'unit_id', 'requester_id', 'title', 'priority', 'status', 'target_date', 'created_at'])
                ->with(['requester:id,name', 'unit:id,name'])
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($inner) use ($search) {
                        $inner->where('title', 'like', "%{$search}%")
                            ->orWhere('status', 'like', "%{$search}%")
                            ->orWhere('priority', 'like', "%{$search}%");
                    });
                }),
            auth()->user()
        )->orderBy($sort, $direction)->paginate($perPage)->withQueryString();

        return view('forms.project-requests.index', compact('projects', 'sort', 'direction', 'perPage', 'search'));
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
