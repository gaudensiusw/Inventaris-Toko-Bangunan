<?php

use Illuminate\Support\Facades\Route;
use Modules\Customer\Http\Controllers\CustomerController;

Route::get('/customers', [CustomerController::class, 'index'])->name('customer.index');
