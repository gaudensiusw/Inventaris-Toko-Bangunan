<?php

use Illuminate\Support\Facades\Route;
use Modules\StockManagement\Http\Controllers\StockManagementController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('stockmanagements', StockManagementController::class)->names('stockmanagement');
});
