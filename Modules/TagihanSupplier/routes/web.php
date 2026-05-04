<?php

use Illuminate\Support\Facades\Route;
use Modules\TagihanSupplier\Http\Controllers\TagihanSupplierController;

Route::middleware(['auth'])->group(function () {
    Route::get('/tagihan-supplier', [TagihanSupplierController::class, 'index'])->name('tagihansupplier.index');
    Route::post('/tagihan-supplier', [TagihanSupplierController::class, 'store'])->name('tagihansupplier.store');
    Route::put('/tagihan-supplier/{tagihan}', [TagihanSupplierController::class, 'update'])->name('tagihansupplier.update');
    Route::delete('/tagihan-supplier/{tagihan}', [TagihanSupplierController::class, 'destroy'])->name('tagihansupplier.destroy');
});
