<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnalysisController;

Route::middleware(['auth', 'role:admin,supervisor,kashift,asst_manager,manager,oshef'])->group(function () {
    Route::get('/analysis/monthly-ng-sub-assy', [AnalysisController::class, 'monthlyNgSubAssy'])->name('analysis.monthly_ng');
    Route::get('/analysis/monthly-ng-in-process', [AnalysisController::class, 'monthlyNgInProcess'])->name('analysis.monthly_ng_in_process');
    Route::get('/analysis/monthly-ng-cross-cut', [AnalysisController::class, 'monthlyNgCrossCut'])->name('analysis.monthly_ng_cross_cut');
});
