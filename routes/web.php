<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ChecksheetController;
use App\Http\Controllers\InProcessChecksheetController;


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
            'TEST DATE', 'TEST TIME', 'TEST SHIFT', 'ITEM', 'PART', 'CUST', 
            100, 10, 90, 10, 'OK', 'TESTER', 'DEBUG ROW'
        ]);
        return "<h1>Success!</h1><p>Baris data dummy berhasil dikirim ke Google Sheet. Silakan cek spreadsheet Anda.</p>";
    } catch (\Exception $e) {
        return "<h1>Failed!</h1><p>Error: " . $e->getMessage() . "</p><p>Stack Trace: " . $e->getTraceAsString() . "</p>";
    }
});

// Rute Dashboard Utama (Untuk Semua Role)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // Rute Checksheet (Input) - Accessible by inspectors and admins? 
    // Assuming 'checksheet.sub_assy' is the input form.
    Route::get('/checksheet/sub-assy', [ChecksheetController::class, 'create'])->name('checksheet.sub_assy');
    
    // Rute Checksheet Inprocess (Input)
    Route::get('/checksheet/in-process', [InProcessChecksheetController::class, 'create'])->name('in_process.create');
    Route::post('/checksheet/in-process', [InProcessChecksheetController::class, 'store'])->name('in_process.store');

    // Rute Analis (Shared by Admin, Supervisor, Kashift, Asst. Manager)
    Route::middleware(['role:admin,supervisor,kashift,asst_manager'])->group(function() {
        Route::get('/analysis/monthly-ng-sub-assy', [App\Http\Controllers\AnalysisController::class, 'monthlyNgSubAssy'])->name('analysis.monthly_ng');
        Route::get('/analysis/monthly-ng-in-process', [App\Http\Controllers\AnalysisController::class, 'monthlyNgInProcess'])->name('analysis.monthly_ng_in_process');
    });
    Route::post('/checksheet/sub-assy', [ChecksheetController::class, 'store'])->name('checksheet.store');
});

// Rute Otentikasi
Route::get('login', [AuthController::class, 'login'])->name('login');
Route::post('login', [AuthController::class, 'authenticate'])->name('login.process');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');


// --- Rute Management (Master Data & Checksheet Actions) ---
// Akses: Admin, Supervisor, Kashift, Asst. Manager (Semua kecuali Inspector)
Route::middleware(['auth', 'role:admin,supervisor,kashift,asst_manager'])->prefix('admin')->name('admin.')->group(function () {
    // Manajemen Barang (Items)
    Route::resource('items', ItemController::class);

    // Laporan Checksheet (Edit/Delete)
    Route::get('checksheets/{checksheet}/edit', [ChecksheetController::class, 'edit'])->name('checksheets.edit');
    Route::put('checksheets/{checksheet}', [ChecksheetController::class, 'update'])->name('checksheets.update');
    Route::delete('checksheets/{checksheet}', [ChecksheetController::class, 'destroy'])->name('checksheets.destroy');

});

// Laporan Checksheet Inprocess (Edit/Delete) - Without 'admin.' name prefix
Route::middleware(['auth', 'role:admin,supervisor,kashift,asst_manager'])->prefix('admin')->group(function () {
    Route::get('in-process-checksheets/{id}/edit', [InProcessChecksheetController::class, 'edit'])->name('in_process.edit');
    Route::put('in-process-checksheets/{id}', [InProcessChecksheetController::class, 'update'])->name('in_process.update');
    Route::delete('in-process-checksheets/{id}', [InProcessChecksheetController::class, 'destroy'])->name('in_process.destroy');
});

// --- Rute Shared Read Access & Approval ---
Route::middleware(['auth'])->group(function () { 
    
    // View Report (Admin, Supervisor, Inspector, Kashift, Asst. Manager)
    Route::middleware(['role:admin,supervisor,inspector,kashift,asst_manager'])->group(function() {
         Route::get('/report/checksheets', [ChecksheetController::class, 'index'])->name('admin.checksheets.index');
         Route::get('/report/in-process-checksheets', [InProcessChecksheetController::class, 'index'])->name('in_process.index');
    });

    // Actions & Export (Admin, Supervisor, Kashift, Asst. Manager)
    Route::middleware(['role:admin,supervisor,kashift,asst_manager'])->group(function() {
         Route::get('/report/in-process-checksheets/export-pdf', [InProcessChecksheetController::class, 'exportPdf'])->name('in_process.export_pdf');
         Route::get('/report/checksheets/export', [ChecksheetController::class, 'export'])->name('admin.checksheets.export');
         Route::post('/report/checksheets/sync', [ChecksheetController::class, 'syncToGoogleSheets'])->name('admin.checksheets.sync');

         Route::get('/report/in-process-checksheets/export', [InProcessChecksheetController::class, 'export'])->name('in_process.export');
         Route::post('/report/in-process-checksheets/sync', [InProcessChecksheetController::class, 'syncToGoogleSheets'])->name('in_process.sync');

         // Approval Actions
         Route::post('/checksheets/{id}/approve/{type}', [ChecksheetController::class, 'approve'])->name('admin.checksheets.approve');
         Route::post('/checksheets/{id}/reject/{type}', [ChecksheetController::class, 'reject'])->name('admin.checksheets.reject');

         Route::post('/in-process-checksheets/{id}/approve/{type}', [InProcessChecksheetController::class, 'approve'])->name('in_process.approve');
         Route::post('/in-process-checksheets/{id}/reject/{type}', [InProcessChecksheetController::class, 'reject'])->name('in_process.reject');
    });
});

// --- Rute Khusus Inspector ---
Route::middleware(['auth', 'role:inspector'])->prefix('inspector')->group(function () {
    Route::get('report_qc', [ReportController::class, 'create'])->name('inspector.create_report');
    Route::post('report_qc', [ReportController::class, 'store'])->name('inspector.store_report');
});
