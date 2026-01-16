<?php

namespace App\Http\Controllers;

use App\Models\Checksheet;
use App\Models\InProcessChecksheet;
use App\Models\CrossCutChecksheet;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AnalysisController extends Controller
{
    public function monthlyNgSubAssy(Request $request)
    {
        $plant = $request->get('plant');
        // Mulai Query: Mengambil data checksheet dengan relasi item, diurutkan berdasarkan tanggal
        $query = Checksheet::select('date', 'total_ng', 'defects', 'sampling_qty', 'cycle_time', 'item_id', 'operator_initials', 'plant')
            ->with('item')
            ->orderBy('date');

        // Admin can switch plants via query parameter, others are locked via HasPlantFilter
        if (auth()->user()->role === 'admin' && $request->has('plant')) {
            $query->withoutGlobalScope('plant')->where('plant', $request->get('plant'));
        }

        // For inspector, we explicitly override the request plant to their own plant for UI consistency
        if (auth()->user()->role === 'inspector') {
            $plant = auth()->user()->plant;
            $request->merge(['plant' => $plant]);
        }

        // Apply Date Filter if present
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        // Ambil Data dari database
        // Menggunakan method Collection untuk pengelompokan (grouping) yang agnostik database (bisa untuk SQLite maupun MySQL)
        $checksheets = $query->get();

        // Kelompokkan data berdasarkan Tahun-Bulan (contoh: "2023-10")
        // Kita menggunakan format 'Y-m' agar pengurutan (sorting) berjalan dengan benar
        $grouped = $checksheets->groupBy(function ($item) {
            return Carbon::parse($item->date)->format('Y-m');
        });

        $labels = [];
        $data = [];
        $dataPercentage = [];
        $dataCycleTime = [];

        foreach ($grouped as $key => $group) {
            // Format label agar mudah dibaca, contoh: "October 2023"
            $labels[] = Carbon::createFromFormat('Y-m', $key)->format('F Y');

            $sumNG = $group->sum('total_ng');
            $sumSampling = $group->sum('sampling_qty');
            $avgCycleTime = $group->avg('cycle_time');

            $data[] = $sumNG;

            // Hitung Persentase NG (NG / Sampling)
            $percentage = $sumSampling > 0 ? ($sumNG / $sumSampling) * 100 : 0;
            $dataPercentage[] = round($percentage, 2);

            // Hitung Rata-rata Cycle Time
            $dataCycleTime[] = round($avgCycleTime, 1);
        }

        // Menghitung Distribusi Varian Defect (NG)
        // Variabel $checksheets berisi semua data yang dimuat. Kita akan memparsing data JSON 'defects'.
        $defectCounts = [];

        foreach ($checksheets as $checksheet) {
            // Assuming 'defects' is stored as JSON string in database based on ChecksheetController@store
            // and it is NOT cast to array in the model.
            if (!empty($checksheet->defects)) {
                $defectsList = is_string($checksheet->defects) ? json_decode($checksheet->defects, true) : $checksheet->defects;

                if (is_array($defectsList)) {
                    foreach ($defectsList as $defect) {
                        // $defect structure: ['type' => '...', 'qty' => ...]
                        if (isset($defect['type']) && isset($defect['qty'])) {
                            $type = $defect['type'];
                            $qty = (int) $defect['qty'];

                            if (!isset($defectCounts[$type])) {
                                $defectCounts[$type] = 0;
                            }
                            $defectCounts[$type] += $qty;
                        }
                    }
                }
            }
        }

        // Menghitung Rata-rata Cycle Time per Item
        // Rumus: Total Cycle Time / Total Sampling Qty
        $itemCycleTimes = [];
        $itemTotalPcs = []; // Store total sampling qty per item
        $itemTotalSeconds = []; // Store total cycle time per item
        $groupedByItem = $checksheets->groupBy('item_id');

        foreach ($groupedByItem as $itemId => $group) {
            $sumCycleTime = $group->sum('cycle_time');
            $sumSamplingQty = $group->sum('sampling_qty');

            $avg = $sumSamplingQty > 0 ? $sumCycleTime / $sumSamplingQty : 0;

            // Mengasumsikan relation 'item' sudah di-load (eager load). Menangani jika item null (terhapus).
            $itemName = $group->first()->item->name ?? 'Unknown Item';
            $itemCycleTimes[$itemName] = round($avg, 1);
            $itemTotalPcs[$itemName] = $sumSamplingQty;
            $itemTotalSeconds[$itemName] = $sumCycleTime;
        }

        // Sort by Cycle Time descending
        arsort($itemCycleTimes);

        $itemLabels = array_keys($itemCycleTimes);
        $itemCycleTimeData = array_values($itemCycleTimes);
        // Align total pcs and seconds with sorted keys
        $sortedItemTotalPcs = [];
        $sortedItemTotalSeconds = [];
        foreach ($itemLabels as $label) {
            $sortedItemTotalPcs[] = $itemTotalPcs[$label] ?? 0;
            $sortedItemTotalSeconds[] = $itemTotalSeconds[$label] ?? 0;
        }

        // Menghitung Rata-rata Cycle Time per Item berdasarkan User (Grouped Bar Chart)
        // Kita Membutuhkan:
        // 1. Labels: Daftar Item (Diurutkan, misal berdasarkan Avg Cycle Time atau Nama)
        // 2. Datasets: Satu per User, berisi data untuk setiap Item

        // Use existing $itemLabels (sorted by global avg cycle time) as the X-axis/Y-axis base
        $inspectorItemLabels = $itemLabels;

        // Dapatkan semua user unik dari tabel untuk memastikan semua tercatat
        // meskipun mereka tidak memiliki data dalam rentang waktu yang dipilih.
        // Juga mengurutkan abjad untuk tampilan yang konsisten.
        $users = Checksheet::select('operator_initials')
            ->distinct()
            ->whereNotNull('operator_initials')
            ->where('operator_initials', '!=', '')
            ->orderBy('operator_initials')
            ->pluck('operator_initials');

        $inspectorItemDatasets = [];
        $colors = [
            '#4e73df',
            '#1cc88a',
            '#36b9cc',
            '#f6c23e',
            '#e74a3b',
            '#858796',
            '#5a5c69',
            '#6f42c1',
            '#fd7e14',
            '#20c9a6'
        ];

        foreach ($users as $index => $user) {
            $userInitials = $user ?: 'Unknown';
            $userData = [];

            foreach ($inspectorItemLabels as $itemName) {
                // Filter checksheets for this User AND this Item
                // Note: This might be slow if dataset is huge, but fine for monthly reports
                $filtered = $checksheets->filter(function ($item) use ($user, $itemName) {
                    $currentItemName = $item->item->name ?? 'Unknown Item';
                    return $item->operator_initials == $user && $currentItemName == $itemName;
                });

                if ($filtered->count() > 0) {
                    $uSumCycle = $filtered->sum('cycle_time');
                    $uSumSampling = $filtered->sum('sampling_qty');
                    $uAvg = $uSumSampling > 0 ? $uSumCycle / $uSumSampling : 0;
                    $userData[] = round($uAvg, 1);
                } else {
                    $userData[] = 0; // or null
                }
            }

            $inspectorItemDatasets[] = [
                'label' => $userInitials,
                'data' => $userData,
                'backgroundColor' => $colors[$index % count($colors)],
                'borderColor' => $colors[$index % count($colors)],
                'borderWidth' => 1
            ];
        }

        // Prepare data for the chart
        // Sort by quantity descending for better visualization
        arsort($defectCounts);

        $defectLabels = array_keys($defectCounts);
        $defectData = array_values($defectCounts);

        return view('analysis.monthly_ng', compact(
            'labels',
            'data',
            'dataPercentage',
            'defectLabels',
            'defectData',
            'dataCycleTime',
            'itemLabels',
            'itemCycleTimeData',
            'inspectorItemLabels',
            'inspectorItemDatasets',
            'sortedItemTotalPcs',
            'sortedItemTotalSeconds',
            'plant'
        ));
    }

    public function monthlyNgInProcess(Request $request)
    {
        $plant = $request->get('plant');
        // Mulai Query: Mengambil data InProcess Checksheet
        $query = InProcessChecksheet::select('date', 'total_ng', 'defects', 'sampling_qty', 'cycle_time', 'item_id', 'operator_initials', 'plant')
            ->with('item')
            ->orderBy('date');

        // Admin can switch plants via query parameter, others are locked via HasPlantFilter
        if (auth()->user()->role === 'admin' && $request->has('plant')) {
            $query->withoutGlobalScope('plant')->where('plant', $request->get('plant'));
        }

        // For inspector, we explicitly override the request plant to their own plant for UI consistency
        if (auth()->user()->role === 'inspector') {
            $plant = auth()->user()->plant;
            $request->merge(['plant' => $plant]);
        }

        // Apply Date Filter if present, otherwise default to last 12 months
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('date', '>=', $request->start_date);
        } else {
            // Default: last 12 months
            $query->whereDate('date', '>=', Carbon::now()->subMonths(12));
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        // Fetch Data
        // utilizing Collection methods for database-agnostic grouping (SQLite vs MySQL)
        $checksheets = $query->get();

        // Group by Year-Month (e.g., "2023-October")
        // We use 'Y-m' for sorting keys properly, then we can format labels later
        $grouped = $checksheets->groupBy(function ($item) {
            return Carbon::parse($item->date)->format('Y-m');
        });

        $labels = [];
        $data = [];
        $dataPercentage = [];
        $dataCycleTime = [];

        foreach ($grouped as $key => $group) {
            // Format label to be readable, e.g., "October 2023"
            $labels[] = Carbon::createFromFormat('Y-m', $key)->format('F Y');

            $sumNG = $group->sum('total_ng');
            $sumSampling = $group->sum('sampling_qty');
            $avgCycleTime = $group->avg('cycle_time');

            $data[] = $sumNG;

            // Calculate Percentage (NG / Sampling)
            $percentage = $sumSampling > 0 ? ($sumNG / $sumSampling) * 100 : 0;
            $dataPercentage[] = round($percentage, 2);

            // Calculate Average Cycle Time
            $dataCycleTime[] = round($avgCycleTime, 1);
        }

        // Calculate Defect Variants Distribution
        // $checksheets contains all records loaded. We will parse 'defects' JSON.
        $defectCounts = [];

        foreach ($checksheets as $checksheet) {
            // Assuming 'defects' is stored as JSON string in database based on ChecksheetController@store
            // and it is NOT cast to array in the model.
            if (!empty($checksheet->defects)) {
                $defectsList = is_string($checksheet->defects) ? json_decode($checksheet->defects, true) : $checksheet->defects;

                if (is_array($defectsList)) {
                    foreach ($defectsList as $defect) {
                        // $defect structure: ['type' => '...', 'qty' => ...]
                        if (isset($defect['type']) && isset($defect['qty'])) {
                            $type = $defect['type'];
                            $qty = (int) $defect['qty'];

                            if (!isset($defectCounts[$type])) {
                                $defectCounts[$type] = 0;
                            }
                            $defectCounts[$type] += $qty;
                        }
                    }
                }
            }
        }

        // Calculate Average Cycle Time per Item
        // Formula: Total Cycle Time / Total Sampling Qty
        $itemCycleTimes = [];
        $itemTotalPcs = []; // Store total sampling qty per item
        $itemTotalSeconds = []; // Store total cycle time per item
        $groupedByItem = $checksheets->groupBy('item_id');

        foreach ($groupedByItem as $itemId => $group) {
            $sumCycleTime = $group->sum('cycle_time');
            $sumSamplingQty = $group->sum('sampling_qty');

            $avg = $sumSamplingQty > 0 ? $sumCycleTime / $sumSamplingQty : 0;

            // Assuming eager loaded item is available. Handle null item (soft deleted or orphan).
            $itemName = $group->first()->item->name ?? 'Unknown Item';
            $itemCycleTimes[$itemName] = round($avg, 1);
            $itemTotalPcs[$itemName] = $sumSamplingQty;
            $itemTotalSeconds[$itemName] = $sumCycleTime;
        }

        // Sort by Cycle Time descending
        arsort($itemCycleTimes);

        $itemLabels = array_keys($itemCycleTimes);
        $itemCycleTimeData = array_values($itemCycleTimes);
        // Align total pcs and seconds with sorted keys
        $sortedItemTotalPcs = [];
        $sortedItemTotalSeconds = [];
        foreach ($itemLabels as $label) {
            $sortedItemTotalPcs[] = $itemTotalPcs[$label] ?? 0;
            $sortedItemTotalSeconds[] = $itemTotalSeconds[$label] ?? 0;
        }

        // Calculate Average Cycle Time per Item by User (Grouped Bar Chart)
        // We need:
        // 1. Labels: List of Items (Sorted, e.g., by Avg Cycle Time or Name)
        // 2. Datasets: One per User, containing data for each Item

        // Use existing $itemLabels (sorted by global avg cycle time) as the X-axis/Y-axis base
        $inspectorItemLabels = $itemLabels;

        // Get all unique users from the entire table to ensure we list everyone
        // even if they have no data in the selected range.
        // Also sorting them alphabetically for consistent display.
        $users = InProcessChecksheet::select('operator_initials')
            ->distinct()
            ->whereNotNull('operator_initials')
            ->where('operator_initials', '!=', '')
            ->orderBy('operator_initials')
            ->pluck('operator_initials');

        $inspectorItemDatasets = [];
        $colors = [
            '#4e73df',
            '#1cc88a',
            '#36b9cc',
            '#f6c23e',
            '#e74a3b',
            '#858796',
            '#5a5c69',
            '#6f42c1',
            '#fd7e14',
            '#20c9a6'
        ];

        foreach ($users as $index => $user) {
            $userInitials = $user ?: 'Unknown';
            $userData = [];

            foreach ($inspectorItemLabels as $itemName) {
                // Filter checksheets for this User AND this Item
                // Note: This might be slow if dataset is huge, but fine for monthly reports
                $filtered = $checksheets->filter(function ($item) use ($user, $itemName) {
                    $currentItemName = $item->item->name ?? 'Unknown Item';
                    return $item->operator_initials == $user && $currentItemName == $itemName;
                });

                if ($filtered->count() > 0) {
                    $uSumCycle = $filtered->sum('cycle_time');
                    $uSumSampling = $filtered->sum('sampling_qty');
                    $uAvg = $uSumSampling > 0 ? $uSumCycle / $uSumSampling : 0;
                    $userData[] = round($uAvg, 1);
                } else {
                    $userData[] = 0; // or null
                }
            }

            $inspectorItemDatasets[] = [
                'label' => $userInitials,
                'data' => $userData,
                'backgroundColor' => $colors[$index % count($colors)],
                'borderColor' => $colors[$index % count($colors)],
                'borderWidth' => 1
            ];
        }

        // Prepare data for the chart
        // Sort by quantity descending for better visualization
        arsort($defectCounts);

        $defectLabels = array_keys($defectCounts);
        $defectData = array_values($defectCounts);

        return view('analysis.monthly_ng_in_process', compact(
            'labels',
            'data',
            'dataPercentage',
            'defectLabels',
            'defectData',
            'dataCycleTime',
            'itemLabels',
            'itemCycleTimeData',
            'inspectorItemLabels',
            'inspectorItemDatasets',
            'sortedItemTotalPcs',
            'sortedItemTotalSeconds',
            'plant'
        ));
    }

    public function monthlyNgCrossCut(Request $request)
    {
        $plant = $request->get('plant');
        // Mulai Query - Cross Cut memiliki struktur berbeda dari checksheet lain
        // Menggunakan 'qc_datetime' sebagai tanggal, dan 'position_remark_judgment' (OK/NG) sebagai penentu NG
        $query = CrossCutChecksheet::select('qc_datetime', 'position_remark_judgment', 'item_id', 'operator_initials', 'cycle_time', 'plant')
            ->with('item')
            ->orderBy('qc_datetime');

        // Admin can switch plants via query parameter, others are locked via HasPlantFilter
        if (auth()->user()->role === 'admin' && $request->has('plant')) {
            $query->withoutGlobalScope('plant')->where('plant', $request->get('plant'));
        }

        // For inspector, we explicitly override the request plant to their own plant for UI consistency
        if (auth()->user()->role === 'inspector') {
            $plant = auth()->user()->plant;
            $request->merge(['plant' => $plant]);
        }

        // Apply Date Filter if present
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('qc_datetime', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('qc_datetime', '<=', $request->end_date);
        }

        // Fetch Data
        $checksheets = $query->get();

        // Group by Year-Month
        $grouped = $checksheets->groupBy(function ($item) {
            return Carbon::parse($item->qc_datetime)->format('Y-m');
        });

        $labels = [];
        $data = []; // Total NG count
        $dataPercentage = []; // NG percentage
        $dataCycleTime = [];

        foreach ($grouped as $key => $group) {
            $labels[] = Carbon::createFromFormat('Y-m', $key)->format('F Y');

            // Hitung item NG (dimana position_remark_judgment = 'NG')
            $ngCount = $group->where('position_remark_judgment', 'NG')->count();
            $totalCount = $group->count();
            $avgCycleTime = $group->avg('cycle_time');

            $data[] = $ngCount;

            // Calculate Percentage (NG / Total)
            $percentage = $totalCount > 0 ? ($ngCount / $totalCount) * 100 : 0;
            $dataPercentage[] = round($percentage, 2);

            // Calculate Average Cycle Time
            $dataCycleTime[] = round($avgCycleTime, 1);
        }

        // Untuk Cross Cut, kita tidak memiliki tipe defect spesifik seperti checksheet lain
        // Kita hanya menampilkan distribusi OK vs NG
        $defectCounts = [
            'NG' => $checksheets->where('position_remark_judgment', 'NG')->count(),
            'OK' => $checksheets->where('position_remark_judgment', 'OK')->count()
        ];

        // Calculate Average Cycle Time per Item
        $itemCycleTimes = [];
        $itemTotalCount = [];
        $itemTotalSeconds = [];
        $groupedByItem = $checksheets->groupBy('item_id');

        foreach ($groupedByItem as $itemId => $group) {
            $sumCycleTime = $group->sum('cycle_time');
            $count = $group->count();

            $avg = $count > 0 ? $sumCycleTime / $count : 0;

            $itemName = $group->first()->item->name ?? 'Unknown Item';
            $itemCycleTimes[$itemName] = round($avg, 1);
            $itemTotalCount[$itemName] = $count;
            $itemTotalSeconds[$itemName] = $sumCycleTime;
        }

        // Sort by Cycle Time descending
        arsort($itemCycleTimes);

        $itemLabels = array_keys($itemCycleTimes);
        $itemCycleTimeData = array_values($itemCycleTimes);
        $sortedItemTotalPcs = [];
        $sortedItemTotalSeconds = [];
        foreach ($itemLabels as $label) {
            $sortedItemTotalPcs[] = $itemTotalCount[$label] ?? 0;
            $sortedItemTotalSeconds[] = $itemTotalSeconds[$label] ?? 0;
        }

        // Calculate Average Cycle Time per Item by User
        $inspectorItemLabels = $itemLabels;

        $users = CrossCutChecksheet::select('operator_initials')
            ->distinct()
            ->whereNotNull('operator_initials')
            ->where('operator_initials', '!=', '')
            ->orderBy('operator_initials')
            ->pluck('operator_initials');

        $inspectorItemDatasets = [];
        $colors = [
            '#4e73df',
            '#1cc88a',
            '#36b9cc',
            '#f6c23e',
            '#e74a3b',
            '#858796',
            '#5a5c69',
            '#6f42c1',
            '#fd7e14',
            '#20c9a6'
        ];

        foreach ($users as $index => $user) {
            $userInitials = $user ?: 'Unknown';
            $userData = [];

            foreach ($inspectorItemLabels as $itemName) {
                $filtered = $checksheets->filter(function ($item) use ($user, $itemName) {
                    $currentItemName = $item->item->name ?? 'Unknown Item';
                    return $item->operator_initials == $user && $currentItemName == $itemName;
                });

                if ($filtered->count() > 0) {
                    $uSumCycle = $filtered->sum('cycle_time');
                    $uCount = $filtered->count();
                    $uAvg = $uCount > 0 ? $uSumCycle / $uCount : 0;
                    $userData[] = round($uAvg, 1);
                } else {
                    $userData[] = 0;
                }
            }

            $inspectorItemDatasets[] = [
                'label' => $userInitials,
                'data' => $userData,
                'backgroundColor' => $colors[$index % count($colors)],
                'borderColor' => $colors[$index % count($colors)],
                'borderWidth' => 1
            ];
        }

        arsort($defectCounts);

        $defectLabels = array_keys($defectCounts);
        $defectData = array_values($defectCounts);

        // Debug logging
        \Log::info('Cross Cut Report Data:', [
            'total_checksheets' => $checksheets->count(),
            'item_labels_count' => count($itemLabels),
            'inspector_labels_count' => count($inspectorItemLabels),
            'inspector_datasets_count' => count($inspectorItemDatasets),
            'users_count' => $users->count(),
            'item_cycle_time_data' => $itemCycleTimeData,
        ]);

        return view('analysis.monthly_ng_cross_cut', compact(
            'labels',
            'data',
            'dataPercentage',
            'defectLabels',
            'defectData',
            'dataCycleTime',
            'itemLabels',
            'itemCycleTimeData',
            'inspectorItemLabels',
            'inspectorItemDatasets',
            'sortedItemTotalPcs',
            'sortedItemTotalSeconds',
            'plant'
        ));
    }
}
