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
        // Cari data checksheet berdasarkan unique_code_id
        $checksheet = InProcessChecksheet::where('unique_code_id', $unique_code_id)
            ->with(['item']) // Load data item jika perlu rincian part
            ->latest()
            ->first();

        if (!$checksheet) {
            return response()->json([
                'success' => false,
                'message' => 'Part pada unique code ini belum di QC',
                'unique_code_id' => $unique_code_id,
                'status_qc' => 'PENDING'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data QC ditemukan.',
            'data' => [
                'unique_code_id' => $checksheet->unique_code_id,
                'part_name' => $checksheet->item ? $checksheet->item->name : 'N/A',
                'part_code' => $checksheet->part_code,
                'judgment' => $checksheet->judgment, 
                'inspector_initials' => $checksheet->operator_initials,
                'qc_date' => $checksheet->date,
                'shift' => $checksheet->shift,
                'status_qc' => 'COMPLETED'
            ]
        ], 200);
    }
}
