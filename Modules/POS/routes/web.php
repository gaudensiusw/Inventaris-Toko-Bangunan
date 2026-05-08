<?php

use Illuminate\Support\Facades\Route;
use Modules\POS\Http\Controllers\POSController;

Route::middleware(['auth', 'role:owner,operator,supervisor'])->group(function () {
    Route::get('/pos', [POSController::class, 'index'])->name('pos.index');
    Route::get('/pos/history', [POSController::class, 'history'])->name('pos.history');
    Route::post('/pos', [POSController::class, 'store'])->name('pos.store');
    Route::get('/pos/receipt/{id}', [POSController::class, 'receipt'])->name('pos.receipt');
});
