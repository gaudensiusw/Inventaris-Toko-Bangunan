<?php

use Illuminate\Support\Facades\Route;
use Modules\OperationalItem\Http\Controllers\OperationalItemController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('operationalitems', OperationalItemController::class)->names('operationalitem');
});
