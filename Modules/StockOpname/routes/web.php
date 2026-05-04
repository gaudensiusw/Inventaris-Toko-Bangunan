<?php

use Illuminate\Support\Facades\Route;
use Modules\StockOpname\Http\Controllers\StockOpnameController;

Route::middleware(['auth'])->group(function () {
    Route::get('/stock-opname', [StockOpnameController::class, 'index'])->name('stockopname.index');
    Route::post('/stock-opname', [StockOpnameController::class, 'store'])->name('stockopname.store');
});
