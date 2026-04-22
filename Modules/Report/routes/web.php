<?php

use Illuminate\Support\Facades\Route;
use Modules\Report\Http\Controllers\ReportController;

Route::get('/reports', [ReportController::class, 'index'])->name('report.index');
