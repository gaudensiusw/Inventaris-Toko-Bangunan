<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Http\Controllers\ProductController;

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/products', [ProductController::class, 'index'])->name('product.index');
    Route::post('/products', [ProductController::class, 'store'])->name('product.store');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('product.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('product.destroy');

    // Categories & Sub-Categories
    Route::get('/categories', [\Modules\Product\Http\Controllers\CategoryController::class, 'index'])->name('category.index');
    Route::post('/categories', [\Modules\Product\Http\Controllers\CategoryController::class, 'store'])->name('category.store');
    Route::put('/categories/{category}', [\Modules\Product\Http\Controllers\CategoryController::class, 'update'])->name('category.update');
    Route::delete('/categories/{category}', [\Modules\Product\Http\Controllers\CategoryController::class, 'destroy'])->name('category.destroy');

    Route::post('/sub-categories', [\Modules\Product\Http\Controllers\CategoryController::class, 'storeSub'])->name('sub-category.store');
    Route::put('/sub-categories/{subCategory}', [\Modules\Product\Http\Controllers\CategoryController::class, 'updateSub'])->name('sub-category.update');
    Route::delete('/sub-categories/{subCategory}', [\Modules\Product\Http\Controllers\CategoryController::class, 'destroySub'])->name('sub-category.destroy');
    Route::get('/api/sub-categories/{categoryId}', [\Modules\Product\Http\Controllers\CategoryController::class, 'getSubCategories'])->name('api.sub-categories');

    // Units Master
    Route::get('/units', [\Modules\Product\Http\Controllers\UnitController::class, 'index'])->name('unit.index');
    Route::post('/units', [\Modules\Product\Http\Controllers\UnitController::class, 'store'])->name('unit.store');
    Route::put('/units/{unit}', [\Modules\Product\Http\Controllers\UnitController::class, 'update'])->name('unit.update');
    Route::delete('/units/{unit}', [\Modules\Product\Http\Controllers\UnitController::class, 'destroy'])->name('unit.destroy');
});
