<?php

namespace App\Http\Controllers\Form;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Support\UnitScope;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetController extends Controller
{
    public function index(Request $request): View
    {
        $assets = UnitScope::apply(
            Asset::query()
                ->with(['unit', 'assignedUser'])
                ->when($request->string('search')->toString(), function ($query, $search) {
                    $query->where(function ($inner) use ($search) {
                        $inner->where('name', 'like', "%{$search}%")
                            ->orWhere('asset_number', 'like', "%{$search}%")
                            ->orWhere('serial_number', 'like', "%{$search}%");
                    });
                })
                ->latest(),
            auth()->user()
        )->paginate(15)->withQueryString();

        return view('forms.assets.index', compact('assets'));
    }
}
