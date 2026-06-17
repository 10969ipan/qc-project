<?php

namespace App\Http\Controllers;

use App\Models\ThicknessStandard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Helpers\ActivityLogger;

class ThicknessStandardController extends Controller
{
    public function index(Request $request)
    {
        $plantCode = $request->get('plant', 'jakarta');
        $plantId = \App\Models\Plant::where('code', strtolower($plantCode))->value('id');

        $query = ThicknessStandard::where('plant_id', $plantId);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('part_name', 'like', "%{$search}%")
                  ->orWhere('customer', 'like', "%{$search}%")
                  ->orWhere('standard_code', 'like', "%{$search}%");
            });
        }

        $standards = $query->orderBy('part_name')->get();

        return view('thickness_standards.index', compact('standards', 'plantCode'));
    }

    public function store(Request $request)
    {
        $plantId = \App\Models\Plant::where('code', strtolower($request->get('plant', 'jakarta')))->value('id');

        $validated = $request->validate([
            'part_name' => 'required|string|max:255',
            'customer' => 'nullable|string|max:255',
            'standard_code' => 'nullable|string|max:255',
            'standard_name' => 'nullable|string|max:255',
            'thickness_cu_std' => 'nullable|numeric',
            'thickness_ni_std' => 'nullable|numeric',
            'thickness_cr_std' => 'nullable|numeric',
            'corrodkote' => 'nullable|string|max:255',
            'cass_test' => 'nullable|string|max:255',
            'salt_spray_test' => 'nullable|string|max:255',
            'porecount_test' => 'nullable|string|max:255',
            'cross_cut_test' => 'nullable|string|max:255',
        ]);

        $validated['plant_id'] = $plantId;

        $standard = ThicknessStandard::create($validated);
        
        ActivityLogger::log('created', $standard, "Menambahkan Master Data Thickness Standard: {$standard->part_name}");

        return redirect()->back()->with('success', 'Master data berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $standard = ThicknessStandard::findOrFail($id);

        $validated = $request->validate([
            'part_name' => 'required|string|max:255',
            'customer' => 'nullable|string|max:255',
            'standard_code' => 'nullable|string|max:255',
            'standard_name' => 'nullable|string|max:255',
            'thickness_cu_std' => 'nullable|numeric',
            'thickness_ni_std' => 'nullable|numeric',
            'thickness_cr_std' => 'nullable|numeric',
            'corrodkote' => 'nullable|string|max:255',
            'cass_test' => 'nullable|string|max:255',
            'salt_spray_test' => 'nullable|string|max:255',
            'porecount_test' => 'nullable|string|max:255',
            'cross_cut_test' => 'nullable|string|max:255',
        ]);

        $standard->update($validated);
        
        ActivityLogger::log('updated', $standard, "Mengubah Master Data Thickness Standard: {$standard->part_name}");

        return redirect()->back()->with('success', 'Master data berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $standard = ThicknessStandard::findOrFail($id);
        $name = $standard->part_name;
        $standard->delete();
        
        ActivityLogger::log('deleted', null, "Menghapus Master Data Thickness Standard: {$name}");

        return redirect()->back()->with('success', 'Master data berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        $plantCode = $request->get('plant', 'jakarta');
        $plantId = \App\Models\Plant::where('code', strtolower($plantCode))->value('id');

        try {
            $spreadsheet = IOFactory::load($request->file('file'));
            $sheet = $spreadsheet->getSheetByName('List Part (Std)');
            
            if (!$sheet) {
                // Try first sheet if "List Part (Std)" not found
                $sheet = $spreadsheet->getActiveSheet();
            }

            $rows = $sheet->toArray();
            
            $inserted = 0;
            $updated = 0;

            // Start reading from row 6 (index 5) based on the excel format we saw earlier
            // Column mapping:
            // 1 => part_name
            // 2 => customer
            // 3 => standard_code
            // 4 => standard_name
            // 5 => cu
            // 6 => ni
            // 7 => cr
            // 8 => corrodkote
            // 9 => cass test
            // 10 => salt spray test
            // 11 => porecount test
            // 12 => cross cut test

            foreach ($rows as $index => $row) {
                if ($index < 5) continue; // Skip headers
                
                $partName = trim($row[1] ?? '');
                if (empty($partName)) continue; // Skip empty rows
                
                $existing = ThicknessStandard::where('plant_id', $plantId)
                                             ->where('part_name', $partName)
                                             ->first();

                $data = [
                    'plant_id' => $plantId,
                    'part_name' => $partName,
                    'customer' => trim($row[2] ?? ''),
                    'standard_code' => trim($row[3] ?? ''),
                    'standard_name' => trim($row[4] ?? ''),
                    'thickness_cu_std' => is_numeric($row[5]) ? $row[5] : null,
                    'thickness_ni_std' => is_numeric($row[6]) ? $row[6] : null,
                    'thickness_cr_std' => is_numeric($row[7]) ? $row[7] : null,
                    'corrodkote' => trim($row[8] ?? ''),
                    'cass_test' => trim($row[9] ?? ''),
                    'salt_spray_test' => trim($row[10] ?? ''),
                    'porecount_test' => trim($row[11] ?? ''),
                    'cross_cut_test' => trim($row[12] ?? ''),
                ];

                if ($existing) {
                    $existing->update($data);
                    $updated++;
                } else {
                    ThicknessStandard::create($data);
                    $inserted++;
                }
            }

            ActivityLogger::log('updated', null, "Melakukan import master data thickness ({$inserted} baru, {$updated} diperbarui)");
            return redirect()->back()->with('success', "Import berhasil: {$inserted} data ditambahkan, {$updated} data diperbarui.");
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses file Excel: ' . $e->getMessage());
        }
    }
}
