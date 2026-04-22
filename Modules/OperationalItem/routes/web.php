<?php

use Illuminate\Support\Facades\Route;
use Modules\OperationalItem\Http\Controllers\OperationalItemController;

Route::get('/operational-items', [OperationalItemController::class, 'index'])->name('operationalitem.index');
