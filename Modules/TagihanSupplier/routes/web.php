<?php

use Illuminate\Support\Facades\Route;
use Modules\TagihanSupplier\Http\Controllers\TagihanSupplierController;

Route::get('/tagihan-supplier', [TagihanSupplierController::class, 'index'])->name('tagihansupplier.index');
