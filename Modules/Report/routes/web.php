<?php

use Illuminate\Support\Facades\Route;
use Modules\Report\Http\Controllers\ReportController;

Route::middleware(['web', 'auth', 'role:owner,supervisor'])->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('report.index');
    Route::get('/reports/{type}', [ReportController::class, 'show'])->name('report.show');
});
