<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MonthlyReportController;
use App\Http\Controllers\CustomerClaimController;
use App\Http\Controllers\CustomerClaimRecordController;

Route::middleware(['auth'])->group(function () {
    // Master Data Management (Admin & Staff)
    Route::middleware(['role:admin,supervisor,kashift,asst_manager,manager,oshef'])->prefix('admin')->name('admin.')->group(function () {
        // Items
        Route::delete('items/{id}/pdf/{index}', [ItemController::class, 'deletePdf'])->name('items.delete-pdf');
        Route::delete('items/{id}/pdf-similar', [ItemController::class, 'deleteSimilarPdf'])->name('items.delete-similar-pdf');
        Route::resource('items', ItemController::class)->except(['create']);

        // Categories
        Route::resource('categories', CategoryController::class)->except(['create']);


        // Monthly Reports
        Route::resource('monthly-reports', MonthlyReportController::class);
        Route::post('monthly-reports/{id}/set-active', [MonthlyReportController::class, 'setActive'])->name('monthly_reports.set_active');

        // Customer Claims

        Route::resource('customer-claims', CustomerClaimController::class)->except(['create', 'edit']);

        // Customer Claim Records (Detailed List)
        Route::get('customer-claim-records-export', [CustomerClaimRecordController::class, 'exportPdf'])->name('customer-claim-records.export');
        Route::delete('customer-claim-records/{id}/attachment/{index}', [CustomerClaimRecordController::class, 'deleteAttachment'])->name('customer-claim-records.attachment.destroy');
        Route::resource('customer-claim-records', CustomerClaimRecordController::class)->names('customer-claim-records');
    });

    // Public/Shared Access to Master Files
    Route::get('items/search-by-part', [ItemController::class, 'searchByPartNumber'])->name('items.search-by-part');
    Route::get('items/{id}/pdf/{index?}', [ItemController::class, 'servePdf'])->name('items.pdf');

    Route::get('monthly-reports/{id}/pdf', [MonthlyReportController::class, 'servePdf'])->name('monthly_reports.pdf');
});
