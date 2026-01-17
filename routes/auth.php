<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Rute Otentikasi
Route::get('login', [AuthController::class, 'login'])->name('login');
Route::post('login', [AuthController::class, 'authenticate'])->name('login.process');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');
