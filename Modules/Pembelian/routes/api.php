<?php

use Illuminate\Support\Facades\Route;
use Modules\Pembelian\Http\Controllers\PembelianController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('pembelians', PembelianController::class)->names('pembelian');
});
