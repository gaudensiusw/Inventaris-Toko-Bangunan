<?php

use Illuminate\Support\Facades\Route;
use Modules\StockOpname\Http\Controllers\StockOpnameController;

Route::get('/stock-opname', [StockOpnameController::class, 'index'])->name('stockopname.index');
