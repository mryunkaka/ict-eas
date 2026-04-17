<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/init-storage', function() {
    Artisan::call('storage:link');
    return 'Storage link created successfully!';
});

Route::get('/clear-cache', function() {
    Artisan::call('view:clear');
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    return 'Cache cleared successfully! View cache, app cache, dan config cache sudah dibersihkan.';
});
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Form\AssetController;
use App\Http\Controllers\Form\AssetHandoverController;
use App\Http\Controllers\Form\EmailRequestController;
use App\Http\Controllers\Form\IctRequestController;
use App\Http\Controllers\Form\IncidentReportController;
use App\Http\Controllers\Form\InventoryController;
use App\Http\Controllers\Form\ProjectRequestController;
use App\Http\Controllers\Form\RepairRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TempUploadController;
use App\Http\Controllers\Tools\PingServerController;
use App\Http\Controllers\Tools\DbConnectionController;
use App\Http\Controllers\Tools\SqlSyncController;
use App\Http\Controllers\Tools\UserManagementController;

Route::redirect('/', '/login');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->middleware(['auth', 'verified'])->name('dashboard.stats');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

	    Route::prefix('forms')->name('forms.')->group(function () {
	        Route::get('ict-requests/next-identifier', [IctRequestController::class, 'nextIdentifier'])->name('ict-requests.next-identifier');
	        Route::get('ict-requests/export', [IctRequestController::class, 'export'])->name('ict-requests.export');
	        Route::get('ict-requests/{ictRequest}/pdf', [IctRequestController::class, 'pdf'])->name('ict-requests.pdf');
	        Route::post('ict-requests/{ictRequest}/print', [IctRequestController::class, 'print'])->name('ict-requests.print');
	        Route::post('ict-requests/{ictRequest}/ppnk', [IctRequestController::class, 'storePpnk'])->name('ict-requests.ppnk.store');
	        Route::post('ict-requests/{ictRequest}/verify-audit', [IctRequestController::class, 'verifyAuditPpnk'])->name('ict-requests.verify-audit');
	        Route::post('ict-requests/{ictRequest}/ppm', [IctRequestController::class, 'storePpm'])->name('ict-requests.ppm.store');
	        Route::post('ict-requests/{ictRequest}/po', [IctRequestController::class, 'storePo'])->name('ict-requests.po.store');
	        Route::post('ict-requests/{ictRequest}/confirm-goods-arrival', [IctRequestController::class, 'confirmGoodsArrival'])->name('ict-requests.confirm-goods-arrival');
	        Route::post('ict-requests/{ictRequest}/goods-receipt', [IctRequestController::class, 'storeGoodsReceipt'])->name('ict-requests.goods-receipt.store');
	        Route::get('ict-requests/{ictRequest}/handover-report/{assetHandover}/pdf', [IctRequestController::class, 'handoverReportPdf'])->name('ict-requests.handover-report.pdf');
	        Route::get('asset-handovers/{assetHandover}/pdf', [AssetHandoverController::class, 'pdf'])->name('asset-handovers.pdf');
	        Route::delete('ict-requests/bulk-destroy', [IctRequestController::class, 'bulkDestroy'])->name('ict-requests.bulk-destroy');
	        Route::delete('ict-requests/{ictRequest}/permanent', [IctRequestController::class, 'permanentDestroy'])->name('ict-requests.permanent-destroy');
	        Route::resource('ict-requests', IctRequestController::class)->only(['index', 'create', 'store', 'edit', 'update']);
	        Route::resource('email-requests', EmailRequestController::class)->only(['index', 'create', 'store']);
	        Route::resource('repairs', RepairRequestController::class)->only(['index', 'create', 'store']);
	        Route::resource('incidents', IncidentReportController::class)->only(['index', 'create', 'store', 'show']);
	        Route::post('incidents/{incident}/maintenance', [IncidentReportController::class, 'storeMaintenance'])->name('incidents.maintenance.store');
	        Route::resource('assets', AssetController::class)->only(['index', 'show']);
	        Route::resource('asset-handovers', AssetHandoverController::class)->only(['index', 'create', 'store']);
	        Route::post('assets/{asset}/lifecycle', [AssetController::class, 'updateLifecycle'])->name('assets.lifecycle.update');
	        Route::resource('projects', ProjectRequestController::class)->only(['index', 'create', 'store']);
	    });

    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/uploads/temp', [TempUploadController::class, 'store'])->name('uploads.temp.store');
    Route::delete('/uploads/temp', [TempUploadController::class, 'destroy'])->name('uploads.temp.destroy');
    Route::get('/approvals', function () {
        return redirect()->route('forms.ict-requests.index');
    })->name('approvals.index');
    Route::post('/approvals/ict-requests/{ictRequest}', [ApprovalController::class, 'updateIct'])->name('approvals.ict.update');
    Route::post('/approvals/email-requests/{emailRequest}', [ApprovalController::class, 'updateEmail'])->name('approvals.email.update');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/monitoring-pp', [ReportController::class, 'monitoringPp'])->name('reports.monitoring-pp');
    Route::get('/reports/monitoring-pp/data', [ReportController::class, 'monitoringPpData'])->name('reports.monitoring-pp.data');
    Route::get('/reports/monitoring-pp/export-excel', [ReportController::class, 'monitoringPpExportExcel'])->name('reports.monitoring-pp.export-excel');
    Route::get('/reports/monitoring-pp/example-import', [ReportController::class, 'monitoringPpExampleExcel'])->name('reports.monitoring-pp.example-import');
    Route::post('/reports/monitoring-pp/import-excel', [ReportController::class, 'monitoringPpImportExcel'])->name('reports.monitoring-pp.import-excel');
    Route::post('/reports/monitoring-pp/upload/photo/{item}', [ReportController::class, 'monitoringPpUploadPhoto'])->name('reports.monitoring-pp.upload.photo');
    Route::post('/reports/monitoring-pp/upload/signed-form/{ictRequest}', [ReportController::class, 'monitoringPpUploadSignedForm'])->name('reports.monitoring-pp.upload.signed-form');
    Route::post('/reports/monitoring-pp/upload/ppnk/{item}', [ReportController::class, 'monitoringPpUploadPpnk'])->name('reports.monitoring-pp.upload.ppnk');
    Route::post('/reports/monitoring-pp/upload/ppm/{item}', [ReportController::class, 'monitoringPpUploadPpm'])->name('reports.monitoring-pp.upload.ppm');
    Route::post('/reports/monitoring-pp/upload/po/{item}', [ReportController::class, 'monitoringPpUploadPo'])->name('reports.monitoring-pp.upload.po');
    Route::post('/reports/monitoring-pp/upload/ba/{assetHandover}', [ReportController::class, 'monitoringPpUploadBa'])->name('reports.monitoring-pp.upload.ba');
    Route::post('/reports/monitoring-pp/bulk-delete', [ReportController::class, 'monitoringPpBulkDelete'])->name('reports.monitoring-pp.bulk-delete');
    Route::get('/reports/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
    Route::get('/reports/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
    Route::get('/tools/users', [UserManagementController::class, 'index'])->name('tools.users.index');
    Route::post('/tools/users', [UserManagementController::class, 'store'])->name('tools.users.store');
    Route::put('/tools/users/{user}', [UserManagementController::class, 'update'])->name('tools.users.update');
    Route::get('/tools/ping-server', [PingServerController::class, 'index'])->name('tools.ping.index');
    Route::post('/tools/ping-server', [PingServerController::class, 'check'])->name('tools.ping.check');
    Route::get('/tools/db-connection', [DbConnectionController::class, 'index'])->name('tools.db-connection.index');
    Route::get('/tools/sql-sync', [SqlSyncController::class, 'index'])->name('tools.sql-sync.index');
    Route::get('/tools/sql-sync/download', [SqlSyncController::class, 'download'])->name('tools.sql-sync.download');
});

require __DIR__.'/auth.php';
