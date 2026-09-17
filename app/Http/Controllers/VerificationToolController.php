<?php

namespace App\Http\Controllers;

use App\Models\VerificationTool;
use App\Models\VerificationSchedule;
use App\Models\VerificationVerification;
use App\Models\VerificationToolLog;
use App\Models\Plant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Helpers\ActivityLogger;
use Carbon\Carbon;

class VerificationToolController extends Controller
{
    public function scheduleIndex(Request $request)
    {
        $plantCode = $request->input('plant', auth()->user()->plant ? auth()->user()->plant->code : 'jakarta');
        $plant = Plant::where('code', $plantCode)->first();

        $year = $request->input('year', date('Y'));
        
        $query = VerificationTool::where('plant_id', $plant->id)
            ->with(['schedules' => function($q) use ($year) {
                $q->where('year', $year);
            }])
            ->where('status', '!=', 'BROKEN');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name_part', 'LIKE', "%{$search}%")
                  ->orWhere('no_part', 'LIKE', "%{$search}%")
                  ->orWhere('customer', 'LIKE', "%{$search}%");
            });
        }

        $tools = $query->get();

        return view('verifications.schedule.index', compact('tools', 'plantCode', 'year'));
    }

    public function toolsIndex(Request $request)
    {
        $plantCode = $request->input('plant', auth()->user()->plant ? auth()->user()->plant->code : 'jakarta');
        $plant = Plant::where('code', $plantCode)->first();

        // Dropdown options for filters
        $toolTypes = VerificationTool::where('plant_id', $plant->id)->whereNotNull('tool_type')->distinct()->pluck('tool_type');
        $customers = VerificationTool::where('plant_id', $plant->id)->whereNotNull('customer')->distinct()->pluck('customer');
        $verificationTypes = ['INTERNAL', 'EXTERNAL'];

        $query = VerificationTool::where('plant_id', $plant->id)
            ->withCount([
                'verifications',
                'schedules as actual_schedules_count' => function($q) {
                    $q->whereNotNull('actual_status');
                }
            ]);

        // Apply filters matching the Checkshet/In-Process logic style
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name_part', 'LIKE', "%{$search}%")
                  ->orWhere('no_part', 'LIKE', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('tool_type')) {
            $query->where('tool_type', $request->tool_type);
        }
        if ($request->filled('customer')) {
            $query->where('customer', $request->customer);
        }
        if ($request->filled('verification_type')) {
            $query->where('verification_type', $request->verification_type);
        }
        if ($request->filled('drawing')) {
            $query->where('drawing', $request->drawing);
        }
        if ($request->filled('judgment')) {
            $query->where(function($q) use ($request) {
                if ($request->judgment === 'BELUM') {
                    $q->whereNull('tool_judgment');
                } else {
                    $q->where('tool_judgment', $request->judgment);
                }
            });
        }
        if ($request->filled('tool_status')) {
            $query->where('tool_status', $request->tool_status);
        }

        $tools = $query->orderBy('name_part')->paginate(10)->appends($request->all());

        // Get filter options (Dynamic)
        $toolTypes = VerificationTool::where('plant_id', $plant->id)->distinct()->pluck('tool_type')->filter()->values();
        $customers = VerificationTool::where('plant_id', $plant->id)->distinct()->pluck('customer')->filter()->values();
        $verificationTypes = ['INTERNAL', 'EXTERNAL'];
        $drawings = ['ADA', 'TIDAK ADA'];
        $judgments = ['BELUM', 'OK', 'NG'];
        $statuses = ['AKTIF', 'TIDAK AKTIF'];

        return view('verifications.tools.index', compact(
            'tools', 'plantCode', 'toolTypes', 'customers', 
            'verificationTypes', 'drawings', 'judgments', 'statuses'
        ));
    }

    public function toolsStore(Request $request)
    {
        $request->validate([
            'plant' => 'required|string',
            'name_part' => 'required|string',
            'no_part' => 'required|string',
            'part_code' => 'nullable|string',
            'tool_type' => 'required|string',
            'customer' => 'nullable|string',
            'quantity' => 'nullable|integer',
            'verification_frequency' => 'nullable|string',
            'planned_verification_date' => 'nullable|date',
            'verification_type' => 'nullable|string',
            'drawing_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $plant = Plant::where('code', $request->plant)->first();

        $data = $request->except(['drawing_file']);
        if ($request->hasFile('drawing_file')) {
            $file = $request->file('drawing_file');
            $fileName = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
            $destinationPath = public_path('uploads/drawings');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            $file->move($destinationPath, $fileName);
            $data['drawing_path'] = 'uploads/drawings/' . $fileName;
            $data['drawing'] = 'ADA';
        }

        if ($request->has('dimension_standards')) {
            $ds = $request->input('dimension_standards');
            if (is_string($ds)) {
                $ds = json_decode($ds, true);
            }
            $data['dimension_standards'] = $ds;
        }

        $tool = VerificationTool::create(array_merge($data, ['plant_id' => $plant->id]));
        
        if ($request->filled('planned_verification_date')) {
            $this->syncPlannedDateSchedule($tool, $request->planned_verification_date);
        }

        ActivityLogger::log('created', $tool, "Menambahkan Master Data Alat Verifikasi: {$tool->name_part}");

        return redirect()->back()->with('success', 'Data alat verifikasi berhasil disimpan.');
    }

    public function toolsEdit($id)
    {
        $tool = VerificationTool::findOrFail($id);
        $toolArray = $tool->toArray();
        $toolArray['drawing_url'] = $tool->drawing_path ? asset($tool->drawing_path) : null;
        return response()->json($toolArray);
    }

    public function toolsUpdate(Request $request, $id)
    {
        $tool = VerificationTool::findOrFail($id);

        $data = $request->except(['drawing_file']);
        if ($request->hasFile('drawing_file')) {
            $file = $request->file('drawing_file');
            $fileName = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
            $destinationPath = public_path('uploads/drawings');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            $file->move($destinationPath, $fileName);
            $data['drawing_path'] = 'uploads/drawings/' . $fileName;
            $data['drawing'] = 'ADA';
        }

        if ($request->has('dimension_standards')) {
            $ds = $request->input('dimension_standards');
            if (is_string($ds)) {
                $ds = json_decode($ds, true);
            }
            $data['dimension_standards'] = $ds;
        }

        $tool->update($data);
        
        if ($request->filled('planned_verification_date')) {
            $this->syncPlannedDateSchedule($tool, $request->planned_verification_date);
        }

        ActivityLogger::log('updated', $tool, "Memperbarui Master Data Alat Verifikasi: {$tool->name_part}");

        return redirect()->back()->with('success', 'Data alat verifikasi berhasil diperbarui.');
    }

    public function getVerificationData($id)
    {
        $tool = VerificationTool::findOrFail($id);

        $dimensions = [];
        if (!empty($tool->dimension_standards) && is_array($tool->dimension_standards)) {
            foreach ($tool->dimension_standards as $idx => $dim) {
                $std = trim($dim['standard'] ?? '');
                $tol = trim($dim['tolerance'] ?? '');
                
                $min = null;
                $max = null;
                
                $stdNum = is_numeric($std) ? (float)$std : null;
                
                // Format 1: Range "23.10 - 23.90"
                if (preg_match('/^\s*([0-9.]+)\s*-\s*([0-9.]+)\s*$/', $tol, $m)) {
                    $min = (float)$m[1];
                    $max = (float)$m[2];
                }
                // Format 2: Asymmetric "+0.1/-0.2" or "+0.2 / -0.4" or "+0/-0.6" or "+0.21/-0.6"
                elseif (preg_match('/^\s*([+-]?[0-9.]+)\s*\/\s*([+-]?[0-9.]+)\s*$/', $tol, $m) && $stdNum !== null) {
                    $v1 = (float)$m[1];
                    $v2 = (float)$m[2];
                    $upper = max($v1, $v2);
                    $lower = min($v1, $v2);
                    $min = $stdNum + $lower;
                    $max = $stdNum + $upper;
                }
                // Format 3: Symmetric "±0.4" or "0.4"
                elseif (preg_match('/^\s*±?\s*([0-9.]+)\s*$/', $tol, $m) && $stdNum !== null) {
                    $tolVal = (float)$m[1];
                    $min = $stdNum - $tolVal;
                    $max = $stdNum + $tolVal;
                }
                elseif (isset($dim['min']) && isset($dim['max']) && $dim['min'] !== '' && $dim['max'] !== '') {
                    $min = (float)$dim['min'];
                    $max = (float)$dim['max'];
                }

                $dimensions[] = [
                    "point" => "Point " . ($idx + 1),
                    "standard" => $std ?: '-',
                    "tolerance" => $tol ?: '-',
                    "min" => $min,
                    "max" => $max,
                ];
            }
        }

        return response()->json([
            'id' => $tool->id,
            'name_part' => $tool->name_part,
            'no_part' => $tool->no_part, // Model
            'tool_type' => $tool->tool_type ?? 'Alat',
            'part_code' => $tool->part_code ?? '-',
            'customer' => $tool->customer ?? 'PT.AHM',
            'quantity' => $tool->quantity ?? 1,
            'verification_frequency' => $tool->verification_frequency ?? '1 Tahun',
            'planned_verification_date' => $tool->planned_verification_date ?? date('Y-m-d'),
            'drawing' => $tool->drawing ?? 'ADA',
            'drawing_url' => $tool->drawing_path ? asset($tool->drawing_path) : null,
            'dimensions' => $dimensions,
        ]);
    }

    public function storeVerification(Request $request, $id)
    {
        $request->validate([
            'tanggal_verifikasi' => 'required|date',
            'judgment' => 'required|string',
            'remarks' => 'nullable|string',
            'next_verifikasi' => 'nullable|date',
        ]);

        $tool = VerificationTool::findOrFail($id);

        // 1. Create verification record in verification_verifications
        $verif = VerificationVerification::create([
            'tool_id' => $tool->id,
            'name_part' => $tool->name_part,
            'no_part' => $tool->no_part,
            'tanggal_verifikasi' => $request->tanggal_verifikasi,
            'next_verifikasi' => $request->next_verifikasi ?? $tool->planned_verification_date,
            'judgment' => $request->judgment,
            'remarks' => $request->remarks,
            'plant_id' => $tool->plant_id,
        ]);

        // 2. Update tool judgment & next planned date
        $updateData = [
            'tool_judgment' => $request->judgment,
            'verification_date_remarks' => $request->remarks,
        ];

        if ($request->filled('next_verifikasi')) {
            $updateData['planned_verification_date'] = $request->next_verifikasi;
        }

        $tool->update($updateData);

        // 3. Mark actual verification in schedule grid
        $vDate = Carbon::parse($request->tanggal_verifikasi);
        $vWeek = (int)ceil($vDate->day / 7);
        if ($vWeek > 4) $vWeek = 4;

        VerificationSchedule::updateOrCreate(
            [
                'tool_id' => $tool->id,
                'year' => $vDate->year,
                'month' => $vDate->month,
                'week' => $vWeek,
            ],
            [
                'actual_status' => $request->judgment,
                'actual_date' => $request->tanggal_verifikasi,
            ]
        );

        // 4. Sync next planned date in schedule grid
        if ($request->filled('next_verifikasi')) {
            $this->syncPlannedDateSchedule($tool, $request->next_verifikasi);
        }

        ActivityLogger::log('created', $verif, "Menginput Verifikasi Alat: {$tool->name_part} ({$request->judgment})");

        return response()->json([
            'success' => true,
            'message' => 'Hasil verifikasi alat berhasil disimpan!'
        ]);
    }

    private function syncPlannedDateSchedule(VerificationTool $tool, string $plannedDate)
    {
        $date = Carbon::parse($plannedDate);
        $year = $date->year;
        $month = $date->month;
        $week = (int)ceil($date->day / 7);
        if ($week > 4) $week = 4;

        // Reset old plan status for this year for this tool
        VerificationSchedule::where('tool_id', $tool->id)
            ->where('year', $year)
            ->update(['planning_status' => null]);

        // Set new plan status for target year, month, week
        VerificationSchedule::updateOrCreate(
            [
                'tool_id' => $tool->id,
                'year' => $year,
                'month' => $month,
                'week' => $week,
            ],
            [
                'planning_status' => 'P',
            ]
        );
    }

    public function toolsDestroy($id)
    {
        $tool = VerificationTool::findOrFail($id);
        $name = $tool->name_part;
        $tool->delete();
        
        ActivityLogger::log('deleted', null, "Menghapus Master Data Alat Verifikasi: {$name}");

        return redirect()->back()->with('success', 'Data alat verifikasi berhasil dihapus.');
    }

    public function verificationsIndex(Request $request)
    {
        $plantCode = $request->input('plant', auth()->user()->plant ? auth()->user()->plant->code : 'jakarta');
        $plant = Plant::where('code', $plantCode)->first();

        $query = VerificationVerification::where('plant_id', $plant->id)
            ->with('tool');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name_part', 'LIKE', "%{$search}%")
                  ->orWhere('no_part', 'LIKE', "%{$search}%");
            });
        }

        $verifications = $query->latest('tanggal_verifikasi')->get();

        return view('verifications.verifications.index', compact('verifications', 'plantCode'));
    }

    public function verificationsStore(Request $request)
    {
        $request->validate([
            'tool_id' => 'required|exists:verification_tools,id',
            'tanggal_verifikasi' => 'required|date',
            'judgment' => 'required|string',
        ]);

        $tool = VerificationTool::findOrFail($request->tool_id);

        $verification = VerificationVerification::create(array_merge($request->all(), [
            'name_part' => $tool->name_part,
            'no_part' => $tool->no_part,
            'plant_id' => $tool->plant_id,
        ]));

        // Update tool judgment
        $tool->update(['tool_judgment' => $request->judgment]);

        // Update schedule if exists
        $date = Carbon::parse($request->tanggal_verifikasi);
        $month = $date->month;
        $week = (int)ceil($date->day / 7);
        if ($week > 4) $week = 4;

        VerificationSchedule::updateOrCreate(
            [
                'tool_id' => $tool->id,
                'year' => $date->year,
                'month' => $month,
                'week' => $week,
            ],
            [
                'actual_status' => $request->judgment,
                'actual_date' => $request->tanggal_verifikasi,
            ]
        );

        ActivityLogger::log('created', $verification, "Input Verifikasi Alat: {$tool->name_part}");

        return redirect()->back()->with('success', 'Data verifikasi berhasil disimpan.');
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
            'plant' => 'required|string',
            'year' => 'required|integer',
            'sheet_name' => 'nullable|string',
        ]);

        $plantCode = $request->plant;
        $plant = Plant::where('code', $plantCode)->first();
        if (!$plant) {
            return redirect()->back()->with('error', 'Plant tidak ditemukan.');
        }

        $year = (int)$request->year;
        $targetSheet = $request->input('sheet_name', '2026 NEW');

        try {
            $file = $request->file('file');
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file->getRealPath());
            $spreadsheet = $reader->load($file->getRealPath());

            $sheet = $spreadsheet->getSheetByName($targetSheet);
            if (!$sheet) {
                $cleanSheetName = trim($targetSheet, '()');
                $sheet = $spreadsheet->getSheetByName($cleanSheetName) ?? $spreadsheet->getActiveSheet();
            }

            $rows = $sheet->toArray(null, true, true, true);
            $totalRows = count($rows);

            $importedToolsCount = 0;
            $importedSchedulesCount = 0;

            // Map columns L..BG (48 columns) to Month (1..12) and Week (1..4)
            $gridMapping = [];
            $colIndex = 12; // 'L'
            for ($m = 1; $m <= 12; $m++) {
                for ($w = 1; $w <= 4; $w++) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                    $gridMapping[$colLetter] = ['month' => $m, 'week' => $w];
                    $colIndex++;
                }
            }

            for ($r = 11; $r <= $totalRows; $r++) {
                $namePart = trim((string)($rows[$r]['C'] ?? ''));
                $noPart = trim((string)($rows[$r]['D'] ?? ''));

                if (empty($namePart) || empty($noPart)) {
                    continue;
                }

                $toolType = trim((string)($rows[$r]['E'] ?? 'JIG INSPECTION'));
                $customer = trim((string)($rows[$r]['F'] ?? ''));
                $quantity = is_numeric($rows[$r]['G'] ?? null) ? (int)$rows[$r]['G'] : 1;
                $freq = trim((string)($rows[$r]['H'] ?? ''));
                $calibrationHistory = trim((string)($rows[$r]['I'] ?? ''));
                $verificationType = trim((string)($rows[$r]['J'] ?? 'INTERNAL'));
                $toolJudgment = trim((string)($rows[$r]['BH'] ?? ''));

                // Upsert Tool Master Data (Without overriding planned dates)
                $tool = VerificationTool::updateOrCreate(
                    [
                        'plant_id' => $plant->id,
                        'name_part' => $namePart,
                        'no_part' => $noPart,
                    ],
                    [
                        'tool_type' => $toolType ?: 'JIG INSPECTION',
                        'customer' => $customer,
                        'quantity' => $quantity,
                        'verification_frequency' => $freq,
                        'calibration_history' => $calibrationHistory,
                        'verification_type' => $verificationType ?: 'INTERNAL',
                        'tool_status' => 'AKTIF',
                    ]
                );

                $importedToolsCount++;

                $actualRowIndex = ($r + 1 <= $totalRows && trim((string)($rows[$r + 1]['K'] ?? '')) === 'A') ? $r + 1 : null;
                if ($actualRowIndex) {
                    $r++;
                }
            }

            ActivityLogger::log('imported', null, "Import Master Data Alat Verifikasi ({$targetSheet}): {$importedToolsCount} tool berhasil diproses.");

            return redirect()->back()->with('success', "Import berhasil! {$importedToolsCount} data master alat verifikasi berhasil diproses.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses file Excel: ' . $e->getMessage());
        }
    }

    /**
     * Download Excel template for Verification Tools Master Data
     */
    /**
     * Download Template / Export Master Data Verification Tools (.xlsx)
     */
    public function downloadToolsTemplate(Request $request)
    {
        $plantCode = $request->input('plant', 'jakarta');
        $plant = Plant::where('code', $plantCode)->first();
        if (!$plant) {
            $plantId = Plant::resolveId($plantCode);
            $plant = Plant::find($plantId);
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Master Alat Verifikasi');

        // Headers
        $headers = [
            'A1' => 'Nama Part',
            'B1' => 'No Part / Model',
            'C1' => 'Part Code',
            'D1' => 'Jenis Alat',
            'E1' => 'Customer',
            'F1' => 'Qty',
            'G1' => 'Frekuensi',
            'H1' => 'Jenis Verifikasi',
            'I1' => 'Drawing',
            'J1' => 'Tanggal Rencana Verifikasi',
            'K1' => 'Status',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        // Header Styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2563EB'] // Primary Blue
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
        ];

        $sheet->getStyle('A1:K1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Fetch actual data for the current plant if available
        $tools = $plant ? VerificationTool::where('plant_id', $plant->id)->orderBy('name_part', 'asc')->get() : collect();

        $rowNum = 2;
        if ($tools->count() > 0) {
            foreach ($tools as $tool) {
                $sheet->setCellValue('A' . $rowNum, $tool->name_part);
                $sheet->setCellValue('B' . $rowNum, $tool->no_part);
                $sheet->setCellValue('C' . $rowNum, $tool->part_code);
                $sheet->setCellValue('D' . $rowNum, $tool->tool_type);
                $sheet->setCellValue('E' . $rowNum, $tool->customer);
                $sheet->setCellValue('F' . $rowNum, $tool->quantity);
                $sheet->setCellValue('G' . $rowNum, $tool->verification_frequency);
                $sheet->setCellValue('H' . $rowNum, $tool->verification_type);
                $sheet->setCellValue('I' . $rowNum, $tool->drawing);
                $sheet->setCellValue('J' . $rowNum, $tool->planned_verification_date ? \Carbon\Carbon::parse($tool->planned_verification_date)->format('Y-m-d') : '');
                $sheet->setCellValue('K' . $rowNum, $tool->tool_status ?? 'AKTIF');
                $rowNum++;
            }
        } else {
            // Sample Data Rows fallback if no tools exist
            $sampleData = [
                ['SPRING SEAT', '51402-K2VG-N000', 'P-0012', 'JIG CHECKER', 'AHM', 1, '1 Bulan', 'INTERNAL', 'ADA', '2026-10-15', 'AKTIF'],
                ['STAY COMP', '64301-K0W-N000', 'P-0045', 'PLUG GAUGE', 'YIMM', 2, '3 Bulan', 'EKSTERNAL', 'TIDAK', '2026-11-01', 'AKTIF'],
            ];
            foreach ($sampleData as $row) {
                $colIndex = 'A';
                foreach ($row as $val) {
                    $sheet->setCellValue($colIndex . $rowNum, $val);
                    $colIndex++;
                }
                $rowNum++;
            }
        }

        // Data Validation Dropdowns for Data Consistency
        $createValidation = function ($formula) {
            $validation = new \PhpOffice\PhpSpreadsheet\Cell\DataValidation();
            $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Pilihan Tidak Valid');
            $validation->setError('Pilihlah salah satu nilai dari daftar dropdown.');
            $validation->setFormula1('"' . $formula . '"');
            return $validation;
        };

        $valFreq = $createValidation('1 Bulan,2 Bulan,3 Bulan,6 Bulan,1 Tahun,2 Tahun');
        $valVerifType = $createValidation('INTERNAL,EKSTERNAL');
        $valDrawing = $createValidation('ADA,TIDAK');
        $valStatus = $createValidation('AKTIF,NON-AKTIF');

        $maxValidationRow = max(500, $rowNum + 50);
        for ($r = 2; $r <= $maxValidationRow; $r++) {
            $sheet->getCell('G' . $r)->setDataValidation(clone $valFreq);
            $sheet->getCell('H' . $r)->setDataValidation(clone $valVerifType);
            $sheet->getCell('I' . $r)->setDataValidation(clone $valDrawing);
            $sheet->getCell('K' . $r)->setDataValidation(clone $valStatus);
        }

        // Auto width for columns
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Template_Master_Alat_Verifikasi_' . strtoupper($plantCode) . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    /**
     * Import Master Data Verification Tools from Excel (.xlsx)
     */
    public function toolsImportExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
            'plant' => 'required|string',
            'duplicate_action' => 'nullable|string|in:update,skip',
        ]);

        $plantCode = $request->plant;
        $plant = Plant::where('code', $plantCode)->first();
        if (!$plant) {
            $plantId = Plant::resolveId($plantCode);
            $plant = Plant::find($plantId);
        }

        if (!$plant) {
            return redirect()->back()->with('error', 'Plant tidak ditemukan.');
        }

        $duplicateAction = $request->input('duplicate_action', 'update');

        try {
            $file = $request->file('file');
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file->getRealPath());
            $spreadsheet = $reader->load($file->getRealPath());

            // Always use active sheet (first sheet)
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);
            $totalRows = count($rows);

            $createdCount = 0;
            $updatedCount = 0;
            $skippedCount = 0;

            for ($r = 2; $r <= $totalRows; $r++) {
                $namePart = trim((string)($rows[$r]['A'] ?? ''));
                $noPart = trim((string)($rows[$r]['B'] ?? ''));

                if (empty($namePart) && empty($noPart)) {
                    continue;
                }

                if (empty($namePart) || empty($noPart)) {
                    continue; // Mandatory fields missing
                }

                $partCode = trim((string)($rows[$r]['C'] ?? '')) ?: null;
                $toolType = strtoupper(trim((string)($rows[$r]['D'] ?? 'JIG CHECKER'))) ?: 'JIG CHECKER';
                $customer = trim((string)($rows[$r]['E'] ?? '')) ?: '-';
                $quantity = is_numeric($rows[$r]['F'] ?? null) ? (int)$rows[$r]['F'] : 1;
                $freq = trim((string)($rows[$r]['G'] ?? '1 Tahun')) ?: '1 Tahun';
                $verificationType = strtoupper(trim((string)($rows[$r]['H'] ?? 'INTERNAL'))) ?: 'INTERNAL';
                $drawing = strtoupper(trim((string)($rows[$r]['I'] ?? 'ADA'))) ?: 'ADA';
                $plannedDateRaw = trim((string)($rows[$r]['J'] ?? '')) ?: null;
                $toolStatus = strtoupper(trim((string)($rows[$r]['K'] ?? 'AKTIF'))) ?: 'AKTIF';

                // Format planned date if available
                $plannedDate = null;
                if (!empty($plannedDateRaw)) {
                    try {
                        if (is_numeric($plannedDateRaw)) {
                            $plannedDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($plannedDateRaw)->format('Y-m-d');
                        } else {
                            $plannedDate = \Carbon\Carbon::parse($plannedDateRaw)->format('Y-m-d');
                        }
                    } catch (\Exception $e) {
                        $plannedDate = null;
                    }
                }

                // Check existing tool
                $existing = VerificationTool::where('plant_id', $plant->id)
                    ->where('name_part', $namePart)
                    ->where('no_part', $noPart)
                    ->first();

                if ($existing && $duplicateAction === 'skip') {
                    $skippedCount++;
                    continue;
                }

                $toolData = [
                    'plant_id' => $plant->id,
                    'name_part' => $namePart,
                    'no_part' => $noPart,
                    'part_code' => $partCode,
                    'tool_type' => $toolType,
                    'customer' => $customer,
                    'quantity' => $quantity,
                    'verification_frequency' => $freq,
                    'verification_type' => $verificationType,
                    'drawing' => $drawing,
                    'tool_status' => $toolStatus,
                ];

                if (!empty($plannedDate)) {
                    $toolData['planned_verification_date'] = $plannedDate;
                }

                if ($existing) {
                    $existing->update($toolData);
                    $tool = $existing;
                    $updatedCount++;
                } else {
                    $tool = VerificationTool::create($toolData);
                    $createdCount++;
                }

                // Automatically update Verification Schedule if planned_verification_date is present
                if (!empty($plannedDate)) {
                    $this->syncPlannedDateSchedule($tool, $plannedDate);
                }
            }

            ActivityLogger::log('imported', null, "Import Master Data Alat Verifikasi: {$createdCount} dibuat, {$updatedCount} diupdate, {$skippedCount} dilewati.");

            $msg = "Import berhasil! {$createdCount} alat baru dibuat, {$updatedCount} diupdate";
            if ($skippedCount > 0) {
                $msg .= ", {$skippedCount} dilewati";
            }
            $msg .= '.';

            return redirect()->back()->with('success', $msg);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses file Excel: ' . $e->getMessage());
        }
    }
}
