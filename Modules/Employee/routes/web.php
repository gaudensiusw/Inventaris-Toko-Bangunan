<?php

use Illuminate\Support\Facades\Route;
use Modules\Employee\Http\Controllers\EmployeeController;

Route::get('/employees', [EmployeeController::class, 'index'])->name('employee.index');
Route::get('/employees/rekap-absensi', [EmployeeController::class, 'exportRekapAbsensi'])->name('employee.rekapAbsensi');
Route::get('/employees/rekap-periode-list', [EmployeeController::class, 'rekapPeriodeList'])->name('employee.rekapPeriodeList');
Route::post('/employees', [EmployeeController::class, 'store'])->name('employee.store');
Route::get('/employees/{id}', [EmployeeController::class, 'show'])->name('employee.show');
Route::get('/employees/{id}/slip-gaji', [EmployeeController::class, 'generateSlipGaji'])->name('employee.slipGaji')->middleware('signed');
Route::post('/employees/{id}/absensi/store', [EmployeeController::class, 'storeAbsensi'])->name('employee.absensi.store');
Route::put('/employees/{id}', [EmployeeController::class, 'update'])->name('employee.update');
Route::patch('/employees/{id}/toggle-status', [EmployeeController::class, 'toggleStatus'])->name('employee.toggleStatus');
Route::post('/employees/{id}/bayar-gaji', [EmployeeController::class, 'bayarGaji'])->name('employee.bayar-gaji');
Route::delete('/employees/{id}/absensi/destroy', [EmployeeController::class, 'destroyAbsensi'])->name('employee.absensi.destroy');
