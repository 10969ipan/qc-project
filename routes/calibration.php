<?php

use App\Http\Controllers\CalibrationController;
use Illuminate\Support\Facades\Route;

Route::prefix('calibration')->name('calibration.')->group(function () {
    // Schedule
    Route::get('schedule', [CalibrationController::class, 'scheduleIndex'])->name('schedule.index');

    // Master Tools
    Route::get('tools', [CalibrationController::class, 'toolsIndex'])->name('tools.index');
    Route::get('tools/export-pdf', [CalibrationController::class, 'toolsPdf'])->name('tools.pdf');
    Route::get('tools/print', [CalibrationController::class, 'toolsPrint'])->name('tools.print');
    Route::post('tools', [CalibrationController::class, 'toolsStore'])->name('tools.store');
    Route::get('tools/{id}/edit', [CalibrationController::class, 'toolsEdit'])->name('tools.edit');
    Route::put('tools/{id}', [CalibrationController::class, 'toolsUpdate'])->name('tools.update');
    Route::post('tools/update-pr', [CalibrationController::class, 'updatePr'])->name('tools.update-pr');
    Route::post('tools/problem-log', [CalibrationController::class, 'storeProblemLog'])->name('tools.store-problem');
    Route::post('tools/problem-log/{id}/judgment', [CalibrationController::class, 'updateProblemJudgment'])->name('tools.update-judgment');
    Route::put('tools/problem-log/{id}', [CalibrationController::class, 'updateProblemLog'])->name('tools.update-problem');
    Route::delete('tools/problem-log/{id}', [CalibrationController::class, 'destroyProblemLog'])->name('tools.destroy-problem');
    Route::get('tools/problem-logs', [CalibrationController::class, 'problemLogs'])->name('tools.problem-logs');
    Route::delete('tools/{id}', [CalibrationController::class, 'toolsDestroy'])->name('tools.destroy');

    // Verifications
    Route::get('verifications', [CalibrationController::class, 'verificationsIndex'])->name('verifications.index');
    Route::post('verifications', [CalibrationController::class, 'verificationsStore'])->name('verifications.store');
    Route::get('verifications/{id}/edit', [CalibrationController::class, 'verificationsEdit'])->name('verifications.edit');
    Route::put('verifications/{id}', [CalibrationController::class, 'verificationsUpdate'])->name('verifications.update');
    Route::get('verifications/export-pdf', [CalibrationController::class, 'verificationsPdf'])->name('verifications.pdf');
    Route::get('verifications/print', [CalibrationController::class, 'verificationsPrint'])->name('verifications.print');

    Route::get('verifications/{id}/qr-pdf', [CalibrationController::class, 'verificationsQrPdf'])->name('verifications.qr-pdf');
    Route::get('verifications/{id}/qr-data', [CalibrationController::class, 'verificationsQrData'])
        ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
        ->name('verifications.qr-data');
    Route::delete('verifications/{id}', [CalibrationController::class, 'verificationsDestroy'])->name('verifications.destroy');
});
