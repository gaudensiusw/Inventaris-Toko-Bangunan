<?php

use Illuminate\Support\Facades\Route;
use Modules\TagihanSupplier\Http\Controllers\TagihanSupplierController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('tagihansuppliers', TagihanSupplierController::class)->names('tagihansupplier');
});
