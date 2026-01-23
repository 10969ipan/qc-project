<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MonthlyReportController;
use App\Http\Controllers\CustomerClaimController;

Route::middleware(['auth'])->group(function () {
    // Master Data Management (Admin & Staff)
    Route::middleware(['role:admin,supervisor,kashift,asst_manager,manager'])->prefix('admin')->name('admin.')->group(function () {
        // Items
        Route::resource('items', ItemController::class)->except(['create']);
        Route::delete('items/{id}/pdf/{index}', [ItemController::class, 'deletePdf'])->name('items.delete-pdf');

        // Categories
        Route::resource('categories', CategoryController::class)->except(['create']);


        // Monthly Reports
        Route::resource('monthly-reports', MonthlyReportController::class);
        Route::post('monthly-reports/{id}/set-active', [MonthlyReportController::class, 'setActive'])->name('monthly_reports.set_active');

        // Customer Claims
        Route::get('customer-claims/yearly', [CustomerClaimController::class, 'yearly'])->name('customer-claims.yearly');
        Route::post('customer-claims/yearly', [CustomerClaimController::class, 'storeYearly'])->name('customer-claims.store-yearly');
        Route::resource('customer-claims', CustomerClaimController::class)->except(['create', 'edit']);
    });

    // Public/Shared Access to Master Files
    Route::get('items/{id}/pdf/{index?}', [ItemController::class, 'servePdf'])->name('items.pdf');

    Route::get('monthly-reports/{id}/pdf', [MonthlyReportController::class, 'servePdf'])->name('monthly_reports.pdf');
});
