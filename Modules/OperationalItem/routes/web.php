<?php

use Illuminate\Support\Facades\Route;
use Modules\OperationalItem\Http\Controllers\OperationalItemController;

Route::middleware(['auth'])->group(function () {
    Route::get('/operational-items', [OperationalItemController::class, 'index'])->name('operationalitem.index');
    Route::post('/operational-items', [OperationalItemController::class, 'store'])->name('operationalitem.store');
    Route::put('/operational-items/{operational_item}', [OperationalItemController::class, 'update'])->name('operationalitem.update');
    Route::delete('/operational-items/{operational_item}', [OperationalItemController::class, 'destroy'])->name('operationalitem.destroy');
});
