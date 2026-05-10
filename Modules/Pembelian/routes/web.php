<?php

use Illuminate\Support\Facades\Route;
use Modules\Pembelian\Http\Controllers\PembelianController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('pembelian', PembelianController::class)->names('pembelian');
});
