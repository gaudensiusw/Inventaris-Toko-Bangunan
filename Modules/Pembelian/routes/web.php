<?php

use Illuminate\Support\Facades\Route;
use Modules\Pembelian\Http\Controllers\PembelianController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('pembelian/{id}/receive', [PembelianController::class, 'receive'])->name('pembelian.receive');
    Route::resource('pembelian', PembelianController::class)->names('pembelian');
});
