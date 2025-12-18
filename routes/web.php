<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ChecksheetController;


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

    // Rute Analis (Shared by Admin, Supervisor, Kashift, Asst. Manager)
    Route::middleware(['role:admin,supervisor,kashift,asst_manager'])->group(function() {
        Route::get('/analysis/monthly-ng-sub-assy', [App\Http\Controllers\AnalysisController::class, 'monthlyNgSubAssy'])->name('analysis.monthly_ng');
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

// --- Rute Shared Read Access & Approval ---
Route::middleware(['auth'])->group(function () { 
    
    // View Report (Admin, Supervisor, Inspector, Kashift, Asst. Manager)
    Route::middleware(['role:admin,supervisor,inspector,kashift,asst_manager'])->group(function() {
         Route::get('/report/checksheets', [ChecksheetController::class, 'index'])->name('admin.checksheets.index');
    });

    // Actions & Export (Admin, Supervisor, Kashift, Asst. Manager)
    Route::middleware(['role:admin,supervisor,kashift,asst_manager'])->group(function() {
         Route::get('/report/checksheets/export', [ChecksheetController::class, 'export'])->name('admin.checksheets.export');
         Route::post('/report/checksheets/sync', [ChecksheetController::class, 'syncToGoogleSheets'])->name('admin.checksheets.sync');

         // Approval Actions
         Route::post('/checksheets/{id}/approve/{type}', [ChecksheetController::class, 'approve'])->name('admin.checksheets.approve');
         Route::post('/checksheets/{id}/reject/{type}', [ChecksheetController::class, 'reject'])->name('admin.checksheets.reject');
    });
});

// --- Rute Khusus Inspector ---
Route::middleware(['auth', 'role:inspector'])->prefix('inspector')->group(function () {
    Route::get('report_qc', [ReportController::class, 'create'])->name('inspector.create_report');
    Route::post('report_qc', [ReportController::class, 'store'])->name('inspector.store_report');
});
