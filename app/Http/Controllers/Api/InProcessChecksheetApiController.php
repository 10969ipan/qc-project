<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InProcessChecksheet;
use Illuminate\Http\Request;

class InProcessChecksheetApiController extends Controller
{
    /**
     * 
     * 
     * @param string $unique_code_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkStatus($unique_code_id)
    {
        $sap_code = null;
        // Jika parameter yang dikirim adalah string QR lengkap, ambil unique_code_id dan sap_code jika ada
        if (strpos($unique_code_id, '|') !== false) {
            $parts = explode('|', $unique_code_id);
            $count = count($parts);
            if ($count >= 4) {
                $unique_code_id = trim($parts[3]);
            }
            if ($count >= 5) {
                $sap_code = trim($parts[$count - 1]);
            }
        }

        // Daftar model yang akan dicek secara berurutan
        $modelsToCheck = [
            'In-Process' => \App\Models\InProcessChecksheet::class,
            'Sub Assy' => \App\Models\SubAssyChecksheet::class,
            'Double Tape' => \App\Models\DoubleTapeChecksheet::class,
            'Plating' => \App\Models\PlatingChecksheet::class,
            'Painting' => \App\Models\PaintingChecksheet::class,
            'Incoming Export' => \App\Models\IncomingExport::class,
            'Incoming Part' => \App\Models\IncomingPart::class,
        ];

        $foundChecksheet = null;
        $processType = '';

        foreach ($modelsToCheck as $processName => $modelClass) {
            if (class_exists($modelClass)) {
                $tableName = (new $modelClass)->getTable();
                if (\Illuminate\Support\Facades\Schema::hasColumn($tableName, 'unique_code_id')) {
                    $query = $modelClass::withoutGlobalScopes()
                        ->where('unique_code_id', $unique_code_id);

                    if ($sap_code && \Illuminate\Support\Facades\Schema::hasColumn($tableName, 'sap_code')) {
                        $query->where('sap_code', $sap_code);
                    }

                    $checksheet = $query->with(['item'])
                        ->latest()
                        ->first();
                        
                    if ($checksheet) {
                        $foundChecksheet = $checksheet;
                        $processType = $processName;
                        break; // Berhenti mencari jika sudah ketemu
                    }
                }
            }
        }

        if (!$foundChecksheet) {
            return response()->json([
                'success' => false,
                'message' => 'Part pada unique code ini belum melewati tahapan QC manapun.',
                'unique_code_id' => $unique_code_id,
                'status_qc' => 'PENDING'
            ], 404);
        }

        if (strtoupper($foundChecksheet->judgment) !== 'OK') {
            return response()->json([
                'success' => false,
                'message' => 'Part ini memiliki judgment NG atau Belum OK. Tidak dapat diproses lebih lanjut.',
                'unique_code_id' => $unique_code_id,
                'status_qc' => 'REJECTED',
                'judgment' => $foundChecksheet->judgment
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data QC ditemukan.',
            'data' => [
                'unique_code_id' => $foundChecksheet->unique_code_id,
                'process_type' => $processType,
                'part_name' => $foundChecksheet->item ? $foundChecksheet->item->name : 'N/A',
                'part_code' => $foundChecksheet->part_code,
                'judgment' => $foundChecksheet->judgment, 
                'inspector_initials' => $foundChecksheet->operator_initials,
                'qc_date' => $foundChecksheet->date,
                'shift' => $foundChecksheet->shift,
                'status_qc' => 'COMPLETED'
            ]
        ], 200);
    }
}
