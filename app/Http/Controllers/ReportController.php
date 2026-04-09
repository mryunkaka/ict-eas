<?php

namespace App\Http\Controllers;

use App\Exports\ModuleReportExport;
use App\Models\EmailRequest;
use App\Models\IctRequest;
use App\Models\IncidentReport;
use App\Models\ProjectRequest;
use App\Models\RepairRequest;
use App\Support\UnitScope;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $module = $request->string('module')->toString() ?: 'ict_requests';
        $status = $request->string('status')->toString();

        [$query, $headings, $rows] = $this->buildReport($request, $module, $status);

        return view('reports.index', [
            'module' => $module,
            'status' => $status,
            'rows' => $rows,
            'headings' => $headings,
            'summary' => [
                'total' => $query->count(),
                'moduleLabel' => str($module)->replace('_', ' ')->title(),
            ],
        ]);
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $module = $request->string('module')->toString() ?: 'ict_requests';
        $status = $request->string('status')->toString();

        [, $headings, $rows] = $this->buildReport($request, $module, $status);

        return Excel::download(
            new ModuleReportExport($headings, $rows->all()),
            'report-'.Str::slug($module).'.xlsx'
        );
    }

    public function exportPdf(Request $request): Response
    {
        $module = $request->string('module')->toString() ?: 'ict_requests';
        $status = $request->string('status')->toString();

        [, $headings, $rows] = $this->buildReport($request, $module, $status);

        return Pdf::loadView('reports.pdf', [
            'title' => 'Report '.str($module)->replace('_', ' ')->title(),
            'headings' => $headings,
            'rows' => $rows,
            'generatedAt' => now(),
        ])->download('report-'.Str::slug($module).'.pdf');
    }

    /**
     * @return array{0: Builder, 1: array<int, string>, 2: Collection<int, array<int, string|null>>}
     */
    protected function buildReport(Request $request, string $module, string $status): array
    {
        $user = $request->user();
        $from = $request->date('from');
        $until = $request->date('until');

        [$query, $headings, $map] = match ($module) {
            'email_requests' => [
                UnitScope::apply(EmailRequest::query()->with(['unit', 'requester']), $user),
                ['Tanggal', 'Pemohon', 'Unit', 'Email', 'Akses', 'Status'],
                fn (EmailRequest $row) => [
                    optional($row->created_at)->format('Y-m-d H:i'),
                    $row->employee_name,
                    $row->unit?->name,
                    $row->requested_email,
                    strtoupper($row->access_level),
                    strtoupper($row->status),
                ],
            ],
            'repair_requests' => [
                UnitScope::apply(RepairRequest::query()->with(['unit', 'requester', 'asset']), $user),
                ['Tanggal', 'Pemohon', 'Unit', 'Ringkasan', 'Prioritas', 'Status'],
                fn (RepairRequest $row) => [
                    optional($row->created_at)->format('Y-m-d H:i'),
                    $row->requester?->name,
                    $row->unit?->name,
                    $row->problem_summary,
                    strtoupper($row->priority),
                    strtoupper($row->status),
                ],
            ],
            'incident_reports' => [
                UnitScope::apply(IncidentReport::query()->with(['unit', 'reporter']), $user),
                ['Tanggal', 'Pelapor', 'Unit', 'Jenis', 'Judul', 'Status'],
                fn (IncidentReport $row) => [
                    optional($row->occurred_at)->format('Y-m-d H:i'),
                    $row->reporter?->name,
                    $row->unit?->name,
                    strtoupper($row->incident_type),
                    $row->title,
                    strtoupper($row->status),
                ],
            ],
            'project_requests' => [
                UnitScope::apply(ProjectRequest::query()->with(['unit', 'requester']), $user),
                ['Tanggal', 'Pemohon', 'Unit', 'Project', 'Prioritas', 'Status'],
                fn (ProjectRequest $row) => [
                    optional($row->created_at)->format('Y-m-d H:i'),
                    $row->requester?->name,
                    $row->unit?->name,
                    $row->title,
                    strtoupper($row->priority),
                    strtoupper($row->status),
                ],
            ],
            default => [
                UnitScope::apply(IctRequest::query()->with(['unit', 'requester']), $user),
                ['Tanggal', 'Pemohon', 'Unit', 'Subjek', 'Prioritas', 'Status'],
                fn (IctRequest $row) => [
                    optional($row->created_at)->format('Y-m-d H:i'),
                    $row->requester?->name,
                    $row->unit?->name,
                    $row->subject,
                    strtoupper($row->priority),
                    strtoupper($row->status),
                ],
            ],
        };

        $query
            ->when($status !== '', fn ($builder) => $builder->where('status', $status))
            ->when($from, fn ($builder) => $builder->whereDate('created_at', '>=', $from))
            ->when($until, fn ($builder) => $builder->whereDate('created_at', '<=', $until));

        $rows = $query->latest()->get()->map($map);

        return [$query, $headings, $rows];
    }
}
