<?php

use Illuminate\Support\Facades\Route;
use Modules\POS\Http\Controllers\POSController;

Route::get('/pos', [POSController::class, 'index'])->name('pos.index');
