<?php

use Illuminate\Support\Facades\Route;
use Modules\POS\Http\Controllers\POSController;

Route::middleware(['auth', 'role:owner,operator,supervisor'])->group(function () {
    Route::get('/pos', [POSController::class, 'index'])->name('pos.index');
    Route::get('/pos/history', [POSController::class, 'history'])->name('pos.history');
    Route::put('/pos/status/{id}', [POSController::class, 'updateStatus'])->name('pos.status.update');
    Route::get('/pos/retur', [POSController::class, 'returIndex'])->name('pos.retur.index');
    Route::post('/pos/retur', [POSController::class, 'processRetur'])->name('pos.retur.process');
    Route::get('/pos/retur/receipt/{id}', [POSController::class, 'returReceipt'])->name('pos.retur.receipt');
    Route::post('/pos', [POSController::class, 'store'])->name('pos.store');
    Route::get('/pos/receipt/{id}', [POSController::class, 'receipt'])->name('pos.receipt');
    Route::get('/pos/rekomendasi', [POSController::class, 'getRecommendations'])->name('pos.rekomendasi');
});
