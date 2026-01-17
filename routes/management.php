<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MonthlyReportController;

Route::middleware(['auth'])->group(function () {
    // Master Data Management (Admin & Staff)
    Route::middleware(['role:admin,supervisor,kashift,asst_manager,manager'])->prefix('admin')->name('admin.')->group(function () {
        // Items
        Route::resource('items', ItemController::class);

        // Categories
        Route::resource('categories', CategoryController::class);

        // Monthly Reports
        Route::resource('monthly-reports', MonthlyReportController::class);
        Route::post('monthly-reports/{id}/set-active', [MonthlyReportController::class, 'setActive'])->name('monthly_reports.set_active');
    });

    // Public/Shared Access to Master Files
    Route::get('items/{id}/pdf', [ItemController::class, 'servePdf'])->name('items.pdf');
    Route::get('monthly-reports/{id}/pdf', [MonthlyReportController::class, 'servePdf'])->name('monthly_reports.pdf');
});
