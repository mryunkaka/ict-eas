<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Form\AssetController;
use App\Http\Controllers\Form\EmailRequestController;
use App\Http\Controllers\Form\IctRequestController;
use App\Http\Controllers\Form\IncidentReportController;
use App\Http\Controllers\Form\InventoryController;
use App\Http\Controllers\Form\ProjectRequestController;
use App\Http\Controllers\Form\RepairRequestController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('forms')->name('forms.')->group(function () {
        Route::resource('ict-requests', IctRequestController::class)->only(['index', 'create', 'store']);
        Route::resource('email-requests', EmailRequestController::class)->only(['index', 'create', 'store']);
        Route::resource('repairs', RepairRequestController::class)->only(['index', 'create', 'store']);
        Route::resource('incidents', IncidentReportController::class)->only(['index', 'create', 'store']);
        Route::resource('assets', AssetController::class)->only(['index']);
        Route::resource('projects', ProjectRequestController::class)->only(['index', 'create', 'store']);
    });

    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
});

require __DIR__.'/auth.php';
