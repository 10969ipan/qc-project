<?php

use App\Http\Controllers\CalibrationController;
use Illuminate\Support\Facades\Route;

Route::prefix('calibration')->name('calibration.')->group(function () {
    // Schedule
    Route::get('schedule', [CalibrationController::class, 'scheduleIndex'])->name('schedule.index');

    // Master Tools
    Route::get('tools', [CalibrationController::class, 'toolsIndex'])->name('tools.index');
    Route::post('tools', [CalibrationController::class, 'toolsStore'])->name('tools.store');
    Route::get('tools/{id}/edit', [CalibrationController::class, 'toolsEdit'])->name('tools.edit');
    Route::put('tools/{id}', [CalibrationController::class, 'toolsUpdate'])->name('tools.update');
    Route::post('tools/update-pr', [CalibrationController::class, 'updatePr'])->name('tools.update-pr');
    Route::delete('tools/{id}', [CalibrationController::class, 'toolsDestroy'])->name('tools.destroy');

    // Verifications
    Route::get('verifications', [CalibrationController::class, 'verificationsIndex'])->name('verifications.index');
    Route::post('verifications', [CalibrationController::class, 'verificationsStore'])->name('verifications.store');
    Route::get('verifications/{id}/edit', [CalibrationController::class, 'verificationsEdit'])->name('verifications.edit');
    Route::put('verifications/{id}', [CalibrationController::class, 'verificationsUpdate'])->name('verifications.update');
    Route::get('verifications/export-pdf', [CalibrationController::class, 'verificationsPdf'])->name('verifications.pdf');
    Route::get('verifications/{id}/qr-pdf', [CalibrationController::class, 'verificationsQrPdf'])->name('verifications.qr-pdf');
    Route::get('verifications/{id}/qr-data', [CalibrationController::class, 'verificationsQrData'])
        ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
        ->name('verifications.qr-data');
    Route::delete('verifications/{id}', [CalibrationController::class, 'verificationsDestroy'])->name('verifications.destroy');
});
