<?php

namespace App\Http\Controllers\Form;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmailRequestRequest;
use App\Models\EmailRequest;
use App\Support\PublicFileUpload;
use App\Support\UnitScope;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EmailRequestController extends Controller
{
    public function index(Request $request): View
    {
        $currentYear = now()->year;
        $isFirstSemester = now()->month <= 6;
        $defaultStartDate = Carbon::create($currentYear, $isFirstSemester ? 1 : 7, 1)->startOfDay();
        $defaultEndDate = Carbon::create($currentYear, $isFirstSemester ? 6 : 12, 1)->endOfMonth()->endOfDay();

        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'until' => ['nullable', 'date', 'after_or_equal:from'],
            'sort' => ['nullable', 'in:employee_name,status,created_at'],
            'direction' => ['nullable', 'in:asc,desc'],
        ]);

        $startDate = isset($validated['from']) && $validated['from'] !== ''
            ? Carbon::parse($validated['from'])->startOfDay()
            : $defaultStartDate;
        $endDate = isset($validated['until']) && $validated['until'] !== ''
            ? Carbon::parse($validated['until'])->endOfDay()
            : $defaultEndDate;
        $sort = $validated['sort'] ?? 'created_at';
        $direction = $validated['direction'] ?? 'desc';

        $requests = UnitScope::apply(
            EmailRequest::query()
                ->select([
                    'id',
                    'unit_id',
                    'requester_id',
                    'employee_name',
                    'department_name',
                    'job_title',
                    'status',
                    'created_at',
                    'full_ttd_signed_at',
                    'full_ttd_path',
                    'full_ttd_name',
                    'full_ttd_size',
                    'full_ttd_mime',
                ])
                ->with(['requester:id,name', 'unit:id,name'])
                ->whereBetween('created_at', [$startDate, $endDate]),
            auth()->user()
        )
            ->orderBy($sort, $direction)
            ->orderByDesc('id')
            ->get();

        return view('forms.email-requests.index', [
            'requests' => $requests,
            'sort' => $sort,
            'direction' => $direction,
            'filters' => [
                'from' => $startDate->toDateString(),
                'until' => $endDate->toDateString(),
            ],
        ]);
    }

    public function create(): View
    {
        $latestRequest = UnitScope::apply(
            EmailRequest::query()
                ->select([
                    'diketahui_dept_head_name',
                    'diketahui_dept_head_title',
                    'diketahui_div_head_name',
                    'diketahui_div_head_title',
                    'disetujui_hrga_head_name',
                    'disetujui_hrga_head_title',
                    'pelaksana_ict_head_name',
                    'pelaksana_ict_head_title',
                ])
                ->latest('id'),
            auth()->user()
        )->first();

        return view('forms.email-requests.create', [
            'defaultApprovalProfile' => $latestRequest,
        ]);
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
            'requested_email' => null,
            'access_level' => 'internal',
            'justification' => (string) $request->input('justification'),
            'diketahui_dept_head_name' => $request->input('diketahui_dept_head_name'),
            'diketahui_dept_head_title' => $request->input('diketahui_dept_head_title'),
            'diketahui_div_head_name' => $request->input('diketahui_div_head_name'),
            'diketahui_div_head_title' => $request->input('diketahui_div_head_title'),
            'disetujui_hrga_head_name' => $request->input('disetujui_hrga_head_name'),
            'disetujui_hrga_head_title' => $request->input('disetujui_hrga_head_title'),
            'pelaksana_ict_head_name' => $request->input('pelaksana_ict_head_name'),
            'pelaksana_ict_head_title' => $request->input('pelaksana_ict_head_title'),
            'status' => 'submitted',
        ]);

        return redirect()->route('forms.email-requests.index')->with('status', 'Permohonan email berhasil disimpan.');
    }

    public function uploadFullTtd(Request $request, EmailRequest $emailRequest): RedirectResponse
    {
        $authorized = UnitScope::apply(
            EmailRequest::query()->whereKey($emailRequest->id),
            $request->user()
        )->exists();
        abort_unless($authorized, 403);

        $validated = $request->validate([
            'ttd_date' => ['required', 'date'],
            'ttd_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
            'from' => ['nullable', 'date'],
            'until' => ['nullable', 'date'],
            'sort' => ['nullable', 'in:employee_name,status,created_at'],
            'direction' => ['nullable', 'in:asc,desc'],
        ]);

        $stored = PublicFileUpload::store($validated['ttd_file'], 'email-request-full-ttd', 255, 'full-ttd-'.$emailRequest->id);

        $oldPath = (string) ($emailRequest->full_ttd_path ?? '');
        if ($oldPath !== '' && $oldPath !== $stored['path']) {
            Storage::disk('public')->delete($oldPath);
        }

        $emailRequest->update([
            'full_ttd_signed_at' => Carbon::parse($validated['ttd_date'])->endOfDay(),
            'full_ttd_path' => $stored['path'],
            'full_ttd_name' => $stored['name'],
            'full_ttd_size' => $stored['size'],
            'full_ttd_mime' => $stored['mime'],
        ]);

        $query = collect([
            'from' => $validated['from'] ?? null,
            'until' => $validated['until'] ?? null,
            'sort' => $validated['sort'] ?? null,
            'direction' => $validated['direction'] ?? null,
        ])->filter(fn ($value) => filled($value))->all();

        return redirect()
            ->route('forms.email-requests.index', $query)
            ->with('status', 'Dokumen permohonan email full TTD berhasil diupload.');
    }

    public function pdf(Request $request, EmailRequest $emailRequest): Response
    {
        $authorized = UnitScope::apply(
            EmailRequest::query()->whereKey($emailRequest->id),
            $request->user()
        )->exists();
        abort_unless($authorized, 403);

        $emailRequest->loadMissing(['requester.unit', 'unit']);

        $pdf = Pdf::loadView('forms.email-requests.pdf', [
            'emailRequest' => $emailRequest,
            'today' => now()->translatedFormat('d / m / Y'),
        ])->setPaper('a4', 'portrait');

        $fileName = 'permohonan-email-'.$emailRequest->id.'.pdf';

        return $pdf->stream($fileName);
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isIctAdmin() || $request->user()->isSuperAdmin(), 403);

        $validated = $request->validate([
            'selected_ids' => ['required', 'array', 'min:1'],
            'selected_ids.*' => ['integer'],
            'from' => ['nullable', 'date'],
            'until' => ['nullable', 'date'],
            'sort' => ['nullable', 'in:employee_name,status,created_at'],
            'direction' => ['nullable', 'in:asc,desc'],
        ]);

        $ids = collect($validated['selected_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return back()->with('status', 'Tidak ada data yang dipilih.');
        }

        $baseQuery = UnitScope::apply(
            EmailRequest::query()->whereIn('id', $ids),
            $request->user()
        );

        $requests = $baseQuery->get();
        if ($requests->isEmpty()) {
            return back()->with('status', 'Data tidak ditemukan atau tidak punya akses untuk menghapus data ini.');
        }

        DB::transaction(function () use ($requests): void {
            foreach ($requests as $item) {
                $path = (string) ($item->full_ttd_path ?? '');
                if ($path !== '') {
                    Storage::disk('public')->delete($path);
                }

                $item->delete();
            }
        });

        return back()->with('status', $requests->count().' data permohonan email berhasil dihapus.');
    }
}
