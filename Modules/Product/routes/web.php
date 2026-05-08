<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Http\Controllers\ProductController;

Route::middleware(['auth', 'role:owner,supervisor'])->group(function () {
    Route::get('/products', [ProductController::class, 'index'])->name('product.index');
    Route::post('/products', [ProductController::class, 'store'])->name('product.store');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('product.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('product.destroy');
    Route::get('/products/sub-categories', [ProductController::class, 'getSubCategories'])->name('product.subcategories');
});
