<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FirstPieceApprovalController;
use App\Http\Controllers\SubAssyChecksheetController;
use App\Http\Controllers\InProcessChecksheetController;
use App\Http\Controllers\CrossCutChecksheetController;
use App\Http\Controllers\CrossCutPaintingChecksheetController;
use App\Http\Controllers\SortirChecksheetController;
use App\Http\Controllers\IncomingPartController;
use App\Http\Controllers\IncomingMaterialController;
use App\Http\Controllers\IncomingSubPartController;
use App\Http\Controllers\IncomingExportController;
use App\Http\Controllers\IncomingChemicalController;
use App\Http\Controllers\PlatingChecksheetController;
use App\Http\Controllers\PaintingChecksheetController;
use App\Http\Controllers\DoubleTapeChecksheetController;
use App\Http\Controllers\PlatingScanController;

Route::middleware(['auth'])->group(function () {
    // Plating Scan Routes
    Route::prefix('plating-scan')->group(function () {
        Route::get('/pasang', [PlatingScanController::class, 'pasangCreate'])->name('plating_scan.pasang.create');
        Route::post('/pasang', [PlatingScanController::class, 'pasangStore'])->name('plating_scan.pasang.store');
        Route::get('/pasang/{id}/qr', [PlatingScanController::class, 'showPasangQr'])->name('plating_scan.pasang.qr');
        Route::get('/pasang-data', [PlatingScanController::class, 'getPasangData'])->name('plating_scan.pasang.data');
        Route::get('/wip-info', [PlatingScanController::class, 'getWipInfo'])->name('plating_scan.wip_info');

        Route::get('/cabut', [PlatingScanController::class, 'cabutCreate'])->name('plating_scan.cabut.create');
        Route::post('/cabut', [PlatingScanController::class, 'cabutStore'])->name('plating_scan.cabut.store');
        Route::get('/cabut/{id}/qr', [PlatingScanController::class, 'showCabutQr'])->name('plating_scan.cabut.qr');

        Route::get('/history', [PlatingScanController::class, 'history'])->name('plating_scan.history');
    });
    // --- Input Routes (Protected by Approval Rate after 12:00 PM) ---
    Route::middleware(['check_approval_rate'])->group(function () {
        // Sub Assy
        Route::get('/checksheet/sub-assy', [SubAssyChecksheetController::class, 'create'])->name('checksheet.sub_assy');
        Route::post('/checksheet/sub-assy', [SubAssyChecksheetController::class, 'store'])->name('checksheet.store');

        // Plating
        Route::get('/checksheet/plating', [PlatingChecksheetController::class, 'create'])->name('plating.create');
        Route::post('/checksheet/plating', [PlatingChecksheetController::class, 'store'])->name('plating.store');

        // Durability Plating (Thickness Data)
        Route::get('/checksheet/durability-plating', [\App\Http\Controllers\DurabilityPlatingChecksheetController::class, 'create'])->name('durability_plating.create');
        Route::post('/checksheet/durability-plating', [\App\Http\Controllers\DurabilityPlatingChecksheetController::class, 'store'])->name('durability_plating.store');

        // Painting
        Route::get('/checksheet/painting', [PaintingChecksheetController::class, 'create'])->name('painting.create');
        Route::post('/checksheet/painting', [PaintingChecksheetController::class, 'store'])->name('painting.store');

        // Double Tape
        Route::get('/checksheet/double-tape', [DoubleTapeChecksheetController::class, 'create'])->name('double_tape.create');
        Route::post('/checksheet/double-tape', [DoubleTapeChecksheetController::class, 'store'])->name('double_tape.store');

        // In-Process
        Route::get('/checksheet/in-process', [InProcessChecksheetController::class, 'create'])->name('in_process.create');
        Route::post('/checksheet/in-process', [InProcessChecksheetController::class, 'store'])->name('in_process.store');

        // First Piece Approval (FPA)
        Route::get('/checksheet/first-piece-approval', [FirstPieceApprovalController::class, 'create'])->name('first_piece_approval.create');
        Route::post('/checksheet/first-piece-approval', [FirstPieceApprovalController::class, 'store'])->name('first_piece_approval.store');

        // Cross Cut
        Route::get('/checksheet/cross-cut', [CrossCutChecksheetController::class, 'create'])->name('cross_cut.create');
        Route::post('/checksheet/cross-cut', [CrossCutChecksheetController::class, 'store'])->name('cross_cut.store');

        // Cross Cut Painting
        Route::get('/checksheet/cross-cut-painting', [CrossCutPaintingChecksheetController::class, 'create'])->name('cross_cut_painting.create');
        Route::post('/checksheet/cross-cut-painting', [CrossCutPaintingChecksheetController::class, 'store'])->name('cross_cut_painting.store');

        // Sortir
        Route::get('/checksheet/sortir', [SortirChecksheetController::class, 'create'])->name('sortir.create');
        Route::post('/checksheet/sortir', [SortirChecksheetController::class, 'store'])->name('sortir.store');

        // --- Incoming Routes (Input) ---
        Route::get('/checksheet/incoming-part', [IncomingPartController::class, 'create'])->name('incoming.parts.create');
        Route::post('/checksheet/incoming-part', [IncomingPartController::class, 'store'])->name('incoming.parts.store');
        Route::get('/checksheet/incoming-material', [IncomingMaterialController::class, 'create'])->name('incoming.materials.create');
        Route::post('/checksheet/incoming-material', [IncomingMaterialController::class, 'store'])->name('incoming.materials.store');
        Route::get('/checksheet/incoming-sub-part', [IncomingSubPartController::class, 'create'])->name('incoming.sub_parts.create');
        Route::post('/checksheet/incoming-sub-part', [IncomingSubPartController::class, 'store'])->name('incoming.sub_parts.store');
        Route::get('/checksheet/incoming-export', [IncomingExportController::class, 'create'])->name('incoming.exports.create');
        Route::post('/checksheet/incoming-export', [IncomingExportController::class, 'store'])->name('incoming.exports.store');
        Route::get('/checksheet/incoming-chemical', [IncomingChemicalController::class, 'create'])->name('incoming.chemicals.create');
        Route::post('/checksheet/incoming-chemical', [IncomingChemicalController::class, 'store'])->name('incoming.chemicals.store');
    });

    // Special routes for FPA that are not direct input
    Route::get('/checksheet/first-piece-approval/export-measurements-template', [FirstPieceApprovalController::class, 'exportMeasureData'])->name('first_piece_approval.export_measurements_template');
    Route::post('/checksheet/first-piece-approval/import-measurements-template', [FirstPieceApprovalController::class, 'importMeasureData'])->name('first_piece_approval.import_measurements_template');

    // Special routes for Cross Cut
    Route::get('/checksheet/cross-cut/{id}', [CrossCutChecksheetController::class, 'show'])->name('cross_cut.show');
    Route::get('/checksheet/cross-cut/{id}/image', [CrossCutChecksheetController::class, 'serveImage'])->name('cross_cut.image');
    Route::get('/cross_cut/{id}/data', [CrossCutChecksheetController::class, 'getData'])->name('cross_cut.data');
    Route::get('/api/cross-cut/next-remark', [CrossCutChecksheetController::class, 'getNextResultRemark'])->name('cross_cut.next_remark');
    Route::get('/api/cross-cut/next-no-lot', [CrossCutChecksheetController::class, 'getAutoNoLot'])->name('cross_cut.next_no_lot');

    // Special routes for Cross Cut Painting
    Route::get('/checksheet/cross-cut-painting/{id}', [CrossCutPaintingChecksheetController::class, 'show'])->name('cross_cut_painting.show');
    Route::get('/checksheet/cross-cut-painting/{id}/image', [CrossCutPaintingChecksheetController::class, 'serveImage'])->name('cross_cut_painting.image');
    Route::get('/cross_cut-painting/{id}/data', [CrossCutPaintingChecksheetController::class, 'getData'])->name('cross_cut_painting.data');

    // Special routes for Plating
    Route::get('/api/plating/next-no-lot', [PlatingChecksheetController::class, 'getAutoNoLot'])->name('plating.next_no_lot');
    Route::get('/api/plating/last-data', [PlatingChecksheetController::class, 'getLastData'])->name('plating.last_data');

    // Special routes for Painting
    Route::get('/api/painting/next-no-lot', [PaintingChecksheetController::class, 'getAutoNoLot'])->name('painting.next_no_lot');
    Route::get('/api/painting/last-data', [PaintingChecksheetController::class, 'getLastData'])->name('painting.last_data');

    // Special routes for Sub Assy
    Route::get('/api/sub-assy/last-line', [SubAssyChecksheetController::class, 'getLastLine'])->name('sub_assy.last_line');

    // --- Report & Action Routes ---

    Route::middleware(['role:admin,supervisor,inspector,kashift,asst_manager,manager,karu_qc,kashift_plating,supervisor_plating,manager_plating,oshef'])->group(function () {
        // Index Pages
        Route::get('/report/checksheets', [SubAssyChecksheetController::class, 'index'])->name('admin.checksheets.index');
        Route::get('/report/plating-checksheets', [PlatingChecksheetController::class, 'index'])->name('plating.index');
        Route::get('/report/durability-plating-checksheets', [\App\Http\Controllers\DurabilityPlatingChecksheetController::class, 'index'])->name('durability_plating.index');
        Route::get('/report/painting-checksheets', [PaintingChecksheetController::class, 'index'])->name('painting.index');
        Route::get('/report/double-tape-checksheets', [DoubleTapeChecksheetController::class, 'index'])->name('double_tape.index');
        Route::get('/report/in-process-checksheets', [InProcessChecksheetController::class, 'index'])->name('in_process.index');
        Route::get('/report/first-piece-approvals', [FirstPieceApprovalController::class, 'index'])->name('first_piece_approval.index');
        Route::get('/report/cross-cut-checksheets', [CrossCutChecksheetController::class, 'index'])->name('cross_cut.index');
        Route::get('/report/cross-cut-painting-checksheets', [CrossCutPaintingChecksheetController::class, 'index'])->name('cross_cut_painting.index');
        Route::get('/report/sortir-checksheets', [SortirChecksheetController::class, 'index'])->name('sortir.index');

        // Export & Sync
        Route::get('/report/in-process-checksheets/daily-recap', [InProcessChecksheetController::class, 'dailyRecap'])->name('in_process.daily_recap');
        Route::get('/report/in-process-checksheets/export-pdf', [InProcessChecksheetController::class, 'exportPdf'])->name('in_process.export_pdf');
        Route::get('/report/in-process-checksheets/print', [InProcessChecksheetController::class, 'printView'])->name('in_process.print');
        Route::get('/report/in-process-checksheets/export-measurements', [InProcessChecksheetController::class, 'exportMeasureData'])->name('in_process.export_measurements');
        Route::post('/report/in-process-checksheets/import-measurements', [InProcessChecksheetController::class, 'importMeasureData'])->name('in_process.import_measurements');

        Route::get('/report/first-piece-approvals/export-pdf', [FirstPieceApprovalController::class, 'exportPdf'])->name('first_piece_approval.export_pdf');
        Route::get('/report/first-piece-approvals/daily-recap', [FirstPieceApprovalController::class, 'dailyRecap'])->name('first_piece_approval.daily_recap');
        Route::get('/report/first-piece-approvals/print', [FirstPieceApprovalController::class, 'printView'])->name('first_piece_approval.print');
        Route::get('/report/first-piece-approvals/export-measurements', [FirstPieceApprovalController::class, 'exportMeasureData'])->name('first_piece_approval.export_measurements');
        Route::post('/report/first-piece-approvals/import-measurements', [FirstPieceApprovalController::class, 'importMeasureData'])->name('first_piece_approval.import_measurements');
        Route::get('/report/checksheets/export', [SubAssyChecksheetController::class, 'export'])->name('admin.checksheets.export');
        Route::get('/report/checksheets/export-pdf', [SubAssyChecksheetController::class, 'exportPdf'])->name('admin.checksheets.export_pdf');
        Route::get('/report/checksheets/print', [SubAssyChecksheetController::class, 'printView'])->name('admin.checksheets.print');

        Route::post('/report/checksheets/sync', [SubAssyChecksheetController::class, 'syncToGoogleSheets'])->name('admin.checksheets.sync');
        Route::get('/report/plating-checksheets/daily-recap', [PlatingChecksheetController::class, 'dailyRecap'])->name('plating.daily_recap');
        Route::get('/report/plating-checksheets/export-pdf', [PlatingChecksheetController::class, 'exportPdf'])->name('plating.export_pdf');
        Route::get('/report/plating-checksheets/print', [PlatingChecksheetController::class, 'printView'])->name('plating.print');
        
        Route::get('/report/durability-plating-checksheets/daily-recap', [\App\Http\Controllers\DurabilityPlatingChecksheetController::class, 'dailyRecap'])->name('durability_plating.daily_recap');
        Route::get('/report/durability-plating-checksheets/export-pdf', [\App\Http\Controllers\DurabilityPlatingChecksheetController::class, 'exportPdf'])->name('durability_plating.export_pdf');
        Route::get('/report/durability-plating-checksheets/print', [\App\Http\Controllers\DurabilityPlatingChecksheetController::class, 'printView'])->name('durability_plating.print');

        Route::get('/report/painting-checksheets/daily-recap', [PaintingChecksheetController::class, 'dailyRecap'])->name('painting.daily_recap');
        Route::get('/report/painting-checksheets/export-pdf', [PaintingChecksheetController::class, 'exportPdf'])->name('painting.export_pdf');
        Route::get('/report/painting-checksheets/print', [PaintingChecksheetController::class, 'printView'])->name('painting.print');
        Route::get('/report/double-tape-checksheets/daily-recap', [DoubleTapeChecksheetController::class, 'dailyRecap'])->name('double_tape.daily_recap');
        Route::get('/report/double-tape-checksheets/export-pdf', [DoubleTapeChecksheetController::class, 'exportPdf'])->name('double_tape.export_pdf');
        Route::get('/report/double-tape-checksheets/print', [DoubleTapeChecksheetController::class, 'printView'])->name('double_tape.print');
        Route::get('/report/in-process-checksheets/export', [InProcessChecksheetController::class, 'export'])->name('in_process.export');
        Route::post('/report/in-process-checksheets/sync', [InProcessChecksheetController::class, 'syncToGoogleSheets'])->name('in_process.sync');
        Route::get('/report/first-piece-approvals/export', [FirstPieceApprovalController::class, 'export'])->name('first_piece_approval.export');
        Route::post('/report/first-piece-approvals/sync', [FirstPieceApprovalController::class, 'syncToGoogleSheets'])->name('first_piece_approval.sync');
        Route::get('/report/cross-cut-checksheets/export-pdf', [CrossCutChecksheetController::class, 'exportPdf'])->name('cross_cut.export_pdf');
        Route::get('/report/cross-cut-checksheets/print', [CrossCutChecksheetController::class, 'printView'])->name('cross_cut.print');
        Route::get('/report/cross-cut-painting-checksheets/export-pdf', [CrossCutPaintingChecksheetController::class, 'exportPdf'])->name('cross_cut_painting.export_pdf');
        Route::get('/report/cross-cut-painting-checksheets/print', [CrossCutPaintingChecksheetController::class, 'printView'])->name('cross_cut_painting.print');
        Route::get('/report/sortir-checksheets/export', [SortirChecksheetController::class, 'export'])->name('sortir.export');
        Route::get('/report/sortir-checksheets/export-pdf', [SortirChecksheetController::class, 'exportPdf'])->name('sortir.export_pdf');
        Route::get('/report/sortir-checksheets/print', [SortirChecksheetController::class, 'printView'])->name('sortir.print');

        // --- Incoming Routes (Reports & Export) ---
        Route::get('/report/incoming-part', [IncomingPartController::class, 'index'])->name('incoming.parts.index');
        Route::get('/report/incoming-material', [IncomingMaterialController::class, 'index'])->name('incoming.materials.index');
        Route::get('/report/incoming-sub-part', [IncomingSubPartController::class, 'index'])->name('incoming.sub_parts.index');
        Route::get('/report/incoming-export', [IncomingExportController::class, 'index'])->name('incoming.exports.index');
        Route::get('/report/incoming-chemical', [IncomingChemicalController::class, 'index'])->name('incoming.chemicals.index');
        Route::get('/report/incoming-part/export-pdf', [IncomingPartController::class, 'exportPdf'])->name('incoming.parts.export_pdf');
        Route::get('/report/incoming-material/export-pdf', [IncomingMaterialController::class, 'exportPdf'])->name('incoming.materials.export_pdf');
        Route::get('/report/incoming-sub-part/export-pdf', [IncomingSubPartController::class, 'exportPdf'])->name('incoming.sub_parts.export_pdf');
        Route::get('/report/incoming-export/export-pdf', [IncomingExportController::class, 'exportPdf'])->name('incoming.exports.export_pdf');
        Route::get('/report/incoming-chemical/export-pdf', [IncomingChemicalController::class, 'exportPdf'])->name('incoming.chemicals.export_pdf');

        // Approval Actions
        Route::post('/checksheets/{id}/approve/{type}', [SubAssyChecksheetController::class, 'approve'])->name('admin.checksheets.approve');
        Route::post('/checksheets/{id}/reject/{type}', [SubAssyChecksheetController::class, 'reject'])->name('admin.checksheets.reject');

        // Plating Approval
        Route::get('/report/plating-checksheets/{id}/edit-approval', [PlatingChecksheetController::class, 'editApproval'])->name('plating.edit_approval');
        Route::put('/report/plating-checksheets/{id}/update-approval', [PlatingChecksheetController::class, 'updateApproval'])->name('plating.update_approval');
        Route::post('/plating-checksheets/{id}/approve/{type}', [PlatingChecksheetController::class, 'approve'])->name('plating.approve');
        Route::post('/plating-checksheets/{id}/reject/{type}', [PlatingChecksheetController::class, 'reject'])->name('plating.reject');

        // Durability Plating Approval
        Route::get('/report/durability-plating-checksheets/{id}/edit-approval', [\App\Http\Controllers\DurabilityPlatingChecksheetController::class, 'editApproval'])->name('durability_plating.edit_approval');
        Route::put('/report/durability-plating-checksheets/{id}/update-approval', [\App\Http\Controllers\DurabilityPlatingChecksheetController::class, 'updateApproval'])->name('durability_plating.update_approval');
        Route::post('/durability-plating-checksheets/{id}/approve/{type}', [\App\Http\Controllers\DurabilityPlatingChecksheetController::class, 'approve'])->name('durability_plating.approve');
        Route::post('/durability-plating-checksheets/{id}/reject/{type}', [\App\Http\Controllers\DurabilityPlatingChecksheetController::class, 'reject'])->name('durability_plating.reject');

        // Painting Approval
        Route::get('/report/painting-checksheets/{id}/edit-approval', [PaintingChecksheetController::class, 'editApproval'])->name('painting.edit_approval');
        Route::put('/report/painting-checksheets/{id}/update-approval', [PaintingChecksheetController::class, 'updateApproval'])->name('painting.update_approval');
        Route::post('/painting-checksheets/{id}/approve/{type}', [PaintingChecksheetController::class, 'approve'])->name('painting.approve');
        Route::post('/painting-checksheets/{id}/reject/{type}', [PaintingChecksheetController::class, 'reject'])->name('painting.reject');

        // Double Tape Approval
        Route::get('/report/double-tape-checksheets/{id}/edit-approval', [DoubleTapeChecksheetController::class, 'editApproval'])->name('double_tape.edit_approval');
        Route::put('/report/double-tape-checksheets/{id}/update-approval', [DoubleTapeChecksheetController::class, 'updateApproval'])->name('double_tape.update_approval');
        Route::post('/double-tape-checksheets/{id}/approve/{type}', [DoubleTapeChecksheetController::class, 'approve'])->name('double_tape.approve');
        Route::post('/double-tape-checksheets/{id}/reject/{type}', [DoubleTapeChecksheetController::class, 'reject'])->name('double_tape.reject');
        Route::post('/in-process-checksheets/{id}/approve/{type}', [InProcessChecksheetController::class, 'approve'])->name('in_process.approve');
        Route::post('/in-process-checksheets/{id}/reject/{type}', [InProcessChecksheetController::class, 'reject'])->name('in_process.reject');
        Route::post('/first-piece-approvals/{id}/approve/{type}', [FirstPieceApprovalController::class, 'approve'])->name('first_piece_approval.approve');
        Route::post('/first-piece-approvals/{id}/reject/{type}', [FirstPieceApprovalController::class, 'reject'])->name('first_piece_approval.reject');
        Route::post('/cross-cut-checksheets/{id}/approve/{type}', [CrossCutChecksheetController::class, 'approve'])->name('cross_cut.approve');
        Route::post('/cross-cut-checksheets/{id}/reject/{type}', [CrossCutChecksheetController::class, 'reject'])->name('cross_cut.reject');
        Route::post('/cross-cut-painting-checksheets/{id}/approve/{type}', [CrossCutPaintingChecksheetController::class, 'approve'])->name('cross_cut_painting.approve');
        Route::post('/cross-cut-painting-checksheets/{id}/reject/{type}', [CrossCutPaintingChecksheetController::class, 'reject'])->name('cross_cut_painting.reject');
        Route::post('/sortir-checksheets/{id}/approve/{type}', [SortirChecksheetController::class, 'approve'])->name('sortir.approve');
        Route::post('/sortir-checksheets/{id}/reject/{type}', [SortirChecksheetController::class, 'reject'])->name('sortir.reject');

        // Bulk Approval Routes
        Route::post('/checksheets/bulk-approve', [SubAssyChecksheetController::class, 'bulkApprove'])->name('admin.checksheets.bulk_approve');
        Route::post('/in-process-checksheets/bulk-approve', [InProcessChecksheetController::class, 'bulkApprove'])->name('in_process.bulk_approve');
        Route::post('/cross-cut-checksheets/bulk-approve', [CrossCutChecksheetController::class, 'bulkApprove'])->name('cross_cut.bulk_approve');
        Route::post('/cross-cut-painting-checksheets/bulk-approve', [CrossCutPaintingChecksheetController::class, 'bulkApprove'])->name('cross_cut_painting.bulk_approve');
        Route::post('/sortir-checksheets/bulk-approve', [SortirChecksheetController::class, 'bulkApprove'])->name('sortir.bulk_approve');
        Route::post('/plating-checksheets/bulk-approve', [PlatingChecksheetController::class, 'bulkApprove'])->name('plating.bulk_approve');
        Route::post('/durability-plating-checksheets/bulk-approve', [\App\Http\Controllers\DurabilityPlatingChecksheetController::class, 'bulkApprove'])->name('durability_plating.bulk_approve');
        Route::post('/painting-checksheets/bulk-approve', [PaintingChecksheetController::class, 'bulkApprove'])->name('painting.bulk_approve');
        Route::post('/double-tape-checksheets/bulk-approve', [DoubleTapeChecksheetController::class, 'bulkApprove'])->name('double_tape.bulk_approve');
        Route::post('/first-piece-approvals/bulk-approve', [FirstPieceApprovalController::class, 'bulkApprove'])->name('first_piece_approval.bulk_approve');

        // --- Incoming Routes (Approval) ---
        Route::post('/incoming-part/{id}/approve/{type}', [IncomingPartController::class, 'approve'])->name('incoming.parts.approve');
        Route::post('/incoming-part/{id}/reject/{type}', [IncomingPartController::class, 'reject'])->name('incoming.parts.reject');
        Route::post('/incoming-material/{id}/approve/{type}', [IncomingMaterialController::class, 'approve'])->name('incoming.materials.approve');
        Route::post('/incoming-material/{id}/reject/{type}', [IncomingMaterialController::class, 'reject'])->name('incoming.materials.reject');
        Route::post('/incoming-sub-part/{id}/approve/{type}', [IncomingSubPartController::class, 'approve'])->name('incoming.sub_parts.approve');
        Route::post('/incoming-sub-part/{id}/reject/{type}', [IncomingSubPartController::class, 'reject'])->name('incoming.sub_parts.reject');
        Route::post('/incoming-export/{id}/approve/{type}', [IncomingExportController::class, 'approve'])->name('incoming.exports.approve');
        Route::post('/incoming-export/{id}/reject/{type}', [IncomingExportController::class, 'reject'])->name('incoming.exports.reject');
        Route::post('/incoming-chemical/{id}/approve/{type}', [IncomingChemicalController::class, 'approve'])->name('incoming.chemicals.approve');
        Route::post('/incoming-chemical/{id}/reject/{type}', [IncomingChemicalController::class, 'reject'])->name('incoming.chemicals.reject');

        // Edit/Update/Delete (General Management)
        Route::prefix('admin')->group(function () {
            // Sub Assy
            Route::get('checksheets/{checksheet}/edit', [SubAssyChecksheetController::class, 'edit'])->name('admin.checksheets.edit');
            Route::put('checksheets/{checksheet}', [SubAssyChecksheetController::class, 'update'])->name('admin.checksheets.update');
            Route::delete('checksheets/{checksheet}', [SubAssyChecksheetController::class, 'destroy'])->name('admin.checksheets.destroy');

            // Plating Edit/Update/Delete
            Route::get('plating-checksheets/{id}/edit', [PlatingChecksheetController::class, 'edit'])->name('plating.edit');
            Route::put('plating-checksheets/{id}', [PlatingChecksheetController::class, 'update'])->name('plating.update');
            Route::delete('plating-checksheets/{id}', [PlatingChecksheetController::class, 'destroy'])->name('plating.destroy');

            // Durability Plating Edit/Update/Delete
            Route::get('durability-plating-checksheets/{id}/edit', [\App\Http\Controllers\DurabilityPlatingChecksheetController::class, 'edit'])->name('durability_plating.edit');
            Route::put('durability-plating-checksheets/{id}', [\App\Http\Controllers\DurabilityPlatingChecksheetController::class, 'update'])->name('durability_plating.update');
            Route::delete('durability-plating-checksheets/{id}', [\App\Http\Controllers\DurabilityPlatingChecksheetController::class, 'destroy'])->name('durability_plating.destroy');

            // Painting Edit/Update/Delete
            Route::get('painting-checksheets/{id}/edit', [PaintingChecksheetController::class, 'edit'])->name('painting.edit');
            Route::put('painting-checksheets/{id}', [PaintingChecksheetController::class, 'update'])->name('painting.update');
            Route::delete('painting-checksheets/{id}', [PaintingChecksheetController::class, 'destroy'])->name('painting.destroy');

            // Double Tape Edit/Update/Delete
            Route::get('double-tape-checksheets/{id}/edit', [DoubleTapeChecksheetController::class, 'edit'])->name('double_tape.edit');
            Route::put('double-tape-checksheets/{id}', [DoubleTapeChecksheetController::class, 'update'])->name('double_tape.update');
            Route::delete('double-tape-checksheets/{id}', [DoubleTapeChecksheetController::class, 'destroy'])->name('double_tape.destroy');

            // In-Process
            Route::get('in-process-checksheets/{id}/edit', [InProcessChecksheetController::class, 'edit'])->name('in_process.edit');
            Route::put('in-process-checksheets/{id}', [InProcessChecksheetController::class, 'update'])->name('in_process.update');
            Route::delete('in-process-checksheets/{id}', [InProcessChecksheetController::class, 'destroy'])->name('in_process.destroy');

            // First Piece Approval
            Route::get('first-piece-approvals/{id}/edit', [FirstPieceApprovalController::class, 'edit'])->name('first_piece_approval.edit');
            Route::put('first-piece-approvals/{id}', [FirstPieceApprovalController::class, 'update'])->name('first_piece_approval.update');
            Route::delete('first-piece-approvals/{id}', [FirstPieceApprovalController::class, 'destroy'])->name('first_piece_approval.destroy');

            // Cross Cut
            Route::get('/cross-cut-checksheets/{id}/edit', [CrossCutChecksheetController::class, 'edit'])->name('cross_cut.edit');
            Route::put('/cross-cut-checksheets/{id}', [CrossCutChecksheetController::class, 'update'])->name('cross_cut.update');
            Route::delete('/cross-cut-checksheets/{id}', [CrossCutChecksheetController::class, 'destroy'])->name('cross_cut.destroy');

            // Cross Cut Painting
            Route::get('/cross-cut-painting-checksheets/{id}/edit', [CrossCutPaintingChecksheetController::class, 'edit'])->name('cross_cut_painting.edit');
            Route::put('/cross-cut-painting-checksheets/{id}', [CrossCutPaintingChecksheetController::class, 'update'])->name('cross_cut_painting.update');
            Route::delete('/cross-cut-painting-checksheets/{id}', [CrossCutPaintingChecksheetController::class, 'destroy'])->name('cross_cut_painting.destroy');

            // Sortir
            Route::get('/sortir-checksheets/{id}/edit', [SortirChecksheetController::class, 'edit'])->name('sortir.edit');
            Route::put('/sortir-checksheets/{id}', [SortirChecksheetController::class, 'update'])->name('sortir.update');
            Route::delete('/sortir-checksheets/{id}', [SortirChecksheetController::class, 'destroy'])->name('sortir.destroy');

            // --- Incoming Routes (Management) ---
            Route::get('incoming-part/{id}/edit', [IncomingPartController::class, 'edit'])->name('incoming.parts.edit');
            Route::put('incoming-part/{id}', [IncomingPartController::class, 'update'])->name('incoming.parts.update');
            Route::delete('incoming-part/{id}', [IncomingPartController::class, 'destroy'])->name('incoming.parts.destroy');
            Route::get('incoming-material/{id}/edit', [IncomingMaterialController::class, 'edit'])->name('incoming.materials.edit');
            Route::put('incoming-material/{id}', [IncomingMaterialController::class, 'update'])->name('incoming.materials.update');
            Route::delete('incoming-material/{id}', [IncomingMaterialController::class, 'destroy'])->name('incoming.materials.destroy');
            Route::get('incoming-sub-part/{id}/edit', [IncomingSubPartController::class, 'edit'])->name('incoming.sub_parts.edit');
            Route::put('incoming-sub-part/{id}', [IncomingSubPartController::class, 'update'])->name('incoming.sub_parts.update');
            Route::delete('incoming-sub-part/{id}', [IncomingSubPartController::class, 'destroy'])->name('incoming.sub_parts.destroy');
            Route::get('incoming-export/{id}/edit', [IncomingExportController::class, 'edit'])->name('incoming.exports.edit');
            Route::put('incoming-export/{id}', [IncomingExportController::class, 'update'])->name('incoming.exports.update');
            Route::delete('incoming-export/{id}', [IncomingExportController::class, 'destroy'])->name('incoming.exports.destroy');
            Route::get('incoming-chemical/{id}/edit', [IncomingChemicalController::class, 'edit'])->name('incoming.chemicals.edit');
            Route::put('incoming-chemical/{id}', [IncomingChemicalController::class, 'update'])->name('incoming.chemicals.update');
            Route::delete('incoming-chemical/{id}', [IncomingChemicalController::class, 'destroy'])->name('incoming.chemicals.destroy');
        });
    });

    // --- Admin-only Overrides ---
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('checksheets/{id}/edit-approval', [SubAssyChecksheetController::class, 'editApproval'])->name('checksheets.edit_approval');
        Route::put('checksheets/{id}/update-approval', [SubAssyChecksheetController::class, 'updateApproval'])->name('checksheets.update_approval');
        Route::get('in-process-checksheets/{id}/edit-approval', [InProcessChecksheetController::class, 'editApproval'])->name('in_process.edit_approval');
        Route::put('in-process-checksheets/{id}/update-approval', [InProcessChecksheetController::class, 'updateApproval'])->name('in_process.update_approval');
        Route::get('first-piece-approvals/{id}/edit-approval', [FirstPieceApprovalController::class, 'editApproval'])->name('first_piece_approval.edit_approval');
        Route::put('first-piece-approvals/{id}/update-approval', [FirstPieceApprovalController::class, 'updateApproval'])->name('first_piece_approval.update_approval');
        Route::get('cross-cut-checksheets/{id}/edit-approval', [CrossCutChecksheetController::class, 'editApproval'])->name('cross_cut.edit_approval');
        Route::put('cross-cut-checksheets/{id}/update-approval', [CrossCutChecksheetController::class, 'updateApproval'])->name('cross_cut.update_approval');
        Route::get('cross-cut-painting-checksheets/{id}/edit-approval', [CrossCutPaintingChecksheetController::class, 'editApproval'])->name('cross_cut_painting.edit_approval');
        Route::put('cross-cut-painting-checksheets/{id}/update-approval', [CrossCutPaintingChecksheetController::class, 'updateApproval'])->name('cross_cut_painting.update_approval');

        // --- Incoming Routes (Admin Overrides) ---
        Route::get('incoming-part/{id}/edit-approval', [IncomingPartController::class, 'editApproval'])->name('incoming.parts.edit_approval');
        Route::put('incoming-part/{id}/update-approval', [IncomingPartController::class, 'updateApproval'])->name('incoming.parts.update_approval');
        Route::get('incoming-material/{id}/edit-approval', [IncomingMaterialController::class, 'editApproval'])->name('incoming.materials.edit_approval');
        Route::put('incoming-material/{id}/update-approval', [IncomingMaterialController::class, 'updateApproval'])->name('incoming.materials.update_approval');
        Route::get('incoming-sub-part/{id}/edit-approval', [IncomingSubPartController::class, 'editApproval'])->name('incoming.sub_parts.edit_approval');
        Route::put('incoming-sub-part/{id}/update-approval', [IncomingSubPartController::class, 'updateApproval'])->name('incoming.sub_parts.update_approval');
        Route::get('incoming-export/{id}/edit-approval', [IncomingExportController::class, 'editApproval'])->name('incoming.exports.edit_approval');
        Route::put('incoming-export/{id}/update-approval', [IncomingExportController::class, 'updateApproval'])->name('incoming.exports.update_approval');
        Route::get('incoming-chemical/{id}/edit-approval', [IncomingChemicalController::class, 'editApproval'])->name('incoming.chemicals.edit_approval');
        Route::put('incoming-chemical/{id}/update-approval', [IncomingChemicalController::class, 'updateApproval'])->name('incoming.chemicals.update_approval');
    });
});
