<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\SubAssyChecksheetController;
use App\Http\Controllers\InProcessChecksheetController;
use App\Http\Controllers\CrossCutChecksheetController;
use App\Http\Controllers\MachineStatusController;

// Rute Default Landing Page
Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/dashboard');
    }
    return view('auth.login');
});

// Modular Routes (Public)
require __DIR__ . '/auth.php';

// Main Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::post('/machine-status/update', [MachineStatusController::class, 'update'])->name('machine-status.update');

    require __DIR__ . '/checksheets.php';
    require __DIR__ . '/management.php';
    require __DIR__ . '/analysis.php';

    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
});


