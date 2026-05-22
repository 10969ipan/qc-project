<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\InProcessChecksheetApiController;
use App\Http\Controllers\Api\MachineDashboardApiController;


Route::prefix('v1')->group(function () {
    // API: Cek status QC berdasarkan Unique Code ID
    // GET /api/v1/qc-check/{unique_code_id}
    Route::get('/qc-check/{unique_code_id}', [InProcessChecksheetApiController::class, 'checkStatus'])
        ->name('api.v1.in_process.check_status');

    
    // API: Dashboard Mesin Plant Karawang (Per Mesin)
    // GET /api/v1/machine-dashboard/{machine_number}
    // Query: ?plant=karawang  &shift=1  &date=2026-05-20
    Route::get('/machine-dashboard/{machine_number}', [MachineDashboardApiController::class, 'show'])
        ->name('api.v1.machine_dashboard.show')
        ->whereNumber('machine_number');
});
