<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\InProcessChecksheetApiController;
use App\Http\Controllers\Api\MachineDashboardApiController;


Route::prefix('v1')->group(function () {
    Route::get('/qc-check/{unique_code_id}', [InProcessChecksheetApiController::class, 'checkStatus'])
        ->name('api.v1.in_process.check_status');
    //// GET https://192.168.0.39/api/v1/machine-dashboard/{machine_number}
    Route::get('/machine-dashboard/{machine_number}', [MachineDashboardApiController::class, 'show'])
        ->name('api.v1.machine_dashboard.show')
        ->whereNumber('machine_number');
});
