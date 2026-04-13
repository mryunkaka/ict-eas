<?php

namespace App\Http\Controllers\Form;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Unit;
use App\Support\UnitScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetController extends Controller
{
    public function index(Request $request): View
    {
        $sort = in_array($request->string('sort')->toString(), ['name', 'asset_number', 'serial_number', 'lifecycle_status', 'created_at'], true) ? $request->string('sort')->toString() : 'created_at';
        $direction = $request->string('direction')->toString() === 'asc' ? 'asc' : 'desc';
        $perPage = in_array((int) $request->integer('per_page', 10), [10, 20, 30, 50, 100], true) ? (int) $request->integer('per_page', 10) : 10;

        $assets = UnitScope::apply(
            Asset::query()
                ->select(['id', 'unit_id', 'assigned_user_id', 'name', 'asset_number', 'serial_number', 'lifecycle_status', 'created_at'])
                ->with(['unit:id,name', 'assignedUser:id,name'])
                ->when($request->string('search')->toString(), function ($query, $search) {
                    $query->where(function ($inner) use ($search) {
                        $inner->where('name', 'like', "%{$search}%")
                            ->orWhere('asset_number', 'like', "%{$search}%")
                            ->orWhere('serial_number', 'like', "%{$search}%");
                    });
                })
                ->orderBy($sort, $direction),
            auth()->user()
        )->paginate($perPage)->withQueryString();

        return view('forms.assets.index', compact('assets', 'sort', 'direction', 'perPage'));
    }

    public function show(Asset $asset): View
    {
        $user = auth()->user();

        abort_unless($user->isIctAdmin() || $asset->unit_id === $user->unit_id, 403);

        $asset->load([
            'unit',
            'assignedUser',
            'lifecycleLogs.actor',
            'lifecycleLogs.fromUnit',
            'lifecycleLogs.toUnit',
        ]);

        return view('forms.assets.show', [
            'asset' => $asset,
            'units' => Unit::query()->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function updateLifecycle(Request $request, Asset $asset): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->isIctAdmin(), 403);

        $validated = $request->validate([
            'action_type' => ['required', 'in:redistribute,transfer,disposal'],
            'to_unit_id' => ['nullable', 'exists:units,id'],
            'notes' => ['nullable', 'string'],
        ]);

        if (in_array($validated['action_type'], ['redistribute', 'transfer'], true) && empty($validated['to_unit_id'])) {
            return back()->withErrors(['to_unit_id' => 'Unit tujuan wajib diisi untuk transfer atau redistribusi.']);
        }

        $originalUnitId = $asset->unit_id;
        $previousStatus = $asset->lifecycle_status;
        $nextStatus = $validated['action_type'] === 'disposal' ? 'disposed' : 'active';

        $asset->update([
            'unit_id' => $validated['action_type'] === 'disposal' ? $asset->unit_id : $validated['to_unit_id'],
            'assigned_user_id' => $validated['action_type'] === 'disposal' ? null : $asset->assigned_user_id,
            'lifecycle_status' => $nextStatus,
        ]);

        $asset->lifecycleLogs()->create([
            'processed_by' => $user->id,
            'from_unit_id' => $originalUnitId,
            'to_unit_id' => $validated['action_type'] === 'disposal' ? null : $validated['to_unit_id'],
            'action_type' => $validated['action_type'],
            'previous_status' => $previousStatus,
            'next_status' => $nextStatus,
            'notes' => $validated['notes'] ?? null,
            'processed_at' => now(),
        ]);

        return back()->with('status', 'Lifecycle asset berhasil diperbarui.');
    }
}
