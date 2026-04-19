<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'overview'])->name('dashboard');
    Route::get('/dashboard/prediksi', [\App\Http\Controllers\DashboardController::class, 'prediksi'])->name('dashboard.prediksi');
    Route::get('/dashboard/selection', [\App\Http\Controllers\DashboardController::class, 'selection'])->name('dashboard.selection');
    Route::get('/dashboard/trend', [\App\Http\Controllers\DashboardController::class, 'trend'])->name('dashboard.trend');
    Route::get('/dashboard/report', [\App\Http\Controllers\DashboardController::class, 'report'])->name('dashboard.report');
});

// Admin Routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/data-input', [\App\Http\Controllers\AdminController::class, 'dataInput'])->name('dataInput');
    Route::get('/model-settings', [\App\Http\Controllers\AdminController::class, 'modelSettings'])->name('modelSettings');
    Route::post('/model-settings/retrain', [\App\Http\Controllers\AdminController::class, 'retrainModel'])->name('modelSettings.retrain');
    Route::get('/users', [\App\Http\Controllers\AdminController::class, 'users'])->name('users');
    Route::post('/users', [\App\Http\Controllers\AdminController::class, 'storeUser'])->name('users.store');

    // Data Management
    Route::post('/data-input/upload', [\App\Http\Controllers\AdminController::class, 'uploadDataset'])->name('dataInput.upload');
    Route::post('/data-input/manual', [\App\Http\Controllers\AdminController::class, 'storeManualData'])->name('dataInput.manual');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
