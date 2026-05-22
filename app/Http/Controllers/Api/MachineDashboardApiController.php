<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InProcessChecksheet;
use App\Models\FirstPieceApproval;
use App\Models\Plant;
use App\Helpers\ShiftHelper;
use Illuminate\Http\Request;

/**
 * API Controller — Dashboard Mesin Plant Karawang
 * Mengembalikan part item dan part number per nomor mesin.
 */
class MachineDashboardApiController extends Controller
{
    /**
     * GET /api/v1/machine-dashboard/{machine_number}
     *
     * Mengembalikan Part Item dan Part No untuk satu nomor mesin.
     *
     * Route params:
     *   - machine_number : int (nomor mesin, contoh: 1, 5, 11)
     *
     * @param  Request $request
     * @param  int     $machineNumber
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, int $machineNumber)
    {
        $plantIdentifier = $request->input('plant', 'karawang');
        $plantId         = Plant::resolveId($plantIdentifier);
        $plantName       = Plant::resolveName($plantIdentifier);

        if (!$plantId) {
            return response()->json([
                'success' => false,
                'message' => "Plant '{$plantIdentifier}' tidak ditemukan.",
            ], 404);
        }

        $now            = now();
        $productionDate = $request->input('date', ShiftHelper::getProductionDate($now));
        $currentShift   = $request->input('shift', ShiftHelper::getShift($now));

        // Cari di In-Process Checksheet dahulu
        $record = InProcessChecksheet::with('item:id,name,part_number,sap_code')
            ->withoutGlobalScope('plant')
            ->where('plant_id', $plantId)
            ->where('date', $productionDate)
            ->where('shift', $currentShift)
            ->where('code_machine', $machineNumber)
            ->latest()
            ->first();

        // Jika tidak ada, cari di First Piece Approval
        if (!$record) {
            $record = FirstPieceApproval::with('item:id,name,part_number,sap_code')
                ->withoutGlobalScope('plant')
                ->where('plant_id', $plantId)
                ->where('date', $productionDate)
                ->where('shift', $currentShift)
                ->where('code_machine', $machineNumber)
                ->latest()
                ->first();
        }

        if (!$record) {
            return response()->json([
                'success'        => false,
                'message'        => "Mesin {$machineNumber} tidak memiliki data pada shift {$currentShift} tanggal {$productionDate} di plant {$plantName}.",
                'machine_number' => $machineNumber,
                'plant'          => $plantName,
                'date'           => $productionDate,
                'shift'          => $currentShift,
            ], 404);
        }

        $item = $record->item;

        return response()->json([
            'success'        => true,
            'plant'          => $plantName,
            'machine_number' => $machineNumber,
            'date'           => $productionDate,
            'shift'          => $currentShift,
            'part_item'      => $item ? $item->name        : null,
            'part_no'        => $item ? $item->part_number  : null,
            'sap_code'       => $item ? $item->sap_code     : null,
            'total_qty'      => (int) ($record->total_qty ?? 0),
            'judgment'       => $record->judgment,
            'source'         => $record->getTable() === 'in_process_checksheets'
                ? 'in_process'
                : 'first_piece_approval',
            'updated_at'     => optional($record->created_at)->format('Y-m-d H:i:s'),
        ]);
    }
}
