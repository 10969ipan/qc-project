<?php

namespace App\Http\Controllers;

use App\Models\StandardPerformanceTest;
use App\Models\DurabilityThicknessReport;
use Illuminate\Http\Request;
use App\Helpers\ActivityLogger;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Validation\Rule;
class StandardPerformanceTestController extends Controller
{
    public function index(Request $request, $isTrial = false)
    {
        $query = StandardPerformanceTest::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('part_name', 'like', "%{$search}%")
                  ->orWhere('customer_standard', 'like', "%{$search}%");
            });
        }

        $standards = $query->orderBy('id', 'asc')->get();
        
        $partNames = StandardPerformanceTest::pluck('part_name')->unique()->filter()->values();
        $partNumbers = StandardPerformanceTest::pluck('part_number')->unique()->filter()->values();
        $customers = StandardPerformanceTest::pluck('customer_name')->unique()->filter()->values();

        return view('durability_plating.index', compact('standards', 'partNames', 'partNumbers', 'customers', 'isTrial'));
    }

    public function indexTrial(Request $request)
    {
        return $this->index($request, true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'part_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('standard_performance_tests')->where(function ($query) use ($request) {
                    return $query->where('part_number', $request->part_number);
                })
            ],
            'part_number' => 'required|string|max:255',
            'customer_name' => 'required|string|max:255',
            'customer_standard' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'thickness_cu' => 'required|string|max:255',
            'thickness_ni' => 'required|string|max:255',
            'thickness_cr' => 'required|string|max:255',
            'thickness_freq' => 'required|string|max:255',
            'corrodkote_time' => 'required|string|max:255',
            'corrodkote_std_max_corrosion' => 'required|string|max:255',
            'corrodkote_freq' => 'required|string|max:255',
            'cass_time' => 'required|string|max:255',
            'cass_std_min_rn' => 'required|string|max:255',
            'cass_freq' => 'required|string|max:255',
            'salt_spray_time' => 'required|string|max:255',
            'salt_spray_std_rusting' => 'required|string|max:255',
            'salt_spray_freq' => 'required|string|max:255',
            'porecount_std_min' => 'required|string|max:255',
            'porecount_freq' => 'required|string|max:255',
        ]);

        $standard = StandardPerformanceTest::create($validated);
        
        ActivityLogger::log('created', $standard, "Menambahkan Master Data Standard Performance Test: {$standard->part_name}");

        return redirect()->back()->with('success', 'Master data berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $standard = StandardPerformanceTest::findOrFail($id);

        $validated = $request->validate([
            'part_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('standard_performance_tests')->where(function ($query) use ($request) {
                    return $query->where('part_number', $request->part_number);
                })->ignore($id)
            ],
            'part_number' => 'required|string|max:255',
            'customer_name' => 'required|string|max:255',
            'customer_standard' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'thickness_cu' => 'required|string|max:255',
            'thickness_ni' => 'required|string|max:255',
            'thickness_cr' => 'required|string|max:255',
            'thickness_freq' => 'required|string|max:255',
            'corrodkote_time' => 'required|string|max:255',
            'corrodkote_std_max_corrosion' => 'required|string|max:255',
            'corrodkote_freq' => 'required|string|max:255',
            'cass_time' => 'required|string|max:255',
            'cass_std_min_rn' => 'required|string|max:255',
            'cass_freq' => 'required|string|max:255',
            'salt_spray_time' => 'required|string|max:255',
            'salt_spray_std_rusting' => 'required|string|max:255',
            'salt_spray_freq' => 'required|string|max:255',
            'porecount_std_min' => 'required|string|max:255',
            'porecount_freq' => 'required|string|max:255',
        ]);

        $standard->update($validated);
        
        ActivityLogger::log('updated', $standard, "Mengubah Master Data Standard Performance Test: {$standard->part_name}");

        return redirect()->back()->with('success', 'Master data berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $standard = StandardPerformanceTest::findOrFail($id);
        $name = $standard->part_name;
        $standard->delete();
        
        ActivityLogger::log('deleted', null, "Menghapus Master Data Standard Performance Test: {$name}");

        return redirect()->back()->with('success', 'Master data berhasil dihapus.');
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Std Performance');
        
        $headers = [
            'No.', 'Nama Part', 'Part No.', 'Customer', 'Standard Customer', 'Kategori', 'Cr', 'Ni', 'Cu', 'Frek. Thickness',
            'Corrodkote Waktu', 'Corrodkote Std Max', 'Corrodkote Frek',
            'Cass Waktu', 'Cass Std Min', 'Cass Frek',
            'Salt Spray Waktu', 'Salt Spray Std', 'Salt Spray Frek',
            'Porecount Std Min', 'Porecount Frek'
        ];
        
        // Tulis header
        foreach ($headers as $colIndex => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . '1', $header);
            
            // Format header: Bold, text centered, and light gray background
            $style = $sheet->getStyle($colLetter . '1');
            $style->getFont()->setBold(true);
            $style->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $style->getFill()->getStartColor()->setARGB('FFE2E8F0');
            $style->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        // Ambil data yang sudah ada dari database
        $items = StandardPerformanceTest::orderBy('part_name', 'asc')->get();

        $rowsData = [];
        if ($items->count() > 0) {
            $no = 1;
            foreach ($items as $item) {
                $rowsData[] = [
                    $no++,
                    $item->part_name,
                    $item->part_number,
                    $item->customer_name,
                    $item->customer_standard,
                    $item->category,
                    $item->thickness_cr,
                    $item->thickness_ni,
                    $item->thickness_cu,
                    $item->thickness_freq,
                    $item->corrodkote_time,
                    $item->corrodkote_std_max_corrosion,
                    $item->corrodkote_freq,
                    $item->cass_time,
                    $item->cass_std_min_rn,
                    $item->cass_freq,
                    $item->salt_spray_time,
                    $item->salt_spray_std_rusting,
                    $item->salt_spray_freq,
                    $item->porecount_std_min,
                    $item->porecount_freq
                ];
            }
        } else {
            // Default sample rows jika tidak ada data
            $rowsData = [
                [
                    1, 'Sample Part A', 'P-1234', 'HONDA', 'HES', 'R2/R4', '20', '15', '10', '1x/Shift',
                    '24', 'Max 5%', '1x/Shift',
                    '48', 'Min RN 8', '1x/Shift',
                    '72', 'Max 2%', '1x/Shift',
                    'Min 5 pores', '1x/Shift'
                ]
            ];
        }

        // Tulis data ke cell
        foreach ($rowsData as $rowIndex => $rowData) {
            foreach ($rowData as $colIndex => $val) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
                $sheet->setCellValue($colLetter . ($rowIndex + 2), $val);
            }
        }

        // Auto size columns
        foreach ($headers as $colIndex => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Template_Import_Standard_Performance_Test.xlsx';
        
        return response()->stream(
            function () use ($writer) {
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Cache-Control' => 'max-age=0',
            ]
        );
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            $spreadsheet = IOFactory::load($request->file('file')->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            
            $count = 0;
            $processedIds = [];
            // Skip header (row 0)
            foreach (array_slice($rows, 1) as $row) {
                if (empty(trim($row[1]))) continue; // Skip if Nama Part is empty
                
                $partName = trim($row[1]);
                $rawPartNo = isset($row[2]) ? trim($row[2]) : '';
                $partNo = ($rawPartNo === '' || $rawPartNo === '-') ? null : $rawPartNo;
                
                $query = StandardPerformanceTest::where('part_name', $partName);
                
                if (is_null($partNo)) {
                    $query->whereNull('part_number');
                } else {
                    $query->where('part_number', $partNo);
                }
                
                $standard = $query->first();
                
                $data = [
                    'part_name' => $partName,
                    'part_number' => $partNo,
                    'customer_name' => $row[3] ?? null,
                    'customer_standard' => $row[4] ?? null,
                    'category' => $row[5] ?? null,
                    'thickness_cr' => $row[6] ?? null,
                    'thickness_ni' => $row[7] ?? null,
                    'thickness_cu' => $row[8] ?? null,
                    'thickness_freq' => $row[9] ?? null,
                    'corrodkote_time' => $row[10] ?? null,
                    'corrodkote_std_max_corrosion' => $row[11] ?? null,
                    'corrodkote_freq' => $row[12] ?? null,
                    'cass_time' => $row[13] ?? null,
                    'cass_std_min_rn' => $row[14] ?? null,
                    'cass_freq' => $row[15] ?? null,
                    'salt_spray_time' => $row[16] ?? null,
                    'salt_spray_std_rusting' => $row[17] ?? null,
                    'salt_spray_freq' => $row[18] ?? null,
                    'porecount_std_min' => $row[19] ?? null,
                    'porecount_freq' => $row[20] ?? null,
                ];

                if ($standard) {
                    $standard->update($data);
                    $processedIds[] = $standard->id;
                } else {
                    $newStandard = StandardPerformanceTest::create($data);
                    $processedIds[] = $newStandard->id;
                }
                $count++;
            }
            
            // Hapus data di database yang tidak ada di file Excel (Sync)
            if (count($processedIds) > 0) {
                StandardPerformanceTest::whereNotIn('id', $processedIds)->delete();
            }
            
            ActivityLogger::log('imported', null, "Mengimport dan mensinkronkan $count Master Data Standard Performance Test");
            
            return redirect()->back()->with('success', "$count data berhasil diimport dan disinkronkan.");
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses file Excel: ' . $e->getMessage());
        }
    }

    public function storeThickness(Request $request)
    {
        $request->validate([
            'standard_performance_test_id' => 'required|exists:standard_performance_tests,id',
            'production_date' => 'nullable|date',
            'shift' => 'nullable|string|max:255',
            'lot_no' => 'nullable|string|max:255',
            'actual_cu' => 'nullable|string|max:255',
            'actual_ni' => 'nullable|string|max:255',
            'actual_cr' => 'nullable|string|max:255',
            'actual_corrodkote_waktu' => 'nullable|string|max:255',
            'standar_jam_corrodkote' => 'nullable|string|max:255',
            'aktual_corrosion' => 'nullable|string|max:255',
            'actual_cass_waktu' => 'nullable|string|max:255',
            'standar_jam_cass' => 'nullable|string|max:255',
            'aktual_rn' => 'nullable|string|max:255',
            'actual_salt_spray_waktu' => 'nullable|string|max:255',
            'standar_jam_salt_spray' => 'nullable|string|max:255',
            'actual_porecount' => 'nullable|string|max:255',
            'result_judgment' => 'nullable|string|max:255',
            'tgl_masuk' => 'nullable|date',
            'jam_masuk' => 'nullable|date_format:H:i',
            'tgl_keluar' => 'nullable|date',
            'jam_keluar' => 'nullable|date_format:H:i',
            'description' => 'nullable|string',

            // Trial fields (Data 2)
            'actual_cu_trial' => 'nullable|string|max:255',
            'actual_ni_trial' => 'nullable|string|max:255',
            'actual_cr_trial' => 'nullable|string|max:255',
            'actual_corrodkote_waktu_trial' => 'nullable|string|max:255',
            'standar_jam_corrodkote_trial' => 'nullable|string|max:255',
            'aktual_corrosion_trial' => 'nullable|string|max:255',
            'actual_cass_waktu_trial' => 'nullable|string|max:255',
            'standar_jam_cass_trial' => 'nullable|string|max:255',
            'aktual_rn_trial' => 'nullable|string|max:255',
            'actual_salt_spray_waktu_trial' => 'nullable|string|max:255',
            'standar_jam_salt_spray_trial' => 'nullable|string|max:255',
            'actual_porecount_trial' => 'nullable|string|max:255',
            'result_judgment_trial' => 'nullable|string|max:255',
            'description_trial' => 'nullable|string'
        ]);

        $evidenceBeforePath = null;
        $evidenceAfterPath = null;
        $evidenceAfterTrialPath = null;

        if ($request->hasFile('evidence_before')) {
            $fileBefore = $request->file('evidence_before');
            $filenameBefore = time() . '_before_' . $fileBefore->getClientOriginalName();
            $fileBefore->move(public_path('uploads/durability_plating'), $filenameBefore);
            $evidenceBeforePath = 'uploads/durability_plating/' . $filenameBefore;
        }

        if ($request->hasFile('evidence_after')) {
            $fileAfter = $request->file('evidence_after');
            $filenameAfter = time() . '_after_' . $fileAfter->getClientOriginalName();
            $fileAfter->move(public_path('uploads/durability_plating'), $filenameAfter);
            $evidenceAfterPath = 'uploads/durability_plating/' . $filenameAfter;
        }

        if ($request->hasFile('evidence_after_trial')) {
            $fileAfterTrial = $request->file('evidence_after_trial');
            $filenameAfterTrial = time() . '_after_data2_' . $fileAfterTrial->getClientOriginalName();
            $fileAfterTrial->move(public_path('uploads/durability_plating'), $filenameAfterTrial);
            $evidenceAfterTrialPath = 'uploads/durability_plating/' . $filenameAfterTrial;
        }

        // If inputting directly from Menu Data 2 (Trial)
        if ($request->boolean('is_trial')) {
            $reportTrial = DurabilityThicknessReport::create([
                'standard_performance_test_id' => $request->standard_performance_test_id,
                'production_date' => $request->production_date,
                'shift' => $request->shift,
                'lot_no' => $request->lot_no,
                'actual_cu' => $request->actual_cu,
                'actual_ni' => $request->actual_ni,
                'actual_cr' => $request->actual_cr,
                'actual_corrodkote_waktu' => $request->actual_corrodkote_waktu ?? '-',
                'standar_jam_corrodkote' => $request->standar_jam_corrodkote ?? '-',
                'aktual_corrosion' => $request->aktual_corrosion ?? null,
                'actual_cass_waktu' => $request->actual_cass_waktu ?? '-',
                'standar_jam_cass' => $request->standar_jam_cass ?? '-',
                'aktual_rn' => $request->aktual_rn ?? null,
                'actual_salt_spray_waktu' => $request->actual_salt_spray_waktu ?? '-',
                'standar_jam_salt_spray' => $request->standar_jam_salt_spray ?? '-',
                'actual_porecount' => $request->actual_porecount ?? '-',
                'result_judgment' => $request->result_judgment ?? '-',
                'tgl_masuk' => $request->tgl_masuk,
                'jam_masuk' => $request->jam_masuk,
                'tgl_keluar' => $request->tgl_keluar,
                'jam_keluar' => $request->jam_keluar,
                'tanggal_cek' => now()->toDateString(),
                'analis_id' => auth()->id(),
                'description' => $request->description,
                'is_trial' => true,
                'evidence_before' => $evidenceBeforePath,
                'evidence_before_uploaded_at' => $evidenceBeforePath ? now() : null,
                'evidence_after' => $evidenceAfterTrialPath ?: $evidenceAfterPath,
                'evidence_after_uploaded_at' => ($evidenceAfterTrialPath ?: $evidenceAfterPath) ? now() : null,
            ]);

            $std = StandardPerformanceTest::find($request->standard_performance_test_id);
            $partName = $std ? $std->part_name : 'Part';
            ActivityLogger::log('created', $reportTrial, "Menambahkan Laporan Durability Plating (Data 2 Trial): {$partName} (Lot: {$request->lot_no})");
            return redirect()->back()->with('success', 'Data 2 berhasil disimpan.');
        }

        // Data 1 (Regular / Actual)
        $report1 = DurabilityThicknessReport::create([
            'standard_performance_test_id' => $request->standard_performance_test_id,
            'production_date' => $request->production_date,
            'shift' => $request->shift,
            'lot_no' => $request->lot_no,
            'actual_cu' => $request->actual_cu,
            'actual_ni' => $request->actual_ni,
            'actual_cr' => $request->actual_cr,
            'actual_corrodkote_waktu' => $request->actual_corrodkote_waktu ?? '-',
            'standar_jam_corrodkote' => $request->standar_jam_corrodkote ?? '-',
            'aktual_corrosion' => $request->aktual_corrosion ?? null,
            'actual_cass_waktu' => $request->actual_cass_waktu ?? '-',
            'standar_jam_cass' => $request->standar_jam_cass ?? '-',
            'aktual_rn' => $request->aktual_rn ?? null,
            'actual_salt_spray_waktu' => $request->actual_salt_spray_waktu ?? '-',
            'standar_jam_salt_spray' => $request->standar_jam_salt_spray ?? '-',
            'actual_porecount' => $request->actual_porecount ?? '-',
            'result_judgment' => $request->result_judgment ?? '-',
            'result_judgment_corrodkote' => $request->result_judgment_corrodkote ?? ($request->filled('standar_jam_corrodkote') && $request->standar_jam_corrodkote !== '-' ? ($request->result_judgment ?? '-') : null),
            'result_judgment_cass' => $request->result_judgment_cass ?? ($request->filled('standar_jam_cass') && $request->standar_jam_cass !== '-' ? ($request->result_judgment ?? '-') : null),
            'result_judgment_salt_spray' => $request->result_judgment_salt_spray ?? ($request->filled('standar_jam_salt_spray') && $request->standar_jam_salt_spray !== '-' ? ($request->result_judgment ?? '-') : null),
            'result_judgment_porecount' => $request->result_judgment_porecount ?? ($request->filled('actual_porecount') && $request->actual_porecount !== '-' ? ($request->result_judgment ?? '-') : null),
            'tgl_masuk' => $request->tgl_masuk,
            'jam_masuk' => $request->jam_masuk,
            'tgl_keluar' => $request->tgl_keluar,
            'jam_keluar' => $request->jam_keluar,
            'tanggal_cek' => now()->toDateString(),
            'analis_id' => auth()->id(),
            'description' => $request->description,
            'description_corrodkote' => $request->description_corrodkote,
            'description_cass' => $request->description_cass,
            'description_salt_spray' => $request->description_salt_spray,
            'description_porecount' => $request->description_porecount,
            'is_trial' => false,
            'evidence_before' => $evidenceBeforePath,
            'evidence_before_uploaded_at' => $evidenceBeforePath ? now() : null,
            'evidence_after' => $evidenceAfterPath,
            'evidence_after_uploaded_at' => $evidenceAfterPath ? now() : null,
            'evidence_after_trial' => $evidenceAfterTrialPath,
            'evidence_after_trial_uploaded_at' => $evidenceAfterTrialPath ? now() : null,
        ]);

        $std = StandardPerformanceTest::find($request->standard_performance_test_id);
        $partName = $std ? $std->part_name : 'Part';
        ActivityLogger::log('created', $report1, "Menambahkan Laporan Durability Plating [DATA 1]: {$partName} (Lot: {$report1->lot_no})");

        // Always create/update Data 2 (Trial) record automatically when Data 1 is created
        $report2 = DurabilityThicknessReport::create([
            'standard_performance_test_id' => $request->standard_performance_test_id,
            'production_date' => $request->production_date,
            'shift' => $request->shift,
            'lot_no' => $request->lot_no,
            'actual_cu' => $request->filled('actual_cu_trial') ? $request->actual_cu_trial : null,
            'actual_ni' => $request->filled('actual_ni_trial') ? $request->actual_ni_trial : null,
            'actual_cr' => $request->filled('actual_cr_trial') ? $request->actual_cr_trial : null,
            'actual_corrodkote_waktu' => $request->filled('actual_corrodkote_waktu_trial') ? $request->actual_corrodkote_waktu_trial : '-',
            'standar_jam_corrodkote' => $request->filled('standar_jam_corrodkote_trial') ? $request->standar_jam_corrodkote_trial : ($request->standar_jam_corrodkote ?? '-'),
            'aktual_corrosion' => $request->filled('aktual_corrosion_trial') ? $request->aktual_corrosion_trial : null,
            'actual_cass_waktu' => $request->filled('actual_cass_waktu_trial') ? $request->actual_cass_waktu_trial : '-',
            'standar_jam_cass' => $request->filled('standar_jam_cass_trial') ? $request->standar_jam_cass_trial : ($request->standar_jam_cass ?? '-'),
            'aktual_rn' => $request->filled('aktual_rn_trial') ? $request->aktual_rn_trial : null,
            'actual_salt_spray_waktu' => $request->filled('actual_salt_spray_waktu_trial') ? $request->actual_salt_spray_waktu_trial : '-',
            'standar_jam_salt_spray' => $request->filled('standar_jam_salt_spray_trial') ? $request->standar_jam_salt_spray_trial : ($request->standar_jam_salt_spray ?? '-'),
            'actual_porecount' => $request->filled('actual_porecount_trial') ? $request->actual_porecount_trial : '-',
            'result_judgment' => ($request->filled('result_judgment_trial') && $request->result_judgment_trial !== '-') ? $request->result_judgment_trial : '-',
            'result_judgment_corrodkote' => $request->result_judgment_corrodkote_trial ?? ($request->filled('standar_jam_corrodkote_trial') && $request->standar_jam_corrodkote_trial !== '-' ? ($request->result_judgment_trial ?? '-') : null),
            'result_judgment_cass' => $request->result_judgment_cass_trial ?? ($request->filled('standar_jam_cass_trial') && $request->standar_jam_cass_trial !== '-' ? ($request->result_judgment_trial ?? '-') : null),
            'result_judgment_salt_spray' => $request->result_judgment_salt_spray_trial ?? ($request->filled('standar_jam_salt_spray_trial') && $request->standar_jam_salt_spray_trial !== '-' ? ($request->result_judgment_trial ?? '-') : null),
            'result_judgment_porecount' => $request->result_judgment_porecount_trial ?? ($request->filled('actual_porecount_trial') && $request->actual_porecount_trial !== '-' ? ($request->result_judgment_trial ?? '-') : null),
            'tgl_masuk' => $request->tgl_masuk,
            'jam_masuk' => $request->jam_masuk,
            'tgl_keluar' => $request->tgl_keluar,
            'jam_keluar' => $request->jam_keluar,
            'tanggal_cek' => now()->toDateString(),
            'analis_id' => auth()->id(),
            'description' => $request->filled('description_trial') ? $request->description_trial : null,
            'description_corrodkote' => $request->filled('description_corrodkote_trial') ? $request->description_corrodkote_trial : null,
            'description_cass' => $request->filled('description_cass_trial') ? $request->description_cass_trial : null,
            'description_salt_spray' => $request->filled('description_salt_spray_trial') ? $request->description_salt_spray_trial : null,
            'description_porecount' => $request->filled('description_porecount_trial') ? $request->description_porecount_trial : null,
            'is_trial' => true,
            'data1_id' => $report1->id,
            'evidence_before' => $evidenceBeforePath,
            'evidence_before_uploaded_at' => $evidenceBeforePath ? now() : null,
            'evidence_after' => $evidenceAfterTrialPath ?: $evidenceAfterPath,
            'evidence_after_uploaded_at' => ($evidenceAfterTrialPath ?: $evidenceAfterPath) ? now() : null,
        ]);

        ActivityLogger::log('created', $report2, "Menambahkan Laporan Durability Plating [DATA 2]: {$partName} (Lot: {$report2->lot_no})");

        return redirect()->back()->with('success', 'Data Thickness (Data 1 & Data 2) berhasil disimpan.');
    }

    public function report(Request $request) { return $this->renderReport($request, 'thickness', false); }
    public function reportCorrodkote(Request $request) { return $this->renderReport($request, 'corrodkote', false); }
    public function reportCass(Request $request) { return $this->renderReport($request, 'cass', false); }
    public function reportSaltSpray(Request $request) { return $this->renderReport($request, 'salt_spray', false); }
    public function reportPorecount(Request $request) { return $this->renderReport($request, 'porecount', false); }

    public function reportTrial(Request $request) { return $this->renderReport($request, 'thickness', true); }
    public function reportCorrodkoteTrial(Request $request) { return $this->renderReport($request, 'corrodkote', true); }
    public function reportCassTrial(Request $request) { return $this->renderReport($request, 'cass', true); }
    public function reportSaltSprayTrial(Request $request) { return $this->renderReport($request, 'salt_spray', true); }
    public function reportPorecountTrial(Request $request) { return $this->renderReport($request, 'porecount', true); }

    private function renderReport(Request $request, $testType, $isTrial = false)
    {
        $query = DurabilityThicknessReport::with([
                'standard', 'analis',
                'analisCorrodkote', 'analisCass', 'analisSaltSpray', 'analisPorecount',
                'updatedBy',
            ])
            ->orderBy('created_at', 'desc');

        if ($request->filled('report_id')) {
            // Direct link: fetch specific report ID directly regardless of filters or empty values
            $query->where('id', $request->report_id);
        } else {
            $query->where('is_trial', $isTrial);

            // Hanya tampilkan baris yang benar-benar memiliki data aktual untuk jenis tes ini
            $query->where(function ($q) use ($testType) {
                if ($testType === 'thickness') {
                    $q->where(function($sub) {
                        $sub->whereNotNull('actual_cu')->where('actual_cu', '!=', '')->where('actual_cu', '!=', '-')
                            ->orWhereNotNull('actual_ni')->where('actual_ni', '!=', '')->where('actual_ni', '!=', '-')
                            ->orWhereNotNull('actual_cr')->where('actual_cr', '!=', '')->where('actual_cr', '!=', '-');
                    });
                } elseif ($testType === 'corrodkote') {
                    $q->whereNotNull('standar_jam_corrodkote')->where('standar_jam_corrodkote', '!=', '')->where('standar_jam_corrodkote', '!=', '-');
                } elseif ($testType === 'cass') {
                    $q->whereNotNull('standar_jam_cass')->where('standar_jam_cass', '!=', '')->where('standar_jam_cass', '!=', '-');
                } elseif ($testType === 'salt_spray') {
                    $q->whereNotNull('standar_jam_salt_spray')->where('standar_jam_salt_spray', '!=', '')->where('standar_jam_salt_spray', '!=', '-');
                } elseif ($testType === 'porecount') {
                    $q->whereNotNull('actual_porecount')->where('actual_porecount', '!=', '')->where('actual_porecount', '!=', '-');
                }
            });
            
            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('standard', function($q) use ($search) {
                    $q->where('part_name', 'like', "%$search%")
                      ->orWhere('part_number', 'like', "%$search%")
                      ->orWhere('customer_name', 'like', "%$search%")
                      ->orWhere('customer_standard', 'like', "%$search%");
                });
            }
            if ($request->filled('customer_name')) {
                $customerName = $request->customer_name;
                $query->whereHas('standard', function($q) use ($customerName) {
                    $q->where('customer_name', $customerName);
                });
            }
            if ($request->filled('category')) {
                $category = $request->category;
                $query->whereHas('standard', function($q) use ($category) {
                    $q->where('category', $category);
                });
            }
            if ($request->filled('start_date')) {
                $query->whereDate('tanggal_cek', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('tanggal_cek', '<=', $request->end_date);
            }
            if ($request->filled('result_judgment')) {
                $this->applyResultJudgmentFilter($query, $testType, $request->result_judgment);
            }
        }

        // Calculate averages across all matching reports before pagination for THICKNESS and CORRODKOTE reports
        $averages = null;
        if ($testType === 'thickness') {
            $allMatching = (clone $query)->get();

            $cr1 = []; $ni1 = []; $cu1 = [];
            $cr2 = []; $ni2 = []; $cu2 = [];

            if (!$isTrial) {
                if (count($allMatching) > 0) {
                    $allIds = $allMatching->pluck('id')->filter()->unique();
                    $allTrials = DurabilityThicknessReport::where('is_trial', true)
                        ->whereIn('data1_id', $allIds)
                        ->get()
                        ->keyBy('data1_id');

                    foreach ($allMatching as $m) {
                        $tr = $allTrials->get($m->id);
                        if ($tr) {
                            $m->actual_cr_trial = $tr->actual_cr;
                            $m->actual_ni_trial = $tr->actual_ni;
                            $m->actual_cu_trial = $tr->actual_cu;
                        }
                    }
                }

                foreach ($allMatching as $item) {
                    if (is_numeric($item->actual_cr)) $cr1[] = (float)$item->actual_cr;
                    if (is_numeric($item->actual_ni)) $ni1[] = (float)$item->actual_ni;
                    if (is_numeric($item->actual_cu)) $cu1[] = (float)$item->actual_cu;

                    if (isset($item->actual_cr_trial) && is_numeric($item->actual_cr_trial)) $cr2[] = (float)$item->actual_cr_trial;
                    if (isset($item->actual_ni_trial) && is_numeric($item->actual_ni_trial)) $ni2[] = (float)$item->actual_ni_trial;
                    if (isset($item->actual_cu_trial) && is_numeric($item->actual_cu_trial)) $cu2[] = (float)$item->actual_cu_trial;
                }
            } else {
                if (count($allMatching) > 0) {
                    $data1Ids = $allMatching->pluck('data1_id')->filter()->unique();
                    $allData1 = DurabilityThicknessReport::whereIn('id', $data1Ids)->get()->keyBy('id');

                    foreach ($allMatching as $m) {
                        $d1 = $allData1->get($m->data1_id);
                        if ($d1) {
                            $m->actual_cr_d1 = $d1->actual_cr;
                            $m->actual_ni_d1 = $d1->actual_ni;
                            $m->actual_cu_d1 = $d1->actual_cu;
                        }
                    }
                }

                foreach ($allMatching as $item) {
                    if (isset($item->actual_cr_d1) && is_numeric($item->actual_cr_d1)) $cr1[] = (float)$item->actual_cr_d1;
                    if (isset($item->actual_ni_d1) && is_numeric($item->actual_ni_d1)) $ni1[] = (float)$item->actual_ni_d1;
                    if (isset($item->actual_cu_d1) && is_numeric($item->actual_cu_d1)) $cu1[] = (float)$item->actual_cu_d1;

                    if (is_numeric($item->actual_cr)) $cr2[] = (float)$item->actual_cr;
                    if (is_numeric($item->actual_ni)) $ni2[] = (float)$item->actual_ni;
                    if (is_numeric($item->actual_cu)) $cu2[] = (float)$item->actual_cu;
                }
            }

            $averages = [
                'count' => count($allMatching),
                'cr1' => count($cr1) ? number_format(array_sum($cr1) / count($cr1), 2) : '-',
                'ni1' => count($ni1) ? number_format(array_sum($ni1) / count($ni1), 2) : '-',
                'cu1' => count($cu1) ? number_format(array_sum($cu1) / count($cu1), 2) : '-',
                'cr2' => count($cr2) ? number_format(array_sum($cr2) / count($cr2), 2) : '-',
                'ni2' => count($ni2) ? number_format(array_sum($ni2) / count($ni2), 2) : '-',
                'cu2' => count($cu2) ? number_format(array_sum($cu2) / count($cu2), 2) : '-',
            ];
        } elseif ($testType === 'corrodkote') {
            $allMatching = (clone $query)->get();

            $parseVal = function($val) {
                if (is_null($val)) return null;
                $cleaned = str_replace(',', '.', trim(str_replace('%', '', (string)$val)));
                return is_numeric($cleaned) ? (float)$cleaned : null;
            };

            $corr1 = [];
            $corr2 = [];

            if (!$isTrial) {
                if (count($allMatching) > 0) {
                    $allIds = $allMatching->pluck('id')->filter()->unique();
                    $allTrials = DurabilityThicknessReport::where('is_trial', true)
                        ->whereIn('data1_id', $allIds)
                        ->get()
                        ->keyBy('data1_id');

                    foreach ($allMatching as $m) {
                        $tr = $allTrials->get($m->id);
                        if ($tr) {
                            $m->aktual_corrosion_trial = $tr->aktual_corrosion;
                        }
                    }
                }

                foreach ($allMatching as $item) {
                    $v1 = $parseVal($item->aktual_corrosion);
                    if (!is_null($v1)) $corr1[] = $v1;

                    if (isset($item->aktual_corrosion_trial)) {
                        $v2 = $parseVal($item->aktual_corrosion_trial);
                        if (!is_null($v2)) $corr2[] = $v2;
                    }
                }
            } else {
                if (count($allMatching) > 0) {
                    $data1Ids = $allMatching->pluck('data1_id')->filter()->unique();
                    $allData1 = DurabilityThicknessReport::whereIn('id', $data1Ids)->get()->keyBy('id');

                    foreach ($allMatching as $m) {
                        $d1 = $allData1->get($m->data1_id);
                        if ($d1) {
                            $m->aktual_corrosion_d1 = $d1->aktual_corrosion;
                        }
                    }
                }

                foreach ($allMatching as $item) {
                    if (isset($item->aktual_corrosion_d1)) {
                        $v1 = $parseVal($item->aktual_corrosion_d1);
                        if (!is_null($v1)) $corr1[] = $v1;
                    }

                    $v2 = $parseVal($item->aktual_corrosion);
                    if (!is_null($v2)) $corr2[] = $v2;
                }
            }

            $averages = [
                'count' => count($allMatching),
                'corrosion1' => count($corr1) ? number_format(array_sum($corr1) / count($corr1), 2) : '-',
                'corrosion2' => count($corr2) ? number_format(array_sum($corr2) / count($corr2), 2) : '-',
            ];
        } elseif ($testType === 'cass') {
            $allMatching = (clone $query)->get();

            $parseVal = function($val) {
                if (is_null($val)) return null;
                $cleaned = str_replace(',', '.', trim(str_ireplace('rn', '', (string)$val)));
                return is_numeric($cleaned) ? (float)$cleaned : null;
            };

            $rn1 = [];
            $rn2 = [];

            if (!$isTrial) {
                if (count($allMatching) > 0) {
                    $allIds = $allMatching->pluck('id')->filter()->unique();
                    $allTrials = DurabilityThicknessReport::where('is_trial', true)
                        ->whereIn('data1_id', $allIds)
                        ->get()
                        ->keyBy('data1_id');

                    foreach ($allMatching as $m) {
                        $tr = $allTrials->get($m->id);
                        if ($tr) {
                            $m->aktual_rn_trial = $tr->aktual_rn;
                        }
                    }
                }

                foreach ($allMatching as $item) {
                    $v1 = $parseVal($item->aktual_rn);
                    if (!is_null($v1)) $rn1[] = $v1;

                    if (isset($item->aktual_rn_trial)) {
                        $v2 = $parseVal($item->aktual_rn_trial);
                        if (!is_null($v2)) $rn2[] = $v2;
                    }
                }
            } else {
                if (count($allMatching) > 0) {
                    $data1Ids = $allMatching->pluck('data1_id')->filter()->unique();
                    $allData1 = DurabilityThicknessReport::whereIn('id', $data1Ids)->get()->keyBy('id');

                    foreach ($allMatching as $m) {
                        $d1 = $allData1->get($m->data1_id);
                        if ($d1) {
                            $m->aktual_rn_d1 = $d1->aktual_rn;
                        }
                    }
                }

                foreach ($allMatching as $item) {
                    if (isset($item->aktual_rn_d1)) {
                        $v1 = $parseVal($item->aktual_rn_d1);
                        if (!is_null($v1)) $rn1[] = $v1;
                    }

                    $v2 = $parseVal($item->aktual_rn);
                    if (!is_null($v2)) $rn2[] = $v2;
                }
            }

            $averages = [
                'count' => count($allMatching),
                'rn1' => count($rn1) ? number_format(array_sum($rn1) / count($rn1), 2) : '-',
                'rn2' => count($rn2) ? number_format(array_sum($rn2) / count($rn2), 2) : '-',
            ];
        }
        
        if ($request->has('print')) {
            $reports = $query->get();
            $docHeader = \App\Models\GeneralSetting::getDocHeader('report_standard_performance_test', auth()->check() && auth()->user()->plant ? strtolower(auth()->user()->plant->name) : 'jakarta', [
                'no_dokumen' => '-',
                'tgl_terbit' => '-',
                'revisi' => '- / -',
                'halaman' => '1 / 1'
            ]);
            return view('durability_plating.print', compact('reports', 'docHeader', 'testType', 'isTrial'));
        }

        $reports = $query->paginate(10)->withQueryString();

        if (!$isTrial) {
            $reportIds = $reports->pluck('id')->filter()->unique();

            // ponytail: prefer data1_id (explicit pairing), fall back to lot_no for legacy rows
            $trialReports = DurabilityThicknessReport::where('is_trial', true)
                ->whereIn('data1_id', $reportIds)
                ->get()
                ->keyBy('data1_id');

            // Legacy fallback: rows created before data1_id column existed
            $stdIds = $reports->pluck('standard_performance_test_id')->filter()->unique();
            $lotNos = $reports->pluck('lot_no')->filter()->unique();
            $legacyTrials = DurabilityThicknessReport::where('is_trial', true)
                ->whereNull('data1_id')
                ->whereIn('standard_performance_test_id', $stdIds)
                ->whereIn('lot_no', $lotNos)
                ->get()
                ->keyBy(function ($item) {
                    return $item->standard_performance_test_id . '_' . $item->lot_no;
                });

            foreach ($reports as $report) {
                // Prefer explicit pairing, then legacy lot_no pairing
                $trial = $trialReports->get($report->id)
                    ?? $legacyTrials->get($report->standard_performance_test_id . '_' . $report->lot_no);
                if ($trial) {
                    $report->actual_cr_trial = $trial->actual_cr;
                    $report->actual_ni_trial = $trial->actual_ni;
                    $report->actual_cu_trial = $trial->actual_cu;
                    $report->actual_corrodkote_waktu_trial = $trial->actual_corrodkote_waktu;
                    $report->standar_jam_corrodkote_trial = $trial->standar_jam_corrodkote;
                    $report->aktual_corrosion_trial = $trial->aktual_corrosion;
                    $report->actual_cass_waktu_trial = $trial->actual_cass_waktu;
                    $report->standar_jam_cass_trial = $trial->standar_jam_cass;
                    $report->actual_salt_spray_waktu_trial = $trial->actual_salt_spray_waktu;
                    $report->standar_jam_salt_spray_trial = $trial->standar_jam_salt_spray;
                    $report->actual_porecount_trial = $trial->actual_porecount;
                    $report->result_judgment_trial = $trial->result_judgment;
                    $report->result_judgment_corrodkote_trial = $trial->result_judgment_corrodkote;
                    $report->result_judgment_cass_trial = $trial->result_judgment_cass;
                    $report->result_judgment_salt_spray_trial = $trial->result_judgment_salt_spray;
                    $report->result_judgment_porecount_trial = $trial->result_judgment_porecount;
                    $report->description_trial = $trial->description;
                    $report->description_corrodkote_trial = $trial->description_corrodkote;
                    $report->description_cass_trial = $trial->description_cass;
                    $report->description_salt_spray_trial = $trial->description_salt_spray;
                    $report->description_porecount_trial = $trial->description_porecount;
                    $report->evidence_after_trial = $trial->evidence_after ?: $report->evidence_after_trial;
                    $report->evidence_after_trial_uploaded_at = $trial->evidence_after_uploaded_at ?: $report->evidence_after_trial_uploaded_at;
                }
            }
        } else {
            $data1Ids = $reports->pluck('data1_id')->filter()->unique();
            $data1Reports = DurabilityThicknessReport::whereIn('id', $data1Ids)->get()->keyBy('id');

            $stdIds = $reports->pluck('standard_performance_test_id')->filter()->unique();
            $lotNos = $reports->pluck('lot_no')->filter()->unique();
            $legacyData1 = DurabilityThicknessReport::where('is_trial', false)
                ->whereIn('standard_performance_test_id', $stdIds)
                ->whereIn('lot_no', $lotNos)
                ->get()
                ->keyBy(function ($item) {
                    return $item->standard_performance_test_id . '_' . $item->lot_no;
                });

            foreach ($reports as $report) {
                $data1 = $data1Reports->get($report->data1_id)
                    ?? $legacyData1->get($report->standard_performance_test_id . '_' . $report->lot_no);
                if ($data1) {
                    $isVal = fn($v) => !is_null($v) && trim((string)$v) !== '' && trim((string)$v) !== '-';
                    if (!$isVal($report->actual_cu) && $isVal($data1->actual_cu)) {
                        $report->actual_cu = $data1->actual_cu;
                    }
                    if (!$isVal($report->actual_ni) && $isVal($data1->actual_ni)) {
                        $report->actual_ni = $data1->actual_ni;
                    }
                    if (!$isVal($report->actual_cr) && $isVal($data1->actual_cr)) {
                        $report->actual_cr = $data1->actual_cr;
                    }
                    $report->data1_ref_id = $data1->id;
                }
            }
        }
        
        $testReportIds = \App\Models\DurabilityThicknessReport::where('is_trial', $isTrial)
            ->where(function ($q) use ($testType) {
                if ($testType === 'thickness') {
                    $q->whereNotNull('actual_cu')->where('actual_cu', '!=', '')->where('actual_cu', '!=', '-')
                        ->orWhereNotNull('actual_ni')->where('actual_ni', '!=', '')->where('actual_ni', '!=', '-')
                        ->orWhereNotNull('actual_cr')->where('actual_cr', '!=', '')->where('actual_cr', '!=', '-');
                } elseif ($testType === 'corrodkote') {
                    $q->whereNotNull('standar_jam_corrodkote')->where('standar_jam_corrodkote', '!=', '')->where('standar_jam_corrodkote', '!=', '-');
                } elseif ($testType === 'cass') {
                    $q->whereNotNull('standar_jam_cass')->where('standar_jam_cass', '!=', '')->where('standar_jam_cass', '!=', '-');
                } elseif ($testType === 'salt_spray') {
                    $q->whereNotNull('standar_jam_salt_spray')->where('standar_jam_salt_spray', '!=', '')->where('standar_jam_salt_spray', '!=', '-');
                } elseif ($testType === 'porecount') {
                    $q->whereNotNull('actual_porecount')->where('actual_porecount', '!=', '')->where('actual_porecount', '!=', '-');
                }
            })
            ->pluck('standard_performance_test_id')
            ->filter()
            ->unique();

        $items = \App\Models\StandardPerformanceTest::whereIn('id', $testReportIds)
            ->orderBy('part_name', 'asc')
            ->get();

        $masterItems = \App\Models\StandardPerformanceTest::orderBy('part_name', 'asc')->get();

        $customers = \App\Models\StandardPerformanceTest::whereNotNull('customer_name')
            ->select('customer_name')
            ->distinct()
            ->orderBy('customer_name')
            ->pluck('customer_name');

        $categories = \App\Models\StandardPerformanceTest::whereNotNull('category')
            ->where('category', '!=', '')
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $rekapMatching = (($testType === 'thickness' || $testType === 'corrodkote' || $testType === 'cass') && isset($allMatching)) ? $allMatching : (clone $query)->get();
        $rekapSummary = $rekapMatching->groupBy('standard_performance_test_id')->map(function ($group) {
            $first = $group->first();
            $std = $first->standard ?? null;
            return (object)[
                'part_name' => $std->part_name ?? '-',
                'part_number' => $std->part_number ?? '-',
                'customer_name' => $std->customer_name ?? '-',
                'customer_standard' => $std->customer_standard ?? '-',
                'total' => $group->count(),
            ];
        })->sortBy('part_name')->values();

        return view('durability_plating.report', compact('reports', 'items', 'masterItems', 'customers', 'categories', 'averages', 'rekapSummary', 'testType', 'isTrial'));
    }

    public function updateThickness(Request $request, $id)
    {
        // ponytail: tanggal_test is sent by input modals (Corrodkote/Cass/Salt/Porecount)
        // but the DB column is tanggal_cek. Map it here.
        if ($request->has('tanggal_test') && !$request->has('tanggal_cek')) {
            $request->merge(['tanggal_cek' => $request->tanggal_test]);
        }

        $request->validate([
            'production_date' => 'nullable|date',
            'shift' => 'nullable|string|max:255',
            'lot_no' => 'nullable|string|max:255',
            'actual_cu' => 'nullable|string|max:255',
            'actual_ni' => 'nullable|string|max:255',
            'actual_cr' => 'nullable|string|max:255',
            'actual_cu_trial' => 'nullable|string|max:255',
            'actual_ni_trial' => 'nullable|string|max:255',
            'actual_cr_trial' => 'nullable|string|max:255',
            'actual_corrodkote_waktu' => 'nullable|string|max:255',
            'standar_jam_corrodkote' => 'nullable|string|max:255',
            'aktual_corrosion' => 'nullable|string|max:255',
            'actual_corrodkote_waktu_trial' => 'nullable|string|max:255',
            'standar_jam_corrodkote_trial' => 'nullable|string|max:255',
            'aktual_corrosion_trial' => 'nullable|string|max:255',
            'actual_cass_waktu' => 'nullable|string|max:255',
            'standar_jam_cass' => 'nullable|string|max:255',
            'aktual_rn' => 'nullable|string|max:255',
            'actual_cass_waktu_trial' => 'nullable|string|max:255',
            'standar_jam_cass_trial' => 'nullable|string|max:255',
            'aktual_rn_trial' => 'nullable|string|max:255',
            'actual_salt_spray_waktu' => 'nullable|string|max:255',
            'standar_jam_salt_spray' => 'nullable|string|max:255',
            'actual_porecount' => 'nullable|string|max:255',
            'actual_salt_spray_waktu_trial' => 'nullable|string|max:255',
            'standar_jam_salt_spray_trial' => 'nullable|string|max:255',
            'actual_porecount_trial' => 'nullable|string|max:255',
            'result_judgment' => 'nullable|string|max:255',
            'result_judgment_corrodkote' => 'nullable|string|max:255',
            'result_judgment_cass' => 'nullable|string|max:255',
            'result_judgment_salt_spray' => 'nullable|string|max:255',
            'result_judgment_porecount' => 'nullable|string|max:255',
            'result_judgment_trial' => 'nullable|string|max:255',
            'result_judgment_corrodkote_trial' => 'nullable|string|max:255',
            'result_judgment_cass_trial' => 'nullable|string|max:255',
            'result_judgment_salt_spray_trial' => 'nullable|string|max:255',
            'result_judgment_porecount_trial' => 'nullable|string|max:255',
            'tgl_masuk' => 'nullable|date',
            'jam_masuk' => 'nullable|date_format:H:i',
            'tgl_keluar' => 'nullable|date',
            'jam_keluar' => 'nullable|date_format:H:i',
            'tanggal_cek' => 'nullable|date',
            'description' => 'nullable|string',
            'description_corrodkote' => 'nullable|string',
            'description_cass' => 'nullable|string',
            'description_salt_spray' => 'nullable|string',
            'description_porecount' => 'nullable|string',
            'description_trial' => 'nullable|string',
            'description_corrodkote_trial' => 'nullable|string',
            'description_cass_trial' => 'nullable|string',
            'description_salt_spray_trial' => 'nullable|string',
            'description_porecount_trial' => 'nullable|string',
        ]);

        $report = DurabilityThicknessReport::findOrFail($id);
        
        $updateData = [];
        $fields = [
            'production_date', 'shift', 'lot_no', 'actual_cu', 'actual_ni', 'actual_cr',
            'actual_corrodkote_waktu', 'standar_jam_corrodkote', 'aktual_corrosion', 'actual_cass_waktu', 'standar_jam_cass', 'aktual_rn',
            'actual_salt_spray_waktu', 'standar_jam_salt_spray', 'actual_porecount',
            'result_judgment', 'result_judgment_corrodkote', 'result_judgment_cass', 'result_judgment_salt_spray', 'result_judgment_porecount',
            'tgl_masuk', 'jam_masuk', 'tgl_keluar', 'jam_keluar', 'tanggal_cek',
            'description', 'description_corrodkote', 'description_cass', 'description_salt_spray', 'description_porecount'
        ];

        $testType = $request->input('test_type', $request->query('type', 'thickness'));

        foreach ($fields as $field) {
            if ($request->has($field)) {
                // Protect Thickness-only measurement values from being altered by non-thickness test inputs
                if ($testType !== 'thickness' && in_array($field, [
                    'actual_cu', 'actual_ni', 'actual_cr',
                ])) {
                    continue;
                }
                $updateData[$field] = $request->$field;
            }
        }

        // Set updated_by and per-test-type PIC (update PIC to current user who modified/updated data)
        $updateData['updated_by'] = auth()->id();

        $analisColumn = match($testType) {
            'corrodkote'  => 'analis_corrodkote_id',
            'cass'        => 'analis_cass_id',
            'salt_spray'  => 'analis_salt_spray_id',
            'porecount'   => 'analis_porecount_id',
            default       => 'analis_id',
        };
        if ($analisColumn) {
            $updateData[$analisColumn] = auth()->id();
        }

        if (!empty($updateData)) {
            $report->update($updateData);
        }

        // Update or create Data 2 record when updating Data 1
        if (!$report->is_trial) {
            // ponytail: prefer explicit data1_id FK, fall back to lot_no for legacy rows
            $trialReport = DurabilityThicknessReport::where('is_trial', true)
                ->where('data1_id', $report->id)
                ->first()
                ?? DurabilityThicknessReport::where('is_trial', true)
                    ->whereNull('data1_id')
                    ->where('standard_performance_test_id', $report->standard_performance_test_id)
                    ->where('lot_no', $report->lot_no)
                    ->first();

            if (!$trialReport) {
                $trialReport = new DurabilityThicknessReport();
                $trialReport->standard_performance_test_id = $report->standard_performance_test_id;
                $trialReport->is_trial = true;
                $trialReport->lot_no = $report->lot_no;
                $trialReport->data1_id = $report->id;
            } elseif (!$trialReport->data1_id) {
                // Backfill data1_id on legacy rows on first encounter
                $trialReport->data1_id = $report->id;
            }

            $trialData = [
                'tanggal_cek' => $report->tanggal_cek,
                'production_date' => $report->production_date,
                'shift' => $report->shift,
                'lot_no' => $report->lot_no,
                'tgl_masuk' => $report->tgl_masuk,
                'jam_masuk' => $report->jam_masuk,
                'tgl_keluar' => $report->tgl_keluar,
                'jam_keluar' => $report->jam_keluar,
                'updated_by' => auth()->id(),
            ];
            if ($analisColumn) {
                $trialData[$analisColumn] = auth()->id();
            }
            $trialReport->fill($trialData);

            if ($testType === 'thickness') {
                if ($request->has('actual_cr_trial')) $trialReport->actual_cr = $request->actual_cr_trial;
                if ($request->has('actual_ni_trial')) $trialReport->actual_ni = $request->actual_ni_trial;
                if ($request->has('actual_cu_trial')) $trialReport->actual_cu = $request->actual_cu_trial;
                if ($request->has('result_judgment_trial') && $request->result_judgment_trial !== '-') $trialReport->result_judgment = $request->result_judgment_trial;
                if ($request->has('description_trial')) $trialReport->description = $request->description_trial;
            }

            // ponytail: use filled() not has() — has() returns true for empty string, overwriting saved data
            if ($request->filled('actual_corrodkote_waktu_trial')) $trialReport->actual_corrodkote_waktu = $request->actual_corrodkote_waktu_trial;
            if ($request->filled('standar_jam_corrodkote_trial')) $trialReport->standar_jam_corrodkote = $request->standar_jam_corrodkote_trial;
            if ($request->filled('aktual_corrosion_trial')) $trialReport->aktual_corrosion = $request->aktual_corrosion_trial;

            if ($request->filled('actual_cass_waktu_trial')) $trialReport->actual_cass_waktu = $request->actual_cass_waktu_trial;
            if ($request->filled('standar_jam_cass_trial')) $trialReport->standar_jam_cass = $request->standar_jam_cass_trial;
            if ($request->filled('aktual_rn_trial')) $trialReport->aktual_rn = $request->aktual_rn_trial;

            if ($request->filled('actual_salt_spray_waktu_trial')) $trialReport->actual_salt_spray_waktu = $request->actual_salt_spray_waktu_trial;
            if ($request->filled('standar_jam_salt_spray_trial')) $trialReport->standar_jam_salt_spray = $request->standar_jam_salt_spray_trial;

            if ($request->filled('actual_porecount_trial')) $trialReport->actual_porecount = $request->actual_porecount_trial;

            if ($request->filled('result_judgment_corrodkote_trial') && $request->result_judgment_corrodkote_trial !== '-') $trialReport->result_judgment_corrodkote = $request->result_judgment_corrodkote_trial;
            if ($request->filled('result_judgment_cass_trial') && $request->result_judgment_cass_trial !== '-') $trialReport->result_judgment_cass = $request->result_judgment_cass_trial;
            if ($request->filled('result_judgment_salt_spray_trial') && $request->result_judgment_salt_spray_trial !== '-') $trialReport->result_judgment_salt_spray = $request->result_judgment_salt_spray_trial;
            if ($request->filled('result_judgment_porecount_trial') && $request->result_judgment_porecount_trial !== '-') $trialReport->result_judgment_porecount = $request->result_judgment_porecount_trial;

            if ($request->filled('description_corrodkote_trial')) $trialReport->description_corrodkote = $request->description_corrodkote_trial;
            if ($request->filled('description_cass_trial')) $trialReport->description_cass = $request->description_cass_trial;
            if ($request->filled('description_salt_spray_trial')) $trialReport->description_salt_spray = $request->description_salt_spray_trial;
            if ($request->filled('description_porecount_trial')) $trialReport->description_porecount = $request->description_porecount_trial;

            $trialReport->save();
        }
        
        // Handle X-button deletions before processing new uploads
        if ($request->input('delete_evidence_before') === '1') {
            if ($report->evidence_before && file_exists(public_path($report->evidence_before))) {
                @unlink(public_path($report->evidence_before));
            }
            $report->update(['evidence_before' => null, 'evidence_before_uploaded_at' => null]);
            DurabilityThicknessReport::where('standard_performance_test_id', $report->standard_performance_test_id)
                ->where('is_trial', true)
                ->where('lot_no', $report->lot_no)
                ->update(['evidence_before' => null, 'evidence_before_uploaded_at' => null]);
        }

        if ($request->input('delete_evidence_after') === '1') {
            if ($report->evidence_after && file_exists(public_path($report->evidence_after))) {
                @unlink(public_path($report->evidence_after));
            }
            $report->update(['evidence_after' => null, 'evidence_after_uploaded_at' => null]);
        }

        if ($request->input('delete_evidence_after_trial') === '1') {
            if ($report->evidence_after_trial && file_exists(public_path($report->evidence_after_trial))) {
                @unlink(public_path($report->evidence_after_trial));
            }
            $report->update(['evidence_after_trial' => null, 'evidence_after_trial_uploaded_at' => null]);
            if (isset($trialReport) && $trialReport) {
                if ($trialReport->evidence_after && file_exists(public_path($trialReport->evidence_after))) {
                    @unlink(public_path($trialReport->evidence_after));
                }
                $trialReport->update(['evidence_after' => null, 'evidence_after_uploaded_at' => null]);
            }
        }

        if ($request->hasFile('evidence_before')) {
            if ($report->evidence_before && file_exists(public_path($report->evidence_before))) {
                @unlink(public_path($report->evidence_before));
            }
            $fileBefore = $request->file('evidence_before');
            $filenameBefore = time() . '_before_' . $fileBefore->getClientOriginalName();
            $fileBefore->move(public_path('uploads/durability_plating'), $filenameBefore);
            $newBeforePath = 'uploads/durability_plating/' . $filenameBefore;
            $report->update([
                'evidence_before' => $newBeforePath,
                'evidence_before_uploaded_at' => now()
            ]);
            DurabilityThicknessReport::where('standard_performance_test_id', $report->standard_performance_test_id)
                ->where('is_trial', true)
                ->where('lot_no', $report->lot_no)
                ->update([
                    'evidence_before' => $newBeforePath,
                    'evidence_before_uploaded_at' => now()
                ]);
        }

        if ($request->hasFile('evidence_after')) {
            if ($report->evidence_after && file_exists(public_path($report->evidence_after))) {
                @unlink(public_path($report->evidence_after));
            }
            $fileAfter = $request->file('evidence_after');
            $filenameAfter = time() . '_after_' . $fileAfter->getClientOriginalName();
            $fileAfter->move(public_path('uploads/durability_plating'), $filenameAfter);
            $newAfterPath = 'uploads/durability_plating/' . $filenameAfter;
            $report->update([
                'evidence_after' => $newAfterPath,
                'evidence_after_uploaded_at' => now()
            ]);
        }

        if ($request->hasFile('evidence_after_trial')) {
            if ($report->evidence_after_trial && file_exists(public_path($report->evidence_after_trial))) {
                @unlink(public_path($report->evidence_after_trial));
            }
            $fileAfterTrial = $request->file('evidence_after_trial');
            $filenameAfterTrial = time() . '_after_data2_' . $fileAfterTrial->getClientOriginalName();
            $fileAfterTrial->move(public_path('uploads/durability_plating'), $filenameAfterTrial);
            $newAfterTrialPath = 'uploads/durability_plating/' . $filenameAfterTrial;
            
            $report->update([
                'evidence_after_trial' => $newAfterTrialPath,
                'evidence_after_trial_uploaded_at' => now()
            ]);

            if (isset($trialReport) && $trialReport) {
                $trialReport->update([
                    'evidence_after' => $newAfterTrialPath,
                    'evidence_after_uploaded_at' => now()
                ]);
            }
        }
        
        $std = $report->standardPerformanceTest;
        $partName = $std ? $std->part_name : 'Part';
        $lotNo = $report->lot_no ?: '-';
        $dataTag = $report->is_trial ? '[DATA 2]' : '[DATA 1]';

        ActivityLogger::log('updated', $report, "Memperbarui Laporan Durability Plating {$dataTag}: {$partName} (Lot: {$lotNo})");

        if (isset($trialReport) && $trialReport) {
            ActivityLogger::log('updated', $trialReport, "Memperbarui Laporan Durability Plating [DATA 2]: {$partName} (Lot: {$lotNo})");
        }

        return redirect()->back()->with('success', 'Data berhasil diupdate.');
    }

    private function isFieldEmpty($value) {
        $val = trim($value);
        return is_null($value) || $val === '' || $val === '-';
    }

    private function clearTestData($report, $type, $clearPaired = true) {
        if ($type === 'thickness') {
            $report->actual_cu = null;
            $report->actual_ni = null;
            $report->actual_cr = null;
        } elseif ($type === 'corrodkote') {
            $report->actual_corrodkote_waktu = null;
            $report->standar_jam_corrodkote = null;
            $report->aktual_corrosion = null;
        } elseif ($type === 'cass') {
            $report->actual_cass_waktu = null;
            $report->standar_jam_cass = null;
        } elseif ($type === 'salt_spray') {
            $report->actual_salt_spray_waktu = null;
            $report->standar_jam_salt_spray = null;
        } elseif ($type === 'porecount') {
            $report->actual_porecount = null;
        }
        
        $allEmpty = $this->isFieldEmpty($report->actual_cu) && $this->isFieldEmpty($report->actual_ni) && $this->isFieldEmpty($report->actual_cr)
            && $this->isFieldEmpty($report->actual_corrodkote_waktu) && $this->isFieldEmpty($report->standar_jam_corrodkote)
            && $this->isFieldEmpty($report->actual_cass_waktu) && $this->isFieldEmpty($report->standar_jam_cass)
            && $this->isFieldEmpty($report->actual_salt_spray_waktu) && $this->isFieldEmpty($report->standar_jam_salt_spray)
            && $this->isFieldEmpty($report->actual_porecount);

        if ($allEmpty) {
            $report->delete();
        } else {
            $report->save();
        }

        if ($clearPaired) {
            $pairedReport = null;
            if (!$report->is_trial) {
                // DATA 1 searching for DATA 2
                $pairedReport = DurabilityThicknessReport::where('is_trial', true)
                    ->where('data1_id', $report->id)
                    ->first();
            } else if ($report->data1_id) {
                // DATA 2 searching for DATA 1
                $pairedReport = DurabilityThicknessReport::find($report->data1_id);
            }

            // Fallback for legacy records without data1_id
            if (!$pairedReport && $report->standard_performance_test_id && $report->lot_no) {
                $pairedReport = DurabilityThicknessReport::where('standard_performance_test_id', $report->standard_performance_test_id)
                    ->where('lot_no', $report->lot_no)
                    ->where('is_trial', !$report->is_trial)
                    ->first();
            }

            if ($pairedReport) {
                $this->clearTestData($pairedReport, $type, false);
            }
        }
    }

    public function destroyThickness(Request $request, $id)
    {
        $report = DurabilityThicknessReport::with('standardPerformanceTest')->findOrFail($id);
        $type = $request->query('type', 'thickness');
        $std = $report->standardPerformanceTest;
        $partName = $std ? $std->part_name : 'Part';
        $lotNo = $report->lot_no ?: '-';
        $typeName = ucwords(str_replace('_', ' ', $type));
        
        $this->clearTestData($report, $type);

        ActivityLogger::log('deleted', $report, "Menghapus data pengujian {$typeName}: {$partName} (Lot: {$lotNo})");

        return redirect()->back()->with('success', "Data $typeName berhasil dihapus.");
    }

    public function bulkDestroyThickness(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:durability_thickness_reports,id',
            'type' => 'nullable|string'
        ]);

        $type = $request->input('type', 'thickness');
        $count = count($request->ids);
        
        foreach($request->ids as $id) {
            $report = DurabilityThicknessReport::find($id);
            if ($report) {
                $this->clearTestData($report, $type);
            }
        }

        $typeName = strtoupper(str_replace('_', ' ', $type));
        ActivityLogger::log('deleted', null, "Hapus Massal Data $typeName Durability Plating ({$count} data)");

        return response()->json([
            'success' => true,
            'message' => "Berhasil menghapus {$count} data laporan $typeName."
        ]);
    }

    public function bulkCopyThickness(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:durability_thickness_reports,id',
        ]);

        $count = 0;
        foreach ($request->ids as $id) {
            $sourceReport = DurabilityThicknessReport::find($id);
            if (!$sourceReport) continue;

            $newLotNo = $sourceReport->lot_no;

            // Replicate Data 1 record
            $newReport = $sourceReport->replicate();
            $newReport->lot_no = $newLotNo;
            $newReport->created_at = now();
            $newReport->updated_at = now();
            $newReport->save();

            // Replicate Data 2 Trial record if exists
            if (!$sourceReport->is_trial) {
                $sourceTrial = DurabilityThicknessReport::where('standard_performance_test_id', $sourceReport->standard_performance_test_id)
                    ->where('is_trial', true)
                    ->where('lot_no', $sourceReport->lot_no)
                    ->first();

                if ($sourceTrial) {
                    $newTrial = $sourceTrial->replicate();
                    $newTrial->lot_no = $newLotNo;
                    $newTrial->created_at = now();
                    $newTrial->updated_at = now();
                    $newTrial->save();
                }
            }

            $count++;
        }

        ActivityLogger::log('created', null, "Duplikasi Massal Laporan Durability Plating ({$count} data)");

        return response()->json([
            'success' => true,
            'message' => "Berhasil menyalin {$count} data laporan."
        ]);
    }

    protected function getApprovalMapping($type)
    {
        $mappings = [
            'supervisor' => ['field' => 'supervisor_qc', 'time' => 'supervisor_approved_at', 'label' => 'SPV Quality'],
            'supervisor_plating' => ['field' => 'supervisor_plating', 'time' => 'supervisor_plating_approved_at', 'label' => 'SPV Plating'],
            'asst_manager' => ['field' => 'asst_manager_qc', 'time' => 'asst_manager_approved_at', 'label' => 'Asst Manager Quality'],
            'asst_manager_plating' => ['field' => 'asst_manager_plating', 'time' => 'asst_manager_plating_approved_at', 'label' => 'Asst Manager Plating'],
        ];
        return $mappings[$type] ?? null;
    }

    public function approve(Request $request, $id, $type)
    {
        try {
            $mapping = $this->getApprovalMapping($type);
            if (!$mapping) {
                return redirect()->back()->with('error', 'Level approval tidak valid.');
            }

            $user = auth()->user();
            $allowedRoles = [$type, 'admin'];
            if ($type === 'supervisor') $allowedRoles[] = 'supervisor_qc';
            if ($type === 'asst_manager') $allowedRoles[] = 'asst_manager_qc';

            if (!in_array($user->role, $allowedRoles)) {
                return redirect()->back()->with('error', 'Anda tidak memiliki hak akses untuk approval ini.');
            }

            $report = DurabilityThicknessReport::findOrFail($id);
            $field = $mapping['field'];
            $timeField = $mapping['time'];
            $label = $mapping['label'];

            $userName = $user->name ?? $user->username ?? 'User';
            $now = now();

            $report->$field = $userName;
            $report->$timeField = $now;
            $report->save();

            // Sync to paired report (Data 1 / Data 2 Trial) if it is also OK (not NG)
            $pairedQuery = $report->data1_id
                ? DurabilityThicknessReport::where('id', $report->data1_id)
                : DurabilityThicknessReport::where('data1_id', $report->id);

            $this->applyResultJudgmentFilter($pairedQuery, $testType ?? 'thickness', 'OK');

            $pairedQuery->where(function($q) use ($field) {
                $q->whereNull($field)->orWhere($field, 'REJECTED');
            })->update([
                $field => $userName,
                $timeField => $now,
            ]);

            ActivityLogger::log('approved', $report, "Melakukan approval ({$label}) pada laporan Durability Plating: {$report->standard->part_name} (Lot: {$report->lot_no})");

            return redirect()->back()->with('success', "Laporan berhasil di-approve oleh {$label}.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, $id, $type)
    {
        try {
            $mapping = $this->getApprovalMapping($type);
            if (!$mapping) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Level approval tidak valid.'], 422);
                }
                return redirect()->back()->with('error', 'Level approval tidak valid.');
            }

            $request->validate([
                'rejection_remarks' => 'required|string|max:1000',
            ]);

            $user = auth()->user();
            $report = DurabilityThicknessReport::findOrFail($id);
            $field = $mapping['field'];
            $timeField = $mapping['time'];
            $label = $mapping['label'];

            $userName = $user->name ?? $user->username ?? 'User';
            $remarks = '[' . now()->format('d/m/Y H:i') . ' ' . $userName . ']: ' . $request->rejection_remarks;
            $now = now();

            $report->$field = 'REJECTED';
            $report->$timeField = $now;
            $report->rejection_remarks = $remarks;
            $report->save();

            // Sync to paired report
            if ($report->data1_id) {
                DurabilityThicknessReport::where('id', $report->data1_id)->update([
                    $field => 'REJECTED',
                    $timeField => $now,
                    'rejection_remarks' => $remarks,
                ]);
            } else {
                DurabilityThicknessReport::where('data1_id', $report->id)->update([
                    $field => 'REJECTED',
                    $timeField => $now,
                    'rejection_remarks' => $remarks,
                ]);
            }

            ActivityLogger::log('rejected', $report, "Melakukan penolakan ({$label}) pada laporan Durability Plating: {$report->standard->part_name} (Lot: {$report->lot_no})");

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Laporan berhasil ditolak.']);
            }

            return redirect()->back()->with('success', 'Laporan berhasil ditolak.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function bulkApprove(Request $request)
    {
        try {
            $type = $request->approval_type ?? auth()->user()->role;
            $mapping = $this->getApprovalMapping($type);
            if (!$mapping) {
                return response()->json(['success' => false, 'message' => 'Level approval tidak valid.'], 422);
            }

            $field = $mapping['field'];
            $timeField = $mapping['time'];
            $user = auth()->user();
            $userName = $user->name ?? $user->username ?? 'User';

            $isTrial = $request->boolean('is_trial', false);
            $testType = $request->input('test_type', 'thickness');

            $query = DurabilityThicknessReport::query();
            $query->where('is_trial', $isTrial);

            if ($request->filled('start_date')) {
                $query->whereDate('tanggal_cek', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('tanggal_cek', '<=', $request->end_date);
            }
            if ($request->filled('result_judgment')) {
                $this->applyResultJudgmentFilter($query, $testType, $request->result_judgment);
            }
            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('standard', function($q) use ($search) {
                    $q->where('part_name', 'like', "%$search%")
                      ->orWhere('part_number', 'like', "%$search%")
                      ->orWhere('customer_name', 'like', "%$search%")
                      ->orWhere('customer_standard', 'like', "%$search%");
                });
            }
            if ($request->filled('customer_name')) {
                $customerName = $request->customer_name;
                $query->whereHas('standard', function($q) use ($customerName) {
                    $q->where('customer_name', $customerName);
                });
            }
            if ($request->filled('category')) {
                $category = $request->category;
                $query->whereHas('standard', function($q) use ($category) {
                    $q->where('category', $category);
                });
            }

            $reportsToApprove = $query->where(function($q) use ($field) {
                $q->whereNull($field)->orWhere($field, 'REJECTED');
            })->get();

            $now = now();
            $updatedCount = 0;
            foreach ($reportsToApprove as $rep) {
                $rep->update([
                    $field => $userName,
                    $timeField => $now,
                ]);

                // Sync paired report (Data 1 / Data 2 Trial) if it also matches the result filter (and is not NG)
                $pairedQuery = $rep->data1_id
                    ? DurabilityThicknessReport::where('id', $rep->data1_id)
                    : DurabilityThicknessReport::where('data1_id', $rep->id);

                $judgmentFilter = $request->filled('result_judgment') ? $request->result_judgment : 'OK';
                $this->applyResultJudgmentFilter($pairedQuery, $testType, $judgmentFilter);

                $pairedQuery->where(function($q) use ($field) {
                    $q->whereNull($field)->orWhere($field, 'REJECTED');
                })->update([
                    $field => $userName,
                    $timeField => $now,
                ]);
                $updatedCount++;
            }

            return response()->json([
                'success' => true,
                'message' => "Berhasil meng-approve {$updatedCount} data laporan.",
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateApproval(Request $request, $id)
    {
        try {
            $report = DurabilityThicknessReport::findOrFail($id);
            $user = auth()->user();
            $userName = $user->name ?? $user->username ?? 'User';
            $now = now();

            $fields = [
                'supervisor_qc' => 'supervisor_approved_at',
                'supervisor_plating' => 'supervisor_plating_approved_at',
                'asst_manager_qc' => 'asst_manager_approved_at',
                'asst_manager_plating' => 'asst_manager_plating_approved_at',
            ];

            $updateData = [];
            foreach ($fields as $field => $timeField) {
                if ($request->has($field)) {
                    $val = $request->input($field);
                    if ($val === 'Approved') {
                        $updateData[$field] = $userName;
                        $updateData[$timeField] = $report->$timeField ?? $now;
                    } elseif ($val === 'REJECTED' || $val === 'Rejected') {
                        $updateData[$field] = 'REJECTED';
                        $updateData[$timeField] = $report->$timeField ?? $now;
                    } else { // Pending / empty
                        $updateData[$field] = null;
                        $updateData[$timeField] = null;
                    }
                }
            }

            if (!empty($updateData)) {
                $report->update($updateData);

                // Sync to paired report (Data 1 / Data 2 Trial)
                if ($report->data1_id) {
                    DurabilityThicknessReport::where('id', $report->data1_id)->update($updateData);
                } else {
                    DurabilityThicknessReport::where('data1_id', $report->id)->update($updateData);
                }

                ActivityLogger::log('updated', $report, "Memperbarui status approval pada laporan Durability Plating: {$report->standard->part_name} (Lot: {$report->lot_no})");
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status approval berhasil diperbarui.',
                ]);
            }

            return redirect()->back()->with('success', 'Status approval berhasil diperbarui.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    private function applyResultJudgmentFilter($query, $testType, $resultJudgment)
    {
        if (empty($resultJudgment)) {
            return;
        }

        $val = strtoupper(trim($resultJudgment));

        $targetColumn = match ($testType) {
            'corrodkote' => 'result_judgment_corrodkote',
            'cass' => 'result_judgment_cass',
            'salt_spray' => 'result_judgment_salt_spray',
            'porecount' => 'result_judgment_porecount',
            default => 'result_judgment',
        };

        $query->where(function ($q) use ($val, $testType, $targetColumn) {
            if ($val === 'NG') {
                $q->where($targetColumn, 'LIKE', '%NG%');

                if ($testType === 'salt_spray') {
                    $q->orWhere($targetColumn, 'LIKE', '%WHITE%')
                      ->orWhere($targetColumn, 'LIKE', '%RED%');
                }

                if ($testType === 'thickness') {
                    $q->orWhereHas('standard', function ($sq) {
                        $sq->whereRaw('(durability_thickness_reports.actual_cr REGEXP "^[0-9]+([.][0-9]+)?$" AND CAST(durability_thickness_reports.actual_cr AS DECIMAL(10,4)) < CAST(standard_performance_tests.thickness_cr AS DECIMAL(10,4)))')
                          ->orWhereRaw('(durability_thickness_reports.actual_ni REGEXP "^[0-9]+([.][0-9]+)?$" AND CAST(durability_thickness_reports.actual_ni AS DECIMAL(10,4)) < CAST(standard_performance_tests.thickness_ni AS DECIMAL(10,4)))')
                          ->orWhereRaw('(durability_thickness_reports.actual_cu REGEXP "^[0-9]+([.][0-9]+)?$" AND CAST(durability_thickness_reports.actual_cu AS DECIMAL(10,4)) < CAST(standard_performance_tests.thickness_cu AS DECIMAL(10,4)))');
                    });
                }
            } elseif ($val === 'OK') {
                $q->where(function ($sub) use ($targetColumn, $testType) {
                    $sub->where($targetColumn, 'LIKE', '%OK%');
                    if ($testType === 'salt_spray') {
                        $sub->orWhere($targetColumn, 'LIKE', '%NO RUST%');
                    }
                });

                // Must NOT be NG in the target column
                $q->where($targetColumn, 'NOT LIKE', '%NG%');
                if ($testType === 'salt_spray') {
                    $q->where($targetColumn, 'NOT LIKE', '%WHITE%')
                      ->where($targetColumn, 'NOT LIKE', '%RED%');
                }

                // For thickness, must NOT have any actual measurement less than standard
                if ($testType === 'thickness') {
                    $q->whereDoesntHave('standard', function ($sq) {
                        $sq->whereRaw('(durability_thickness_reports.actual_cr REGEXP "^[0-9]+([.][0-9]+)?$" AND CAST(durability_thickness_reports.actual_cr AS DECIMAL(10,4)) < CAST(standard_performance_tests.thickness_cr AS DECIMAL(10,4)))')
                          ->orWhereRaw('(durability_thickness_reports.actual_ni REGEXP "^[0-9]+([.][0-9]+)?$" AND CAST(durability_thickness_reports.actual_ni AS DECIMAL(10,4)) < CAST(standard_performance_tests.thickness_ni AS DECIMAL(10,4)))')
                          ->orWhereRaw('(durability_thickness_reports.actual_cu REGEXP "^[0-9]+([.][0-9]+)?$" AND CAST(durability_thickness_reports.actual_cu AS DECIMAL(10,4)) < CAST(standard_performance_tests.thickness_cu AS DECIMAL(10,4)))');
                    });
                }
            } elseif (str_contains($val, 'WHITE')) {
                $q->where($targetColumn, 'LIKE', '%WHITE%');
            } elseif (str_contains($val, 'RED')) {
                $q->where($targetColumn, 'LIKE', '%RED%');
            } else {
                $q->where($targetColumn, 'LIKE', "%$val%");
            }
        });
    }
}