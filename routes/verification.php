<?php

use App\Http\Controllers\VerificationToolController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::prefix('verifications')->name('verifications.')->group(function () {
        // Schedule
        Route::get('/schedule', [VerificationToolController::class, 'scheduleIndex'])->name('schedule.index');
        
        // Tool Master Data
        Route::get('/tools', [VerificationToolController::class, 'toolsIndex'])->name('tools.index');
        Route::post('/tools', [VerificationToolController::class, 'toolsStore'])->name('tools.store');
        Route::get('/tools/{id}/edit', [VerificationToolController::class, 'toolsEdit'])->name('tools.edit');
        Route::put('/tools/{id}', [VerificationToolController::class, 'toolsUpdate'])->name('tools.update');
        Route::delete('/tools/{id}', [VerificationToolController::class, 'toolsDestroy'])->name('tools.destroy');

        // Verification Results
        Route::get('/verifications', [VerificationToolController::class, 'verificationsIndex'])->name('verifications.index');
        Route::post('/verifications', [VerificationToolController::class, 'verificationsStore'])->name('verifications.store');
    });
});
