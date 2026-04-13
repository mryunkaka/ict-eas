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
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard', [
            'statCards' => [
                ['key' => 'ict_requests', 'label' => 'Permintaan ICT', 'href' => route('forms.ict-requests.index'), 'icon' => 'heroicon-o-computer-desktop'],
                ['key' => 'email_requests', 'label' => 'Permohonan Email', 'href' => route('forms.email-requests.index'), 'icon' => 'heroicon-o-envelope'],
                ['key' => 'repair_requests', 'label' => 'Perbaikan ICT', 'href' => route('forms.repairs.index'), 'icon' => 'heroicon-o-wrench-screwdriver'],
                ['key' => 'incident_reports', 'label' => 'BAK / Insiden', 'href' => route('forms.incidents.index'), 'icon' => 'heroicon-o-exclamation-triangle'],
                ['key' => 'assets', 'label' => 'Master Asset', 'href' => route('forms.assets.index'), 'icon' => 'heroicon-o-cube'],
                ['key' => 'inventory_items', 'label' => 'Stok Aktif', 'href' => route('inventory.index'), 'icon' => 'heroicon-o-archive-box'],
                ['key' => 'project_requests', 'label' => 'Project ICT', 'href' => route('forms.projects.index'), 'icon' => 'heroicon-o-clipboard-document-list'],
            ],
        ]);
    }

    public function stats(): JsonResponse
    {
        $user = auth()->user();

        $stats = Cache::remember(
            "dashboard-stats:user-{$user->id}",
            now()->addSeconds(20),
            fn () => [
                'ict_requests' => UnitScope::apply(IctRequest::query(), $user)->count(),
                'email_requests' => UnitScope::apply(EmailRequest::query(), $user)->count(),
                'repair_requests' => UnitScope::apply(RepairRequest::query(), $user)->count(),
                'incident_reports' => UnitScope::apply(IncidentReport::query(), $user)->count(),
                'assets' => UnitScope::apply(Asset::query(), $user)->count(),
                'inventory_items' => InventoryItem::query()->when(! $user->isSuperAdmin(), fn ($query) => $query->where('unit_id', $user->unit_id))->count(),
                'project_requests' => UnitScope::apply(ProjectRequest::query(), $user)->count(),
            ],
        );

        return response()->json([
            'stats' => $stats,
            'generated_at' => now()->toIso8601String(),
        ]);
    }
}
