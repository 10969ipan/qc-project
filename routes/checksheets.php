<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SubAssyChecksheetController;
use App\Http\Controllers\InProcessChecksheetController;
use App\Http\Controllers\CrossCutChecksheetController;
use App\Http\Controllers\SortirChecksheetController;

Route::middleware(['auth'])->group(function () {
    // --- Input Routes ---

    // Sub Assy
    Route::get('/checksheet/sub-assy', [SubAssyChecksheetController::class, 'create'])->name('checksheet.sub_assy');
    Route::post('/checksheet/sub-assy', [SubAssyChecksheetController::class, 'store'])->name('checksheet.store');

    // In-Process
    Route::get('/checksheet/in-process', [InProcessChecksheetController::class, 'create'])->name('in_process.create');
    Route::post('/checksheet/in-process', [InProcessChecksheetController::class, 'store'])->name('in_process.store');

    // Cross Cut
    Route::get('/checksheet/cross-cut', [CrossCutChecksheetController::class, 'create'])->name('cross_cut.create');
    Route::post('/checksheet/cross-cut', [CrossCutChecksheetController::class, 'store'])->name('cross_cut.store');
    Route::get('/checksheet/cross-cut/{id}', [CrossCutChecksheetController::class, 'show'])->name('cross_cut.show');
    Route::get('/checksheet/cross-cut/{id}/image', [CrossCutChecksheetController::class, 'serveImage'])->name('cross_cut.image');
    Route::get('/cross_cut/{id}/data', [CrossCutChecksheetController::class, 'getData'])->name('cross_cut.data');

    // Sortir
    Route::get('/checksheet/sortir', [SortirChecksheetController::class, 'create'])->name('sortir.create');
    Route::post('/checksheet/sortir', [SortirChecksheetController::class, 'store'])->name('sortir.store');

    // --- Report & Action Routes ---

    Route::middleware(['role:admin,supervisor,inspector,kashift,asst_manager,manager,karu_qc,kashift_plating,supervisor_plating,manager_plating'])->group(function () {
        // Index Pages
        Route::get('/report/checksheets', [SubAssyChecksheetController::class, 'index'])->name('admin.checksheets.index');
        Route::get('/report/in-process-checksheets', [InProcessChecksheetController::class, 'index'])->name('in_process.index');
        Route::get('/report/cross-cut-checksheets', [CrossCutChecksheetController::class, 'index'])->name('cross_cut.index');
        Route::get('/report/sortir-checksheets', [SortirChecksheetController::class, 'index'])->name('sortir.index');

        // Export & Sync
        Route::get('/report/in-process-checksheets/export-pdf', [InProcessChecksheetController::class, 'exportPdf'])->name('in_process.export_pdf');
        Route::get('/report/checksheets/export', [SubAssyChecksheetController::class, 'export'])->name('admin.checksheets.export');
        Route::post('/report/checksheets/sync', [SubAssyChecksheetController::class, 'syncToGoogleSheets'])->name('admin.checksheets.sync');
        Route::get('/report/in-process-checksheets/export', [InProcessChecksheetController::class, 'export'])->name('in_process.export');
        Route::post('/report/in-process-checksheets/sync', [InProcessChecksheetController::class, 'syncToGoogleSheets'])->name('in_process.sync');
        Route::get('/report/cross-cut-checksheets/export-pdf', [CrossCutChecksheetController::class, 'exportPdf'])->name('cross_cut.export_pdf');
        Route::get('/report/sortir-checksheets/export', [SortirChecksheetController::class, 'export'])->name('sortir.export');

        // Approval Actions
        Route::post('/checksheets/{id}/approve/{type}', [SubAssyChecksheetController::class, 'approve'])->name('admin.checksheets.approve');
        Route::post('/checksheets/{id}/reject/{type}', [SubAssyChecksheetController::class, 'reject'])->name('admin.checksheets.reject');
        Route::post('/in-process-checksheets/{id}/approve/{type}', [InProcessChecksheetController::class, 'approve'])->name('in_process.approve');
        Route::post('/in-process-checksheets/{id}/reject/{type}', [InProcessChecksheetController::class, 'reject'])->name('in_process.reject');
        Route::post('/cross-cut-checksheets/{id}/approve/{type}', [CrossCutChecksheetController::class, 'approve'])->name('cross_cut.approve');
        Route::post('/cross-cut-checksheets/{id}/reject/{type}', [CrossCutChecksheetController::class, 'reject'])->name('cross_cut.reject');
        Route::post('/sortir-checksheets/{id}/approve/{type}', [SortirChecksheetController::class, 'approve'])->name('sortir.approve');
        Route::post('/sortir-checksheets/{id}/reject/{type}', [SortirChecksheetController::class, 'reject'])->name('sortir.reject');

        // Edit/Update/Delete (General Management)
        Route::prefix('admin')->group(function () {
            // Sub Assy
            Route::get('checksheets/{checksheet}/edit', [SubAssyChecksheetController::class, 'edit'])->name('admin.checksheets.edit');
            Route::put('checksheets/{checksheet}', [SubAssyChecksheetController::class, 'update'])->name('admin.checksheets.update');
            Route::delete('checksheets/{checksheet}', [SubAssyChecksheetController::class, 'destroy'])->name('admin.checksheets.destroy');

            // In-Process
            Route::get('in-process-checksheets/{id}/edit', [InProcessChecksheetController::class, 'edit'])->name('in_process.edit');
            Route::put('in-process-checksheets/{id}', [InProcessChecksheetController::class, 'update'])->name('in_process.update');
            Route::delete('in-process-checksheets/{id}', [InProcessChecksheetController::class, 'destroy'])->name('in_process.destroy');

            // Cross Cut
            Route::get('/cross-cut-checksheets/{id}/edit', [CrossCutChecksheetController::class, 'edit'])->name('cross_cut.edit');
            Route::put('/cross-cut-checksheets/{id}', [CrossCutChecksheetController::class, 'update'])->name('cross_cut.update');
            Route::delete('/cross-cut-checksheets/{id}', [CrossCutChecksheetController::class, 'destroy'])->name('cross_cut.destroy');

            // Sortir
            Route::get('/sortir-checksheets/{id}/edit', [SortirChecksheetController::class, 'edit'])->name('sortir.edit');
            Route::put('/sortir-checksheets/{id}', [SortirChecksheetController::class, 'update'])->name('sortir.update');
            Route::delete('/sortir-checksheets/{id}', [SortirChecksheetController::class, 'destroy'])->name('sortir.destroy');
        });
    });

    // --- Admin-only Overrides ---
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('checksheets/{id}/edit-approval', [SubAssyChecksheetController::class, 'editApproval'])->name('checksheets.edit_approval');
        Route::put('checksheets/{id}/update-approval', [SubAssyChecksheetController::class, 'updateApproval'])->name('checksheets.update_approval');
        Route::get('in-process-checksheets/{id}/edit-approval', [InProcessChecksheetController::class, 'editApproval'])->name('in_process.edit_approval');
        Route::put('in-process-checksheets/{id}/update-approval', [InProcessChecksheetController::class, 'updateApproval'])->name('in_process.update_approval');
        Route::get('cross-cut-checksheets/{id}/edit-approval', [CrossCutChecksheetController::class, 'editApproval'])->name('cross_cut.edit_approval');
        Route::put('cross-cut-checksheets/{id}/update-approval', [CrossCutChecksheetController::class, 'updateApproval'])->name('cross_cut.update_approval');
    });
});
