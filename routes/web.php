<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// Dummy route untuk menerima klik tombol login dan melempar user ke dashboard
Route::post('/login', function () {
    return redirect('/dashboard');
})->name('login.submit');

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// Dummy route untuk tombol logout, mengembalikan user ke halaman login
Route::post('/logout', function () {
    return redirect('/login');
})->name('logout');