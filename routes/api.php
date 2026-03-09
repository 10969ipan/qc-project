<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\InProcessChecksheetApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    // API untuk cek status QC berdasarkan Unique Code ID
    // Dapat diakses oleh sistem Produksi atau Gudang
    Route::get('/qc-check/{unique_code_id}', [InProcessChecksheetApiController::class, 'checkStatus'])
        ->name('api.v1.in_process.check_status');
});
