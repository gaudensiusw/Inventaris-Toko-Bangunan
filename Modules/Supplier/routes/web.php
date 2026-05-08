<?php

use Illuminate\Support\Facades\Route;
use Modules\Supplier\Http\Controllers\SupplierController;

Route::middleware(['auth', 'role:owner,supervisor'])->group(function () {
    Route::get('/supplier', [SupplierController::class, 'index'])->name('supplier.index');
    Route::post('/supplier', [SupplierController::class, 'store'])->name('supplier.store');
    Route::put('/supplier/{supplier}', [SupplierController::class, 'update'])->name('supplier.update');
    Route::delete('/supplier/{supplier}', [SupplierController::class, 'destroy'])->name('supplier.destroy');
});
