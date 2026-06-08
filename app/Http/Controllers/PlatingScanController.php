<?php

namespace App\Http\Controllers;

use App\Models\PlatingPasangRecord;
use App\Models\PlatingCabutRecord;
use App\Models\PlatingCabutSplit;
use App\Models\PlatingChecksheet;
use App\Models\Plant;
use App\Helpers\ShiftHelper;
use Illuminate\Http\Request;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PlatingScanController extends Controller
{
    protected function restrictToKarawang()
    {
        $user = auth()->user();
        if ($user->role === 'admin') {
            return;
        }

        $plant = $user->plant;
        if (!$plant || strtolower($plant->code) !== 'karawang') {
            abort(403, 'Akses terbatas untuk Plant Karawang saja.');
        }
    }

    // --- PLATING PASANG ---

    public function pasangCreate()
    {
        $this->restrictToKarawang();
        $now = now();
        $defaultDate = ShiftHelper::getProductionDate($now);
        $defaultShift = ShiftHelper::getShift($now);

        return view('plating_scan.pasang', compact('defaultDate', 'defaultShift'));
    }

    public function pasangStore(Request $request)
    {
        $this->restrictToKarawang();

        $request->validate([
            'wip_qrcode' => 'required|string',
            'tanggal_pasang' => 'required|date',
            'shift' => 'required|string',
            'inisial_pasang' => 'required|string',
            'no_po' => 'required|string',
            'no_lot' => 'required|string',
            'qty' => 'required|integer|min:1',
        ]);

        $wipQr = trim($request->wip_qrcode);
        $parts = explode('|', $wipQr);

        if (count($parts) < 5) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['wip_qrcode' => 'Format QR WIP tidak valid. Minimal harus berisi 5 bagian terpisah karakter pipe (|).']);
        }

        $customerPart = trim($parts[0]);
        $noPo = trim($request->no_po);
        $qty = intval(trim($request->qty));
        $lotId = trim($parts[3]);
        $uniqueCode = trim($parts[4]);
        $sapCode = isset($parts[4]) ? trim($parts[4]) : null; 

        // Validasi sisa quantity WIP
        $originalQty = intval(trim($parts[2]));
        $usedQty = PlatingPasangRecord::where('wip_qrcode', $wipQr)->sum('qty');
        $remainingQty = max(0, $originalQty - $usedQty);

        if ($qty > $remainingQty) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['qty' => "Quantity input ({$qty}) melebihi sisa quantity WIP yang tersedia ({$remainingQty} pcs dari total {$originalQty} pcs)."]);
        }

        // Extract cavity from unique_code (format: line-cavity-counter, e.g. 7-02-0347)
        $uniqueParts = explode('-', $uniqueCode);
        $cav = (count($uniqueParts) >= 2) ? trim($uniqueParts[1]) : '01';

        // Extract individual initials and clean/normalize them
        $inputInitial = trim($request->inisial_pasang);
        preg_match_all('/[A-Za-z0-9]+/i', $inputInitial, $matches);
        $initialsArray = array_map('strtoupper', $matches[0] ?? []);
        
        if (empty($initialsArray)) {
            $displayInitials = strtoupper($inputInitial);
            $qrInitials = preg_replace('/[^A-Za-z0-9]/', '', $displayInitials);
        } else {
            $displayInitials = implode(' / ', $initialsArray);
            $qrInitials = implode('', $initialsArray);
        }

        // Format tanggal pasang segment: e.g. 04062026IPIOKL1 (dmY + operator + shift)
        $tanggalFormatted = date('dmY', strtotime($request->tanggal_pasang));
        $shiftVal = trim($request->shift);
        $tanggalPasangSegment = $tanggalFormatted . $qrInitials . $shiftVal;

        // Get next sequential JIG number for this production date and PO
        $lastRecordToday = PlatingPasangRecord::where('tanggal_pasang', $request->tanggal_pasang)
            ->where('no_po', trim($request->no_po))
            ->orderBy('id', 'desc')
            ->first();
        
        $nextJigNum = 1;
        if ($lastRecordToday && !empty($lastRecordToday->generated_qrcode)) {
            $qrParts = explode('|', $lastRecordToday->generated_qrcode);
            $lastJigString = end($qrParts); // JIG-XXX
            if (strpos($lastJigString, 'JIG-') === 0) {
                $lastNum = intval(substr($lastJigString, 4));
                $nextJigNum = $lastNum + 1;
            }
        }
        $jigCode = 'JIG-' . str_pad($nextJigNum, 3, '0', STR_PAD_LEFT);

        // Generate QR Pasang: Customer Part|No PO-No Lot|tanggal_pasang|qty|JIG-XXX
        $generatedQr = sprintf(
            "%s|%s-%s|%s|%s|%s",
            $customerPart,
            trim($request->no_po),
            trim($request->no_lot),
            $tanggalPasangSegment,
            $qty,
            $jigCode
        );

        $plantId = Plant::resolveId('karawang');

        $record = PlatingPasangRecord::create([
            'wip_qrcode' => $wipQr,
            'customer_part' => $customerPart,
            'no_po' => $noPo,
            'no_lot' => trim($request->no_lot),
            'qty' => $qty,
            'lot_id' => $lotId,
            'unique_code' => $uniqueCode,
            'sap_code' => $sapCode,
            'tanggal_pasang' => $request->tanggal_pasang,
            'shift' => $request->shift,
            'inisial_pasang' => $displayInitials,
            'generated_qrcode' => $generatedQr,
            'plant_id' => $plantId,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('plating_scan.pasang.create')
            ->with('print_pasang_id', $record->id);
    }

    public function getPasangData(Request $request)
    {
        $this->restrictToKarawang();
        $qr = $request->query('qr');
        $record = PlatingPasangRecord::where('generated_qrcode', $qr)->first();
        if ($record) {
            $tanggalFormatted = date('dmY', strtotime($record->tanggal_pasang));
            
            // Clean initials to alphanumeric only (e.g. "IP / IO / KL" -> "IPIOKL")
            preg_match_all('/[A-Za-z0-9]+/i', $record->inisial_pasang, $matches);
            $initialsArray = array_map('strtoupper', $matches[0] ?? []);
            $qrInitials = empty($initialsArray) 
                ? preg_replace('/[^A-Za-z0-9]/', '', strtoupper($record->inisial_pasang)) 
                : implode('', $initialsArray);

            $qrParts = explode('|', $record->generated_qrcode);
            $jigCode = end($qrParts);

            return response()->json([
                'success' => true,
                'customer_part' => $record->customer_part,
                'no_po' => $record->no_po,
                'no_lot' => $record->no_lot,
                'lot_id' => $record->lot_id,
                'qty' => $record->qty,
                'unique_code' => $record->unique_code,
                'sap_code' => $record->sap_code,
                'tanggal_pasang_formatted' => $tanggalFormatted,
                'inisial_pasang_combined' => $qrInitials,
                'shift' => $record->shift,
                'jig' => $jigCode
            ]);
        }
        return response()->json(['success' => false, 'message' => 'QR Plating Pasang tidak ditemukan di database.']);
    }

    public function getWipInfo(Request $request)
    {
        $this->restrictToKarawang();
        $qr = trim($request->query('qr'));

        if (empty($qr)) {
            return response()->json(['success' => false, 'message' => 'QR Code WIP kosong.']);
        }

        $parts = explode('|', $qr);
        if (count($parts) < 5) {
            return response()->json(['success' => false, 'message' => 'Format QR WIP tidak valid.']);
        }

        $customerPart = trim($parts[0]);
        $originalQty = intval(trim($parts[2]));

        // Hitung quantity yang sudah dipakai dari WIP QR ini
        $usedQty = PlatingPasangRecord::where('wip_qrcode', $qr)->sum('qty');
        $remainingQty = max(0, $originalQty - $usedQty);

        return response()->json([
            'success' => true,
            'customer_part' => $customerPart,
            'original_qty' => $originalQty,
            'used_qty' => $usedQty,
            'remaining_qty' => $remainingQty
        ]);
    }

    public function showPasangQr($id)
    {
        $this->restrictToKarawang();
        $record = PlatingPasangRecord::findOrFail($id);

        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel' => QRCode::ECC_H,
            'scale' => 5,
        ]);
        $qrCodeImage = (new QRCode($options))->render($record->generated_qrcode);

        $labels = [
            [
                'title' => 'LABEL PLATING - PASANG',
                'qr_image' => $qrCodeImage,
                'qr_text' => $record->generated_qrcode,
                'details' => [
                    'Part Code' => $record->customer_part,
                    'No PO' => $record->no_po,
                    'No Lot' => $record->no_lot ?: '-',
                    'Qty' => $record->qty . ' pcs',
                    'Tgl/Jam Pasang' => $record->tanggal_pasang->format('d/m/Y') . ' / ' . $record->created_at->format('H:i'),
                    'Operator/Shift' => $record->inisial_pasang . ' / ' . $record->shift,
                    'Unique Code' => explode('|', $record->generated_qrcode)[count(explode('|', $record->generated_qrcode))-1],
                ]
            ]
        ];

        return view('plating_scan.print_qr', compact('labels'));
    }

    // --- PLATING CABUT ---

    public function cabutCreate()
    {
        $this->restrictToKarawang();
        $now = now();
        $defaultDate = ShiftHelper::getProductionDate($now);
        $defaultShift = ShiftHelper::getShift($now);

        return view('plating_scan.cabut', compact('defaultDate', 'defaultShift'));
    }

    public function cabutStore(Request $request)
    {
        $this->restrictToKarawang();

        $request->validate([
            'pasang_qrcode' => 'required|string',
            'tanggal_cabut' => 'required|date',
            'shift' => 'required|string',
            'inisial_cabut' => 'required|string|max:50',
            'no_po' => 'required|string',
            'no_lot_original' => 'required|string',
            'qty_original' => 'required|integer',
            'splits' => 'required|array|min:1',
            'splits.*.no_lot_split' => 'required|string',
            'splits.*.qty_split' => 'required|integer|min:1',
        ]);

        $pasangQr = trim($request->pasang_qrcode);
        $pasangRecord = PlatingPasangRecord::where('generated_qrcode', $pasangQr)->first();

        if (!$pasangRecord) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['pasang_qrcode' => 'QR Plating Pasang tidak terdaftar di sistem. Anda harus melakukan proses Pasang terlebih dahulu.']);
        }

        // Validasi total quantity splits
        $totalSplitQty = array_sum(array_column($request->splits, 'qty_split'));
        if ($totalSplitQty > $request->qty_original) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['splits' => 'Total quantity bucket (' . $totalSplitQty . ') tidak boleh melebihi quantity original (' . $request->qty_original . ').']);
        }

        $plantId = Plant::resolveId('karawang');

        DB::beginTransaction();
        try {
            $cabutRecord = PlatingCabutRecord::create([
                'plating_pasang_record_id' => $pasangRecord->id,
                'pasang_qrcode' => $pasangQr,
                'tanggal_cabut' => $request->tanggal_cabut,
                'shift' => $request->shift,
                'no_po' => $request->no_po,
                'no_lot_original' => $request->no_lot_original,
                'qty_original' => $request->qty_original,
                'inisial_cabut' => strtoupper(trim($request->inisial_cabut)),
                'plant_id' => $plantId,
                'user_id' => Auth::id(),
            ]);

            $operatorClean = preg_replace('/[^A-Za-z0-9]/', '', $cabutRecord->inisial_cabut);
            $tglCabutFormatted = \Carbon\Carbon::parse($cabutRecord->tanggal_cabut)->format('dmY');
            $lotCabut = $tglCabutFormatted . $operatorClean . $cabutRecord->shift;

            // Get next sequential CBT number for this production date and PO
            $lastSplitToday = PlatingCabutSplit::whereHas('cabutRecord', function ($query) use ($request) {
                $query->where('tanggal_cabut', $request->tanggal_cabut)
                      ->where('no_po', trim($request->no_po));
            })
            ->orderBy('id', 'desc')
            ->first();

            $startCbtNum = 1;
            if ($lastSplitToday && !empty($lastSplitToday->generated_qrcode)) {
                $qrParts = explode('|', $lastSplitToday->generated_qrcode);
                $lastCbtString = end($qrParts); // CBT-XXX
                if (strpos($lastCbtString, 'CBT-') === 0) {
                    $lastNum = intval(substr($lastCbtString, 4));
                    $startCbtNum = $lastNum + 1;
                }
            }

            $index = $startCbtNum;
            foreach ($request->splits as $split) {
                $uniqueCodeSegment = 'CBT-' . str_pad($index, 3, '0', STR_PAD_LEFT);
                
                // Format QR Cabut: part_code|po|lot_cabut|qty_split|unique_code
                $generatedQr = sprintf(
                    "%s|%s|%s|%d|%s",
                    $pasangRecord->customer_part,
                    $cabutRecord->no_po,
                    $lotCabut,
                    intval($split['qty_split']),
                    $uniqueCodeSegment
                );

                PlatingCabutSplit::create([
                    'plating_cabut_record_id' => $cabutRecord->id,
                    'no_lot_split' => $lotCabut . '|' . $uniqueCodeSegment,
                    'qty_split' => intval($split['qty_split']),
                    'generated_qrcode' => $generatedQr,
                ]);
                $index++;
            }

            DB::commit();

            return redirect()->route('plating_scan.cabut.create')
                ->with('print_cabut_id', $cabutRecord->id);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
        }
    }

    public function showCabutQr($id)
    {
        $this->restrictToKarawang();
        $cabutRecord = PlatingCabutRecord::with('splits', 'pasangRecord')->findOrFail($id);

        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel' => QRCode::ECC_H,
            'scale' => 5,
        ]);

        $labels = [];
        foreach ($cabutRecord->splits as $split) {
            $qrCodeImage = (new QRCode($options))->render($split->generated_qrcode);
            $labels[] = [
                'title' => 'LABEL PLATING - CABUT',
                'qr_image' => $qrCodeImage,
                'qr_text' => $split->generated_qrcode,
                'details' => [
                    'Part Code' => $cabutRecord->pasangRecord->customer_part,
                    'No PO' => $cabutRecord->no_po,
                    'No Lot' => $cabutRecord->pasangRecord->no_lot ?: '-',
                    'Lot Pasang' => $split->no_lot_split,
                    'Tgl Pasang' => $cabutRecord->pasangRecord->tanggal_pasang->format('d/m/Y'),
                    'Qty' => $split->qty_split . ' pcs',
                    'Tgl/Jam Cabut' => $cabutRecord->tanggal_cabut->format('d/m/Y') . ' / ' . $cabutRecord->created_at->format('H:i'),
                    'Operator/Shift' => ($cabutRecord->inisial_cabut ?: '-') . ' / ' . $cabutRecord->shift,
                    'Unique Code' => explode('|', $split->generated_qrcode)[count(explode('|', $split->generated_qrcode))-1],
                ]
            ];
        }

        return view('plating_scan.print_qr', compact('labels'));
    }

    // --- RIWAYAT SCAN ---

    public function history()
    {
        $this->restrictToKarawang();
        $plantId = Plant::resolveId('karawang');

        $pasangRecords = PlatingPasangRecord::where('plant_id', $plantId)
            ->with('cabutRecord.splits')
            ->latest()
            ->paginate(5, ['*'], 'pasang_page');

        $cabutRecords = PlatingCabutRecord::where('plant_id', $plantId)
            ->with('splits', 'pasangRecord')
            ->latest()
            ->paginate(5, ['*'], 'cabut_page');

        return view('plating_scan.history', compact('pasangRecords', 'cabutRecords'));
    }

}
