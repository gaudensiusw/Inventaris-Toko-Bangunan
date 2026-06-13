<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Modules\Dashboard\Http\Controllers\DashboardController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'chartData'])->name('dashboard.chart-data');

    Route::get('/absensi/kamera', [\App\Http\Controllers\FaceAbsensiController::class, 'index'])->name('absensi.kamera');
    Route::post('/absensi/kamera', [\App\Http\Controllers\FaceAbsensiController::class, 'store'])->name('absensi.kamera.store');

    Route::get('/audit-logs', [\App\Http\Controllers\AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::delete('/audit-logs/{id}', [\App\Http\Controllers\AuditLogController::class, 'destroy'])->name('audit-logs.destroy');

    // Modules protection is usually handled in their own route files, 
    // but we can wrap them here or update each module's web.php.

    Route::middleware(['role:owner,supervisor'])->group(function () {
        Route::get('/accounts', [\App\Http\Controllers\AccountController::class, 'index'])->name('accounts.index');
        Route::post('/accounts', [\App\Http\Controllers\AccountController::class, 'store'])->name('accounts.store');
        Route::put('/accounts/{id}', [\App\Http\Controllers\AccountController::class, 'update'])->name('accounts.update');
        Route::delete('/accounts/{id}', [\App\Http\Controllers\AccountController::class, 'destroy'])->name('accounts.destroy');
        Route::patch('/accounts/{id}/toggle-status', [\App\Http\Controllers\AccountController::class, 'toggleStatus'])->name('accounts.toggleStatus');
        Route::post('/accounts/{id}/reset-password', [\App\Http\Controllers\AccountController::class, 'resetPassword'])->name('accounts.resetPassword');
        Route::get('/accounts/permissions', [\App\Http\Controllers\AccountController::class, 'getPermissions'])->name('accounts.permissions.get');
        Route::post('/accounts/permissions', [\App\Http\Controllers\AccountController::class, 'updatePermissions'])->name('accounts.permissions.update');
    });
});