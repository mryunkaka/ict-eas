<?php

namespace App\Http\Controllers\Form;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIctRequestRequest;
use App\Models\IctRequest;
use App\Support\UnitScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class IctRequestController extends Controller
{
    public function index(): View
    {
        $requests = UnitScope::apply(
            IctRequest::query()->with(['requester', 'unit'])->latest(),
            auth()->user()
        )->paginate(10);

        return view('forms.ict-requests.index', compact('requests'));
    }

    public function create(): View
    {
        return view('forms.ict-requests.create');
    }

    public function store(StoreIctRequestRequest $request): RedirectResponse
    {
        $user = $request->user();

        $ictRequest = IctRequest::create([
            'unit_id' => $user->unit_id,
            'requester_id' => $user->id,
            'subject' => (string) $request->input('subject'),
            'request_category' => (string) $request->input('request_category'),
            'priority' => (string) $request->input('priority'),
            'status' => 'submitted',
            'needed_at' => $request->input('needed_at'),
            'justification' => (string) $request->input('justification'),
            'additional_budget_reason' => $request->input('additional_budget_reason'),
        ]);

        $ictRequest->items()->create([
            'line_number' => 1,
            'item_name' => (string) $request->input('item_name'),
            'brand_type' => $request->input('brand_type'),
            'quantity' => (int) $request->input('quantity'),
            'estimated_price' => $request->input('estimated_price'),
            'notes' => $request->input('item_notes'),
        ]);

        return redirect()->route('forms.ict-requests.index')->with('status', 'Permintaan fasilitas ICT berhasil disimpan.');
    }
}
