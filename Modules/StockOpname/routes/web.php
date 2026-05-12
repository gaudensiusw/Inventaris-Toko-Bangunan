<?php

use Illuminate\Support\Facades\Route;
use Modules\StockOpname\Http\Controllers\StockOpnameController;

Route::middleware(['auth'])->group(function () {
    Route::get('/stock-opname', [StockOpnameController::class, 'index'])->name('stockopname.index');
    Route::get('/stock-opname/history', [StockOpnameController::class, 'history'])->name('stockopname.history');
    Route::post('/stock-opname', [StockOpnameController::class, 'store'])->name('stockopname.store');
    Route::get('/stock-opname/history', [StockOpnameController::class, 'history'])->name('stockopname.history');
    
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/stock-opname/approval', [StockOpnameController::class, 'approval'])->name('stockopname.approval');
        Route::post('/stock-opname/approve/{id}', [StockOpnameController::class, 'approve'])->name('stockopname.approve');
        Route::post('/stock-opname/reject/{id}', [StockOpnameController::class, 'reject'])->name('stockopname.reject');
    });
});
