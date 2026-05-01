<?php

use Illuminate\Support\Facades\Route;
use Modules\Employee\Http\Controllers\EmployeeController;

Route::get('/employees', [EmployeeController::class, 'index'])->name('employee.index');
Route::post('/employees', [EmployeeController::class, 'store'])->name('employee.store');
Route::get('/employees/{id}', [EmployeeController::class, 'show'])->name('employee.show');
Route::get('/employees/{id}/slip-gaji', [EmployeeController::class, 'generateSlipGaji'])->name('employee.slipGaji');
Route::post('/employees/{id}/absensi/store', [EmployeeController::class, 'storeAbsensi'])->name('employee.absensi.store');
Route::put('/employees/{id}', [EmployeeController::class, 'update'])->name('employee.update');
Route::delete('/employees/{id}', [EmployeeController::class, 'destroy'])->name('employee.destroy');
