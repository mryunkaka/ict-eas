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

        $items = InventoryItem::query()
            ->with('unit')
            ->when($scope !== 'all', fn ($query) => $query->where('scope', $scope))
            ->when(!$user->isSuperAdmin() && $scope !== 'eas', fn ($query) => $query->where('unit_id', $user->unit_id))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('forms.inventory.index', compact('items', 'scope'));
    }
}
