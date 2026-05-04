<?php

use Illuminate\Support\Facades\Route;
use Modules\StockManagement\Http\Controllers\StockManagementController;

Route::middleware(['auth'])->group(function () {
    Route::get('/stock-management', [StockManagementController::class, 'index'])->name('stockmanagement.index');
    Route::post('/stock-management', [StockManagementController::class, 'store'])->name('stockmanagement.store');
});
