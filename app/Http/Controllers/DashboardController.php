<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\EmailRequest;
use App\Models\IctRequest;
use App\Models\IncidentReport;
use App\Models\InventoryItem;
use App\Models\ProjectRequest;
use App\Models\RepairRequest;
use App\Support\UnitScope;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();

        return view('dashboard', [
            'stats' => [
                ['label' => 'Permintaan ICT', 'value' => UnitScope::apply(IctRequest::query(), $user)->count()],
                ['label' => 'Permohonan Email', 'value' => UnitScope::apply(EmailRequest::query(), $user)->count()],
                ['label' => 'Perbaikan ICT', 'value' => UnitScope::apply(RepairRequest::query(), $user)->count()],
                ['label' => 'BAK / Insiden', 'value' => UnitScope::apply(IncidentReport::query(), $user)->count()],
                ['label' => 'Master Asset', 'value' => UnitScope::apply(Asset::query(), $user)->count()],
                ['label' => 'Stok Aktif', 'value' => InventoryItem::query()->count()],
                ['label' => 'Project ICT', 'value' => UnitScope::apply(ProjectRequest::query(), $user)->count()],
            ],
        ]);
    }
}
