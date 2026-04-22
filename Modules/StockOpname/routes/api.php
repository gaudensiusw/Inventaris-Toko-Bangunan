<?php

use Illuminate\Support\Facades\Route;
use Modules\StockOpname\Http\Controllers\StockOpnameController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('stockopnames', StockOpnameController::class)->names('stockopname');
});
