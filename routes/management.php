<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MonthlyReportController;
use App\Http\Controllers\CustomerClaimController;
use App\Http\Controllers\CustomerClaimRecordController;

Route::middleware(['auth'])->group(function () {
    // Master Data Management (Admin & Staff)
    Route::middleware(['role:admin,supervisor,kashift,karu_qc,asst_manager,manager,oshef'])->prefix('admin')->name('admin.')->group(function () {
        // Items
        Route::delete('items/{id}/pdf/{index}', [ItemController::class, 'deletePdf'])->name('items.delete-pdf');
        Route::delete('items/{id}/pdf-similar', [ItemController::class, 'deleteSimilarPdf'])->name('items.delete-similar-pdf');
        Route::resource('items', ItemController::class)->except(['create']);

        // Categories
        Route::resource('categories', CategoryController::class)->except(['create']);

        // Settings UI Prototype
        Route::get('settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
        Route::get('settings/permissions', [\App\Http\Controllers\SettingsController::class, 'getPermissions'])->name('settings.permissions');
        
        // User Management CRUD
        Route::post('settings/users', [\App\Http\Controllers\SettingsController::class, 'storeUser'])->name('settings.users.store');
        Route::put('settings/users/{id}', [\App\Http\Controllers\SettingsController::class, 'updateUser'])->name('settings.users.update');
        Route::patch('settings/users/{id}/reset-password', [\App\Http\Controllers\SettingsController::class, 'resetPassword'])->name('settings.users.reset-password');
        Route::patch('settings/users/{id}/status', [\App\Http\Controllers\SettingsController::class, 'toggleUserStatus'])->name('settings.users.status');
        Route::get('settings/users/export', [\App\Http\Controllers\SettingsController::class, 'exportUsers'])->name('settings.users.export');
        Route::post('settings/users/import', [\App\Http\Controllers\SettingsController::class, 'importUsers'])->name('settings.users.import');
        Route::delete('settings/users/{id}', [\App\Http\Controllers\SettingsController::class, 'deleteUser'])->name('settings.users.delete');
        
        // Menu & Permissions Management
        Route::get('settings/menus/{id}', [\App\Http\Controllers\SettingsController::class, 'getMenuDetails'])->name('settings.menus.details');
        Route::put('settings/menus/{id}', [\App\Http\Controllers\SettingsController::class, 'updateMenuDetails'])->name('settings.menus.update');
        Route::post('settings/menus/order', [\App\Http\Controllers\SettingsController::class, 'updateMenuOrder'])->name('settings.menus.order');
        Route::post('settings/permissions', [\App\Http\Controllers\SettingsController::class, 'savePermissions'])->name('settings.permissions.save');
        
        // Activity Logs
        Route::get('settings/activity-logs', [\App\Http\Controllers\ActivityLogController::class, 'index'])->name('settings.activity_logs');
        
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
