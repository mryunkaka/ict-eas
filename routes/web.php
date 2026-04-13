<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Form\AssetController;
use App\Http\Controllers\Form\EmailRequestController;
use App\Http\Controllers\Form\IctRequestController;
use App\Http\Controllers\Form\IncidentReportController;
use App\Http\Controllers\Form\InventoryController;
use App\Http\Controllers\Form\ProjectRequestController;
use App\Http\Controllers\Form\RepairRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Tools\PingServerController;
use App\Http\Controllers\Tools\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->middleware(['auth', 'verified'])->name('dashboard.stats');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('forms')->name('forms.')->group(function () {
        Route::get('ict-requests/export', [IctRequestController::class, 'export'])->name('ict-requests.export');
        Route::get('ict-requests/{ictRequest}/pdf', [IctRequestController::class, 'pdf'])->name('ict-requests.pdf');
        Route::post('ict-requests/{ictRequest}/print', [IctRequestController::class, 'print'])->name('ict-requests.print');
        Route::post('ict-requests/{ictRequest}/ppnk', [IctRequestController::class, 'storePpnk'])->name('ict-requests.ppnk.store');
        Route::post('ict-requests/{ictRequest}/verify-audit', [IctRequestController::class, 'verifyAuditPpnk'])->name('ict-requests.verify-audit');
        Route::delete('ict-requests/bulk-destroy', [IctRequestController::class, 'bulkDestroy'])->name('ict-requests.bulk-destroy');
        Route::delete('ict-requests/{ictRequest}/permanent', [IctRequestController::class, 'permanentDestroy'])->name('ict-requests.permanent-destroy');
        Route::resource('ict-requests', IctRequestController::class)->only(['index', 'create', 'store', 'edit', 'update']);
        Route::resource('email-requests', EmailRequestController::class)->only(['index', 'create', 'store']);
        Route::resource('repairs', RepairRequestController::class)->only(['index', 'create', 'store']);
        Route::resource('incidents', IncidentReportController::class)->only(['index', 'create', 'store', 'show']);
        Route::post('incidents/{incident}/maintenance', [IncidentReportController::class, 'storeMaintenance'])->name('incidents.maintenance.store');
        Route::resource('assets', AssetController::class)->only(['index', 'show']);
        Route::post('assets/{asset}/lifecycle', [AssetController::class, 'updateLifecycle'])->name('assets.lifecycle.update');
        Route::resource('projects', ProjectRequestController::class)->only(['index', 'create', 'store']);
    });

    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
    Route::post('/approvals/ict-requests/{ictRequest}', [ApprovalController::class, 'updateIct'])->name('approvals.ict.update');
    Route::post('/approvals/email-requests/{emailRequest}', [ApprovalController::class, 'updateEmail'])->name('approvals.email.update');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
    Route::get('/reports/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
    Route::get('/tools/users', [UserManagementController::class, 'index'])->name('tools.users.index');
    Route::post('/tools/users', [UserManagementController::class, 'store'])->name('tools.users.store');
    Route::put('/tools/users/{user}', [UserManagementController::class, 'update'])->name('tools.users.update');
    Route::get('/tools/ping-server', [PingServerController::class, 'index'])->name('tools.ping.index');
    Route::post('/tools/ping-server', [PingServerController::class, 'check'])->name('tools.ping.check');
});

require __DIR__.'/auth.php';
