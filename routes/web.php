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

// Rute Default Landing Page - Explicitly matched
Route::match(['GET', 'POST', 'HEAD'], '/', [AuthController::class, 'index'])->name('home');

// Modular Routes (Public)
require __DIR__ . '/auth.php';

// Public Calibration Download (Auto-download via QR)
Route::get('/calibration/verification/{id}/download', [\App\Http\Controllers\CalibrationController::class, 'publicVerificationsDownload'])->name('public.calibration.download');

// Main Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::post('/machine-status/update', [MachineStatusController::class, 'update'])->name('machine-status.update');

    require __DIR__ . '/checksheets.php';
    require __DIR__ . '/management.php';
    require __DIR__ . '/analysis.php';
    require __DIR__ . '/calibration.php';

    // KAKOTORA
    Route::resource('kakotora', \App\Http\Controllers\KakotoraController::class);

    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/clear-all', [\App\Http\Controllers\NotificationController::class, 'clearAll'])->name('notifications.clear-all');

    // Session Heartbeat
    Route::get('/session/ping', function () {
        return response()->json(['status' => 'ok']);
    })->name('session.ping');
});

// Catch-all route for debugging
Route::fallback(function () {
    return response()->json([
        'message' => 'Route not found or method not allowed',
        'uri' => request()->getRequestUri(),
        'method' => request()->getMethod(),
    ], 404);
});


