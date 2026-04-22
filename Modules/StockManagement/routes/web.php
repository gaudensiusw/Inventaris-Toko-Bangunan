<?php

use Illuminate\Support\Facades\Route;
use Modules\StockManagement\Http\Controllers\StockManagementController;

Route::get('/stock-management', [StockManagementController::class, 'index'])->name('stockmanagement.index');
