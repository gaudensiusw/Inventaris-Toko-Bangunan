<?php

use Illuminate\Support\Facades\Route;
use Modules\Customer\Http\Controllers\CustomerController;

Route::middleware(['auth'])->group(function () {
    Route::get('/customers', [CustomerController::class, 'index'])->name('customer.index');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customer.store');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customer.update');
    Route::put('/customers/transaction/{id}', [CustomerController::class, 'updateTransaction'])->name('customer.transaction.update');
    Route::post('/customers/transaction/{id}/refund', [CustomerController::class, 'processRefund'])->name('customer.transaction.refund');
    Route::post('/customers/pay/{id}', [CustomerController::class, 'payTransaction'])->name('customer.pay');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customer.destroy');
});
