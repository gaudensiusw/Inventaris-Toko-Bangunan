<?php

use Illuminate\Support\Facades\Route;
use Modules\Notification\Http\Controllers\NotificationController;

Route::get('/notifications', [NotificationController::class, 'index'])->name('notification.index');
Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notification.read');
