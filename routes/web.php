<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\SubAssyChecksheetController;
use App\Http\Controllers\InProcessChecksheetController;
use App\Http\Controllers\CrossCutChecksheetController;
use App\Http\Controllers\MachineStatusController;

Route::post('/machine-status/update', [MachineStatusController::class, 'update'])->name('machine-status.update')->middleware('auth');
// Rute Default Landing Page
Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/dashboard');
    }
    // Jika belum login, tampilkan halaman login
    return view('auth.login');
});

// Helper route to run migrations
Route::get('/run-migration', function () {
    Artisan::call('migrate', ['--force' => true]);
    return nl2br(Artisan::output());
});

// Helper route to debug Google Sheets integration
Route::get('/debug-google-sheets', function () {
    try {
        $service = new \App\Services\GoogleSheetService();
        $service->appendRow([
            'TEST DATE',
            'TEST TIME',
            'TEST SHIFT',
            'ITEM',
            'PART',
            'CUST',
            100,
            10,
            90,
            10,
            'OK',
            'TESTER',
            'DEBUG ROW'
        ]);
        return "<h1>Success!</h1><p>Baris data dummy berhasil dikirim ke Google Sheet. Silakan cek spreadsheet Anda.</p>";
    } catch (\Exception $e) {
        return "<h1>Failed!</h1><p>Error: " . $e->getMessage() . "</p><p>Stack Trace: " . $e->getTraceAsString() . "</p>";
    }
});

// Rute Dashboard Utama (Untuk Semua Role)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // Rute Checksheet (Input) - Accessible by inspectors and admins? 
    // Assuming 'checksheet.sub_assy' is the input form.
    Route::get('/checksheet/sub-assy', [SubAssyChecksheetController::class, 'create'])->name('checksheet.sub_assy');

    // Rute Checksheet Inprocess (Input)
    Route::get('/checksheet/in-process', [InProcessChecksheetController::class, 'create'])->name('in_process.create');
    Route::post('/checksheet/in-process', [InProcessChecksheetController::class, 'store'])->name('in_process.store');

    // Rute Checksheet Cross Cut (Input)
    Route::get('/checksheet/cross-cut', [CrossCutChecksheetController::class, 'create'])->name('cross_cut.create');
    Route::post('/checksheet/cross-cut', [CrossCutChecksheetController::class, 'store'])->name('cross_cut.store');
    Route::get('/checksheet/cross-cut/{id}', [CrossCutChecksheetController::class, 'show'])->name('cross_cut.show');
    Route::get('/checksheet/cross-cut/{id}/image', [CrossCutChecksheetController::class, 'serveImage'])->name('cross_cut.image');
    Route::get('/cross_cut/{id}/data', [CrossCutChecksheetController::class, 'getData'])->name('cross_cut.data');

    // Rute Sortir (Input)
    Route::get('/checksheet/sortir', [App\Http\Controllers\SortirChecksheetController::class, 'create'])->name('sortir.create');
    Route::post('/checksheet/sortir', [App\Http\Controllers\SortirChecksheetController::class, 'store'])->name('sortir.store');


    // Rute Analis (Shared by Admin, Supervisor, Kashift, Asst. Manager)
    Route::middleware(['role:admin,supervisor,kashift,asst_manager,manager'])->group(function () {
        Route::get('/analysis/monthly-ng-sub-assy', [App\Http\Controllers\AnalysisController::class, 'monthlyNgSubAssy'])->name('analysis.monthly_ng');
        Route::get('/analysis/monthly-ng-in-process', [App\Http\Controllers\AnalysisController::class, 'monthlyNgInProcess'])->name('analysis.monthly_ng_in_process');
        Route::get('/analysis/monthly-ng-cross-cut', [App\Http\Controllers\AnalysisController::class, 'monthlyNgCrossCut'])->name('analysis.monthly_ng_cross_cut');
    });
    Route::post('/checksheet/sub-assy', [SubAssyChecksheetController::class, 'store'])->name('checksheet.store');
});

// Rute Otentikasi
Route::get('login', [AuthController::class, 'login'])->name('login');
Route::post('login', [AuthController::class, 'authenticate'])->name('login.process');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');


// --- Rute Management (Master Data & Checksheet Actions) ---
// Akses: Admin, Supervisor, Kashift, Asst. Manager (Semua kecuali Inspector)
Route::middleware(['auth', 'role:admin,supervisor,kashift,asst_manager,manager'])->prefix('admin')->name('admin.')->group(function () {
    // Manajemen Barang (Items)
    Route::resource('items', ItemController::class);
    // Route::get('items/{id}/pdf', [ItemController::class, 'servePdf'])->name('items.pdf');

    // Manajemen Kategori
    Route::resource('categories', App\Http\Controllers\CategoryController::class);

    // Manajemen Laporan Bulanan
    Route::resource('monthly-reports', App\Http\Controllers\MonthlyReportController::class);
    Route::post('monthly-reports/{id}/set-active', [App\Http\Controllers\MonthlyReportController::class, 'setActive'])->name('monthly_reports.set_active');

    // Laporan Checksheet (Edit/Delete)
    Route::get('checksheets/{checksheet}/edit', [SubAssyChecksheetController::class, 'edit'])->name('checksheets.edit');
    Route::put('checksheets/{checksheet}', [SubAssyChecksheetController::class, 'update'])->name('checksheets.update');
    Route::delete('checksheets/{checksheet}', [SubAssyChecksheetController::class, 'destroy'])->name('checksheets.destroy');
});

// New route for PDF access for all authenticated users
Route::middleware(['auth'])->get('items/{id}/pdf', [ItemController::class, 'servePdf'])->name('items.pdf');
Route::middleware(['auth'])->get('monthly-reports/{id}/pdf', [App\Http\Controllers\MonthlyReportController::class, 'servePdf'])->name('monthly_reports.pdf');

// --- Rute Khusus Admin ---
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Admin-only Approval Override for Sub Assy
    Route::get('checksheets/{id}/edit-approval', [SubAssyChecksheetController::class, 'editApproval'])->name('checksheets.edit_approval');
    Route::put('checksheets/{id}/update-approval', [SubAssyChecksheetController::class, 'updateApproval'])->name('checksheets.update_approval');

    // Admin-only Approval Override for In Process
    Route::get('in-process-checksheets/{id}/edit-approval', [InProcessChecksheetController::class, 'editApproval'])->name('in_process.edit_approval');
    Route::put('in-process-checksheets/{id}/update-approval', [InProcessChecksheetController::class, 'updateApproval'])->name('in_process.update_approval');

    // Admin-only Approval Override for Cross Cut
    Route::get('cross-cut-checksheets/{id}/edit-approval', [CrossCutChecksheetController::class, 'editApproval'])->name('cross_cut.edit_approval');
    Route::put('cross-cut-checksheets/{id}/update-approval', [CrossCutChecksheetController::class, 'updateApproval'])->name('cross_cut.update_approval');
});

// Laporan Checksheet Inprocess (Edit/Delete) - Without 'admin.' name prefix
Route::middleware(['auth', 'role:admin,supervisor,kashift,asst_manager,manager'])->prefix('admin')->group(function () {
    Route::get('in-process-checksheets/{id}/edit', [InProcessChecksheetController::class, 'edit'])->name('in_process.edit');
    Route::put('in-process-checksheets/{id}', [InProcessChecksheetController::class, 'update'])->name('in_process.update');
    Route::delete('in-process-checksheets/{id}', [InProcessChecksheetController::class, 'destroy'])->name('in_process.destroy');
});

// --- Rute Shared Read Access & Approval ---
Route::middleware(['auth'])->group(function () {

    // View Report (Admin, Supervisor, Inspector, Kashift, Asst. Manager, Manager, and New Roles)
    Route::middleware(['role:admin,supervisor,inspector,kashift,asst_manager,manager,karu_qc,kashift_plating,supervisor_plating,manager_plating'])->group(function () {
        Route::get('/report/checksheets', [SubAssyChecksheetController::class, 'index'])->name('admin.checksheets.index');
        Route::get('/report/in-process-checksheets', [InProcessChecksheetController::class, 'index'])->name('in_process.index');
        Route::get('/report/cross-cut-checksheets', [CrossCutChecksheetController::class, 'index'])->name('cross_cut.index');
        Route::get('/report/sortir-checksheets', [App\Http\Controllers\SortirChecksheetController::class, 'index'])->name('sortir.index');
    });

    // Actions & Export (Admin, Supervisor, Kashift, Asst. Manager, Manager, and New Roles)
    Route::middleware(['role:admin,supervisor,kashift,asst_manager,manager,karu_qc,kashift_plating,supervisor_plating,manager_plating'])->group(function () {
        Route::get('/report/in-process-checksheets/export-pdf', [InProcessChecksheetController::class, 'exportPdf'])->name('in_process.export_pdf');
        Route::get('/report/checksheets/export', [SubAssyChecksheetController::class, 'export'])->name('admin.checksheets.export');
        Route::post('/report/checksheets/sync', [SubAssyChecksheetController::class, 'syncToGoogleSheets'])->name('admin.checksheets.sync');

        Route::get('/report/in-process-checksheets/export', [InProcessChecksheetController::class, 'export'])->name('in_process.export');
        Route::post('/report/in-process-checksheets/sync', [InProcessChecksheetController::class, 'syncToGoogleSheets'])->name('in_process.sync');

        // Approval Actions
        Route::post('/checksheets/{id}/approve/{type}', [SubAssyChecksheetController::class, 'approve'])->name('admin.checksheets.approve');
        Route::post('/checksheets/{id}/reject/{type}', [SubAssyChecksheetController::class, 'reject'])->name('admin.checksheets.reject');

        Route::post('/in-process-checksheets/{id}/approve/{type}', [InProcessChecksheetController::class, 'approve'])->name('in_process.approve');
        Route::post('/in-process-checksheets/{id}/reject/{type}', [InProcessChecksheetController::class, 'reject'])->name('in_process.reject');

        Route::post('/cross-cut-checksheets/{id}/approve/{type}', [CrossCutChecksheetController::class, 'approve'])->name('cross_cut.approve');
        Route::post('/cross-cut-checksheets/{id}/reject/{type}', [CrossCutChecksheetController::class, 'reject'])->name('cross_cut.reject');

        // Cross Cut Edit, Update, Delete
        Route::get('/cross-cut-checksheets/{id}/edit', [CrossCutChecksheetController::class, 'edit'])->name('cross_cut.edit');
        Route::put('/cross-cut-checksheets/{id}', [CrossCutChecksheetController::class, 'update'])->name('cross_cut.update');
        Route::delete('/cross-cut-checksheets/{id}', [CrossCutChecksheetController::class, 'destroy'])->name('cross_cut.destroy');
        Route::get('/report/cross-cut-checksheets/export-pdf', [CrossCutChecksheetController::class, 'exportPdf'])->name('cross_cut.export_pdf');

        // Sortir Approval, Rejection, Edit, Update, Delete, Export
        Route::post('/sortir-checksheets/{id}/approve/{type}', [App\Http\Controllers\SortirChecksheetController::class, 'approve'])->name('sortir.approve');
        Route::post('/sortir-checksheets/{id}/reject/{type}', [App\Http\Controllers\SortirChecksheetController::class, 'reject'])->name('sortir.reject');
        Route::get('/sortir-checksheets/{id}/edit', [App\Http\Controllers\SortirChecksheetController::class, 'edit'])->name('sortir.edit');
        Route::put('/sortir-checksheets/{id}', [App\Http\Controllers\SortirChecksheetController::class, 'update'])->name('sortir.update');
        Route::delete('/sortir-checksheets/{id}', [App\Http\Controllers\SortirChecksheetController::class, 'destroy'])->name('sortir.destroy');
        Route::get('/report/sortir-checksheets/export', [App\Http\Controllers\SortirChecksheetController::class, 'export'])->name('sortir.export');
    });
});


