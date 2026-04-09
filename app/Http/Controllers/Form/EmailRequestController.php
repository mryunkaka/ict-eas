<?php

namespace App\Http\Controllers\Form;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmailRequestRequest;
use App\Models\EmailRequest;
use App\Support\UnitScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmailRequestController extends Controller
{
    public function index(): View
    {
        $requests = UnitScope::apply(
            EmailRequest::query()->with(['requester', 'unit'])->latest(),
            auth()->user()
        )->paginate(10);

        return view('forms.email-requests.index', compact('requests'));
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
