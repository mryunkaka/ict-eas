<?php

namespace App\Http\Controllers\Form;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmailRequestRequest;
use App\Models\EmailRequest;
use App\Support\UnitScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailRequestController extends Controller
{
    public function index(Request $request): View
    {
        $sort = in_array($request->string('sort')->toString(), ['employee_name', 'requested_email', 'status', 'created_at'], true) ? $request->string('sort')->toString() : 'created_at';
        $direction = $request->string('direction')->toString() === 'asc' ? 'asc' : 'desc';
        $perPage = in_array((int) $request->integer('per_page', 10), [10, 20, 30, 50, 100], true) ? (int) $request->integer('per_page', 10) : 10;
        $search = $request->string('search')->toString();

        $requests = UnitScope::apply(
            EmailRequest::query()
                ->select(['id', 'unit_id', 'requester_id', 'employee_name', 'requested_email', 'status', 'created_at'])
                ->with(['requester:id,name', 'unit:id,name'])
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($inner) use ($search) {
                        $inner->where('employee_name', 'like', "%{$search}%")
                            ->orWhere('requested_email', 'like', "%{$search}%")
                            ->orWhere('status', 'like', "%{$search}%");
                    });
                }),
            auth()->user()
        )->orderBy($sort, $direction)->paginate($perPage)->withQueryString();

        return view('forms.email-requests.index', compact('requests', 'sort', 'direction', 'perPage', 'search'));
    }

    public function create(): View
    {
        return view('forms.email-requests.create');
    }

    public function store(StoreEmailRequestRequest $request): RedirectResponse
    {
        $user = $request->user();

        EmailRequest::create([
            'unit_id' => $user->unit_id,
            'requester_id' => $user->id,
            'employee_name' => (string) $request->input('employee_name'),
            'department_name' => (string) $request->input('department_name'),
            'job_title' => $request->input('job_title'),
            'requested_email' => (string) $request->input('requested_email'),
            'access_level' => (string) $request->input('access_level'),
            'justification' => (string) $request->input('justification'),
            'status' => 'submitted',
        ]);

        return redirect()->route('forms.email-requests.index')->with('status', 'Permohonan email berhasil disimpan.');
    }
}
