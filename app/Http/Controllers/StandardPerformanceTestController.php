<?php

namespace App\Http\Controllers;

use App\Models\StandardPerformanceTest;
use App\Models\DurabilityThicknessReport;
use Illuminate\Http\Request;
use App\Helpers\ActivityLogger;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
class StandardPerformanceTestController extends Controller
{
    public function index(Request $request)
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

        return view('durability_plating.index', compact('standards'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'part_name' => 'required|string|max:255',
            'customer_name' => 'nullable|string|max:255',
            'customer_standard' => 'nullable|string|max:255',
            'thickness_cu' => 'nullable|string|max:255',
            'thickness_ni' => 'nullable|string|max:255',
            'thickness_cr' => 'nullable|string|max:255',
            'thickness_freq' => 'nullable|string|max:255',
            'corrodkote_time' => 'nullable|string|max:255',
            'corrodkote_std_max_corrosion' => 'nullable|string|max:255',
            'corrodkote_freq' => 'nullable|string|max:255',
            'cass_time' => 'nullable|string|max:255',
            'cass_std_min_rn' => 'nullable|string|max:255',
            'cass_freq' => 'nullable|string|max:255',
            'salt_spray_time' => 'nullable|string|max:255',
            'salt_spray_std_rusting' => 'nullable|string|max:255',
            'salt_spray_freq' => 'nullable|string|max:255',
            'porecount_std_min' => 'nullable|string|max:255',
            'porecount_freq' => 'nullable|string|max:255',
        ]);

        $standard = StandardPerformanceTest::create($validated);
        
        ActivityLogger::log('created', $standard, "Menambahkan Master Data Standard Performance Test: {$standard->part_name}");

        return redirect()->back()->with('success', 'Master data berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $standard = StandardPerformanceTest::findOrFail($id);

        $validated = $request->validate([
            'part_name' => 'required|string|max:255',
            'customer_name' => 'nullable|string|max:255',
            'customer_standard' => 'nullable|string|max:255',
            'thickness_cu' => 'nullable|string|max:255',
            'thickness_ni' => 'nullable|string|max:255',
            'thickness_cr' => 'nullable|string|max:255',
            'thickness_freq' => 'nullable|string|max:255',
            'corrodkote_time' => 'nullable|string|max:255',
            'corrodkote_std_max_corrosion' => 'nullable|string|max:255',
            'corrodkote_freq' => 'nullable|string|max:255',
            'cass_time' => 'nullable|string|max:255',
            'cass_std_min_rn' => 'nullable|string|max:255',
            'cass_freq' => 'nullable|string|max:255',
            'salt_spray_time' => 'nullable|string|max:255',
            'salt_spray_std_rusting' => 'nullable|string|max:255',
            'salt_spray_freq' => 'nullable|string|max:255',
            'porecount_std_min' => 'nullable|string|max:255',
            'porecount_freq' => 'nullable|string|max:255',
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
            'No.', 'Nama Part', 'Customer', 'Standard Customer', 'Cr', 'Ni', 'Cu', 'Frek. Thickness',
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
                    $item->customer_name,
                    $item->customer_standard,
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
                    1, 'Sample Part A', 'HONDA', 'HES', '20', '15', '10', '1x/Shift',
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
            // Skip header (row 0)
            foreach (array_slice($rows, 1) as $row) {
                if (empty(trim($row[1]))) continue; // Skip if Nama Part is empty
                
                StandardPerformanceTest::updateOrCreate(
                    ['part_name' => trim($row[1])],
                    [
                        'customer_name' => $row[2] ?? null,
                        'customer_standard' => $row[3] ?? null,
                        'thickness_cr' => $row[4] ?? null,
                        'thickness_ni' => $row[5] ?? null,
                        'thickness_cu' => $row[6] ?? null,
                        'thickness_freq' => $row[7] ?? null,
                        'corrodkote_time' => $row[8] ?? null,
                        'corrodkote_std_max_corrosion' => $row[9] ?? null,
                        'corrodkote_freq' => $row[10] ?? null,
                        'cass_time' => $row[11] ?? null,
                        'cass_std_min_rn' => $row[12] ?? null,
                        'cass_freq' => $row[13] ?? null,
                        'salt_spray_time' => $row[14] ?? null,
                        'salt_spray_std_rusting' => $row[15] ?? null,
                        'salt_spray_freq' => $row[16] ?? null,
                        'porecount_std_min' => $row[17] ?? null,
                        'porecount_freq' => $row[18] ?? null,
                    ]
                );
                $count++;
            }
            
            ActivityLogger::log('imported', null, "Mengimport $count Master Data Standard Performance Test");
            
            return redirect()->back()->with('success', "$count data berhasil diimport.");
            
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
            'actual_corrodkote' => 'nullable|string|max:255',
            'actual_cass_waktu' => 'nullable|string|max:255',
            'actual_cass' => 'nullable|string|max:255',
            'actual_salt_spray_waktu' => 'nullable|string|max:255',
            'actual_salt_spray' => 'nullable|string|max:255',
            'actual_porecount' => 'nullable|string|max:255',
            'result_judgment' => 'nullable|string|max:255',
            'tgl_masuk' => 'nullable|date',
            'jam_masuk' => 'nullable|date_format:H:i',
            'tgl_keluar' => 'nullable|date',
            'jam_keluar' => 'nullable|date_format:H:i',
            'description' => 'nullable|string'
        ]);

        $report = DurabilityThicknessReport::create([
            'standard_performance_test_id' => $request->standard_performance_test_id,
            'production_date' => $request->production_date,
            'shift' => $request->shift,
            'lot_no' => $request->lot_no,
            'actual_cu' => $request->actual_cu,
            'actual_ni' => $request->actual_ni,
            'actual_cr' => $request->actual_cr,
            'actual_corrodkote_waktu' => $request->actual_corrodkote_waktu ?? '-',
            'actual_corrodkote' => $request->actual_corrodkote ?? '-',
            'actual_cass_waktu' => $request->actual_cass_waktu ?? '-',
            'actual_cass' => $request->actual_cass ?? '-',
            'actual_salt_spray_waktu' => $request->actual_salt_spray_waktu ?? '-',
            'actual_salt_spray' => $request->actual_salt_spray ?? '-',
            'actual_porecount' => $request->actual_porecount ?? '-',
            'result_judgment' => $request->result_judgment ?? '-',
            'tgl_masuk' => $request->tgl_masuk,
            'jam_masuk' => $request->jam_masuk,
            'tgl_keluar' => $request->tgl_keluar,
            'jam_keluar' => $request->jam_keluar,
            'tanggal_cek' => now()->toDateString(),
            'analis_id' => auth()->id(),
            'description' => $request->description,
        ]);
        
        if ($request->hasFile('evidence_before')) {
            $fileBefore = $request->file('evidence_before');
            $filenameBefore = time() . '_before_' . $fileBefore->getClientOriginalName();
            $fileBefore->move(public_path('uploads/durability_plating'), $filenameBefore);
            $report->update([
                'evidence_before' => 'uploads/durability_plating/' . $filenameBefore,
                'evidence_before_uploaded_at' => now()
            ]);
        }
        
        if ($request->hasFile('evidence_after')) {
            $fileAfter = $request->file('evidence_after');
            $filenameAfter = time() . '_after_' . $fileAfter->getClientOriginalName();
            $fileAfter->move(public_path('uploads/durability_plating'), $filenameAfter);
            $report->update([
                'evidence_after' => 'uploads/durability_plating/' . $filenameAfter,
                'evidence_after_uploaded_at' => now()
            ]);
        }
        
        ActivityLogger::log('created', null, "Input Thickness Report untuk ID: {$request->standard_performance_test_id}");

        return redirect()->back()->with('success', 'Data Thickness berhasil disimpan.');
    }

    public function report(Request $request)
    {
        return $this->renderReport($request, 'thickness');
    }

    public function reportCorrodkote(Request $request) { return $this->renderReport($request, 'corrodkote'); }
    public function reportCass(Request $request) { return $this->renderReport($request, 'cass'); }
    public function reportSaltSpray(Request $request) { return $this->renderReport($request, 'salt_spray'); }
    public function reportPorecount(Request $request) { return $this->renderReport($request, 'porecount'); }

    private function renderReport(Request $request, $testType)
    {
        $query = DurabilityThicknessReport::with('standard')->orderBy('created_at', 'desc');

        // Hanya tampilkan baris yang benar-benar memiliki data aktual untuk jenis tes ini
        $query->where(function ($q) use ($testType) {
            if ($testType === 'thickness') {
                $q->where(function($sub) {
                    $sub->whereNotNull('actual_cu')->where('actual_cu', '!=', '')->where('actual_cu', '!=', '-')
                        ->orWhereNotNull('actual_ni')->where('actual_ni', '!=', '')->where('actual_ni', '!=', '-')
                        ->orWhereNotNull('actual_cr')->where('actual_cr', '!=', '')->where('actual_cr', '!=', '-');
                });
            } elseif ($testType === 'corrodkote') {
                $q->whereNotNull('actual_corrodkote')->where('actual_corrodkote', '!=', '')->where('actual_corrodkote', '!=', '-');
            } elseif ($testType === 'cass') {
                $q->whereNotNull('actual_cass')->where('actual_cass', '!=', '')->where('actual_cass', '!=', '-');
            } elseif ($testType === 'salt_spray') {
                $q->whereNotNull('actual_salt_spray')->where('actual_salt_spray', '!=', '')->where('actual_salt_spray', '!=', '-');
            } elseif ($testType === 'porecount') {
                $q->whereNotNull('actual_porecount')->where('actual_porecount', '!=', '')->where('actual_porecount', '!=', '-');
            }
        });
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('standard', function($q) use ($search) {
                $q->where('part_name', 'like', "%$search%")
                  ->orWhere('customer_name', 'like', "%$search%");
            });
        }
        if ($request->filled('customer_name')) {
            $customerName = $request->customer_name;
            $query->whereHas('standard', function($q) use ($customerName) {
                $q->where('customer_name', $customerName);
            });
        }
        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_cek', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_cek', '<=', $request->end_date);
        }
        if ($request->filled('result_judgment')) {
            $query->where('result_judgment', $request->result_judgment);
        }
        if ($request->filled('report_id')) {
            $query->where('id', $request->report_id);
        }
        
        if ($request->has('print')) {
            $reports = $query->get();
            $docHeader = \App\Models\GeneralSetting::getDocHeader('report_standard_performance_test', auth()->check() && auth()->user()->plant ? strtolower(auth()->user()->plant->name) : 'jakarta', [
                'no_dokumen' => '-',
                'tgl_terbit' => '-',
                'revisi' => '- / -',
                'halaman' => '1 / 1'
            ]);
            return view('durability_plating.print', compact('reports', 'docHeader', 'testType'));
        }

        $reports = $query->paginate(10)->withQueryString();
        
        $items = \App\Models\StandardPerformanceTest::whereIn('id', \App\Models\DurabilityThicknessReport::select('standard_performance_test_id'))
            ->orderBy('part_name', 'asc')
            ->get();

        $masterItems = \App\Models\StandardPerformanceTest::orderBy('part_name', 'asc')->get();

        $customers = \App\Models\StandardPerformanceTest::whereNotNull('customer_name')
            ->select('customer_name')
            ->distinct()
            ->orderBy('customer_name')
            ->pluck('customer_name');

        return view('durability_plating.report', compact('reports', 'items', 'masterItems', 'customers', 'testType'));
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
            'actual_corrodkote_waktu' => 'nullable|string|max:255',
            'actual_corrodkote' => 'nullable|string|max:255',
            'actual_cass_waktu' => 'nullable|string|max:255',
            'actual_cass' => 'nullable|string|max:255',
            'actual_salt_spray_waktu' => 'nullable|string|max:255',
            'actual_salt_spray' => 'nullable|string|max:255',
            'actual_porecount' => 'nullable|string|max:255',
            'result_judgment' => 'nullable|string|max:255',
            'tgl_masuk' => 'nullable|date',
            'jam_masuk' => 'nullable|date_format:H:i',
            'tgl_keluar' => 'nullable|date',
            'jam_keluar' => 'nullable|date_format:H:i',
            'tanggal_cek' => 'nullable|date',
            'description' => 'nullable|string'
        ]);

        $report = DurabilityThicknessReport::findOrFail($id);
        
        $updateData = [];
        $fields = [
            'production_date', 'shift', 'lot_no', 'actual_cu', 'actual_ni', 'actual_cr',
            'actual_corrodkote_waktu', 'actual_corrodkote', 'actual_cass_waktu', 'actual_cass',
            'actual_salt_spray_waktu', 'actual_salt_spray', 'actual_porecount',
            'result_judgment', 'tgl_masuk', 'jam_masuk', 'tgl_keluar', 'jam_keluar', 'tanggal_cek', 'description'
        ];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                $updateData[$field] = $request->$field;
            }
        }

        if (!empty($updateData)) {
            $report->update($updateData);
        }
        
        // Handle X-button deletions before processing new uploads
        if ($request->input('delete_evidence_before') === '1') {
            if ($report->evidence_before && file_exists(public_path($report->evidence_before))) {
                @unlink(public_path($report->evidence_before));
            }
            $report->update(['evidence_before' => null, 'evidence_before_uploaded_at' => null]);
        }

        if ($request->input('delete_evidence_after') === '1') {
            if ($report->evidence_after && file_exists(public_path($report->evidence_after))) {
                @unlink(public_path($report->evidence_after));
            }
            $report->update(['evidence_after' => null, 'evidence_after_uploaded_at' => null]);
        }

        if ($request->hasFile('evidence_before')) {
            if ($report->evidence_before && file_exists(public_path($report->evidence_before))) {
                @unlink(public_path($report->evidence_before));
            }
            $fileBefore = $request->file('evidence_before');
            $filenameBefore = time() . '_before_' . $fileBefore->getClientOriginalName();
            $fileBefore->move(public_path('uploads/durability_plating'), $filenameBefore);
            $report->update([
                'evidence_before' => 'uploads/durability_plating/' . $filenameBefore,
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
            $report->update([
                'evidence_after' => 'uploads/durability_plating/' . $filenameAfter,
                'evidence_after_uploaded_at' => now()
            ]);
        }
        
        ActivityLogger::log('updated', null, "Update Thickness Report untuk ID Laporan: {$id}");

        return redirect()->back()->with('success', 'Data berhasil diupdate.');
    }

    private function isFieldEmpty($value) {
        $val = trim($value);
        return is_null($value) || $val === '' || $val === '-';
    }

    private function clearTestData($report, $type) {
        if ($type === 'thickness') {
            $report->actual_cu = null;
            $report->actual_ni = null;
            $report->actual_cr = null;
        } elseif ($type === 'corrodkote') {
            $report->actual_corrodkote_waktu = null;
            $report->actual_corrodkote = null;
        } elseif ($type === 'cass') {
            $report->actual_cass_waktu = null;
            $report->actual_cass = null;
        } elseif ($type === 'salt_spray') {
            $report->actual_salt_spray_waktu = null;
            $report->actual_salt_spray = null;
        } elseif ($type === 'porecount') {
            $report->actual_porecount = null;
        }
        
        $allEmpty = $this->isFieldEmpty($report->actual_cu) && $this->isFieldEmpty($report->actual_ni) && $this->isFieldEmpty($report->actual_cr)
            && $this->isFieldEmpty($report->actual_corrodkote_waktu) && $this->isFieldEmpty($report->actual_corrodkote)
            && $this->isFieldEmpty($report->actual_cass_waktu) && $this->isFieldEmpty($report->actual_cass)
            && $this->isFieldEmpty($report->actual_salt_spray_waktu) && $this->isFieldEmpty($report->actual_salt_spray)
            && $this->isFieldEmpty($report->actual_porecount);

        if ($allEmpty) {
            $report->delete();
        } else {
            $report->save();
        }
    }

    public function destroyThickness(Request $request, $id)
    {
        $report = DurabilityThicknessReport::findOrFail($id);
        $type = $request->query('type', 'thickness');
        
        $this->clearTestData($report, $type);

        $typeName = strtoupper(str_replace('_', ' ', $type));
        ActivityLogger::log('deleted', null, "Hapus Data $typeName Report untuk ID Laporan: {$id}");

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
}