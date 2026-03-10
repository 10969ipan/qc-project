<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\InProcessChecksheetApiController;


Route::prefix('v1')->group(function () {
    // API untuk cek status QC berdasarkan Unique Code ID
    // Dapat diakses oleh sistem Produksi atau Gudang FG
    Route::get('/qc-check/{unique_code_id}', [InProcessChecksheetApiController::class, 'checkStatus'])
        ->name('api.v1.in_process.check_status');
});
