<?php

use Illuminate\Support\Facades\Route;
use Modules\POS\Http\Controllers\POSController;

Route::get('/pos', [POSController::class, 'index'])->name('pos.index');
Route::post('/pos', [POSController::class, 'store'])->name('pos.store');
Route::get('/pos/receipt/{id}', [POSController::class, 'receipt'])->name('pos.receipt');
