<?php

namespace App\Http\Controllers\Form;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $scope = $request->string('scope')->toString() ?: 'unit';
        $user = auth()->user();
        $sort = in_array($request->string('sort')->toString(), ['code', 'name', 'scope', 'quantity_on_hand', 'created_at'], true) ? $request->string('sort')->toString() : 'created_at';
        $direction = $request->string('direction')->toString() === 'asc' ? 'asc' : 'desc';
        $perPage = in_array((int) $request->integer('per_page', 10), [10, 20, 30, 50, 100], true) ? (int) $request->integer('per_page', 10) : 10;
        $search = $request->string('search')->toString();

        $items = InventoryItem::query()
            ->select(['id', 'unit_id', 'code', 'name', 'quantity_on_hand', 'scope', 'created_at'])
            ->with('unit:id,name')
            ->when($scope !== 'all', fn ($query) => $query->where('scope', $scope))
            ->when(! $user->isSuperAdmin() && $scope !== 'eas', fn ($query) => $query->where('unit_id', $user->unit_id))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('scope', 'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        return view('forms.inventory.index', compact('items', 'scope', 'sort', 'direction', 'perPage', 'search'));
    }
}
