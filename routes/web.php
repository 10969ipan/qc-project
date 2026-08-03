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
Route::match(['GET', 'POST', 'HEAD'], '/qc', [AuthController::class, 'index']);

// Modular Routes (Public)
require __DIR__ . '/auth.php';

// Public Calibration Download (Auto-download via QR)
Route::get('/calibration/verification/{id}/download', [\App\Http\Controllers\CalibrationController::class, 'publicVerificationsDownload'])->name('public.calibration.download');

// Main Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/tv', [\App\Http\Controllers\DashboardController::class, 'tvIndex'])->name('dashboard.tv');
    Route::get('/dashboard/tv/live', [\App\Http\Controllers\DashboardController::class, 'tvLiveData'])->name('dashboard.tv.live');
    Route::get('/dashboard/tv/defects', [\App\Http\Controllers\DashboardController::class, 'tvDefects'])->name('dashboard.tv.defects');
    Route::post('/machine-status/update', [MachineStatusController::class, 'update'])->name('machine-status.update');

    require __DIR__ . '/checksheets.php';
    require __DIR__ . '/management.php';
    require __DIR__ . '/analysis.php';
    require __DIR__ . '/calibration.php';
    require __DIR__ . '/verification.php';

    // KAKOTORA
    Route::post('kakotora/bulk-destroy', [\App\Http\Controllers\KakotoraController::class, 'bulkDestroy'])->name('kakotora.bulk_destroy');
    Route::post('kakotora/delete-pdf/{id}', [\App\Http\Controllers\KakotoraController::class, 'deletePdf'])->name('kakotora.delete_pdf');
    Route::post('kakotora/delete-foto/{id}', [\App\Http\Controllers\KakotoraController::class, 'deleteFoto'])->name('kakotora.delete_foto');
    Route::get('kakotora/print', [\App\Http\Controllers\KakotoraController::class, 'print'])->name('kakotora.print');
    Route::post('kakotora/add-problem', [\App\Http\Controllers\KakotoraController::class, 'addProblem'])->name('kakotora.add_problem');
    Route::post('kakotora/delete-problem', [\App\Http\Controllers\KakotoraController::class, 'deleteProblem'])->name('kakotora.delete_problem');
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
    if (request()->expectsJson() || request()->ajax()) {
        return response()->json([
            'message' => 'Route not found or method not allowed',
            'uri' => request()->getRequestUri(),
            'method' => request()->getMethod(),
        ], 404);
    }
    return redirect()->route('login');
});



Route::get('/test-label-qr', function () { return view('plating.label_qr'); });
