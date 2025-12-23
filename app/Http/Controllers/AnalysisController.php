<?php

namespace App\Http\Controllers;

use App\Models\Checksheet;
use App\Models\InProcessChecksheet;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AnalysisController extends Controller
{
    public function monthlyNgSubAssy(Request $request)
    {
        // Start Query
        $query = Checksheet::select('date', 'total_ng', 'defects', 'sampling_qty', 'cycle_time', 'item_id', 'operator_initials')
            ->with('item')
            ->orderBy('date');

        // Apply Date Filter if present
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        // Fetch Data
        // utilizing Collection methods for database-agnostic grouping (SQLite vs MySQL)
        $checksheets = $query->get();

        // Group by Year-Month (e.g., "2023-October")
        // We use 'Y-m' for sorting keys properly, then we can format labels later
        $grouped = $checksheets->groupBy(function($item) {
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
                $defectsList = json_decode($checksheet->defects, true);
                
                if (is_array($defectsList)) {
                    foreach ($defectsList as $defect) {
                        // $defect structure: ['type' => '...', 'qty' => ...]
                        if (isset($defect['type']) && isset($defect['qty'])) {
                            $type = $defect['type'];
                            $qty = (int)$defect['qty'];
                            
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
        // OR Use Fixed Standard if available
        $itemCycleTimes = [];
        $itemTotalPcs = []; // Store total sampling qty per item
        $itemTotalSeconds = []; // Store total cycle time per item
        $groupedByItem = $checksheets->groupBy('item_id');

        foreach ($groupedByItem as $itemId => $group) {
            $sumCycleTime = $group->sum('cycle_time');
            $sumSamplingQty = $group->sum('sampling_qty');

            $calcAvg = $sumSamplingQty > 0 ? $sumCycleTime / $sumSamplingQty : 0;

            // Check for Fixed Standard
            $item = $group->first()->item;
            if ($item && $item->standard_cycle_time > 0) {
                // Use fixed standard
                $std = $item->standard_cycle_time;
            } else {
                // Fallback to calculated average
                $std = $calcAvg;
            }

            $itemName = $item->name ?? 'Unknown Item';

            // Store the Standard (either fixed or calculated) for the Chart Bars
            $itemCycleTimes[$itemName] = round($std, 1);

            // Store Actuals for Tooltip context
            // "Standard" is what we compare against. "Actual Avg" is what happened.
            // But the chart structure expects "Total Pcs" and "Total Seconds" to be displayed.
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
        $users = Checksheet::select('operator_initials')
            ->distinct()
            ->whereNotNull('operator_initials')
            ->where('operator_initials', '!=', '')
            ->orderBy('operator_initials')
            ->pluck('operator_initials');
        
        $inspectorItemDatasets = [];
        $colors = [
            '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', 
            '#858796', '#5a5c69', '#6f42c1', '#fd7e14', '#20c9a6'
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
            'labels', 'data', 'dataPercentage', 'defectLabels', 'defectData',
            'dataCycleTime', 'itemLabels', 'itemCycleTimeData',
            'inspectorItemLabels', 'inspectorItemDatasets',
            'sortedItemTotalPcs', 'sortedItemTotalSeconds'
        ));
    }

    public function monthlyNgInProcess(Request $request)
    {
        // Start Query
        $query = InProcessChecksheet::select('date', 'total_ng', 'defects', 'sampling_qty', 'cycle_time', 'item_id')
            ->with('item')
            ->orderBy('date');

        // Apply Date Filter if present
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        // Fetch Data
        // utilizing Collection methods for database-agnostic grouping (SQLite vs MySQL)
        $checksheets = $query->get();

        // Group by Year-Month (e.g., "2023-October")
        // We use 'Y-m' for sorting keys properly, then we can format labels later
        $grouped = $checksheets->groupBy(function($item) {
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
                $defectsList = json_decode($checksheet->defects, true);
                
                if (is_array($defectsList)) {
                    foreach ($defectsList as $defect) {
                        // $defect structure: ['type' => '...', 'qty' => ...]
                        if (isset($defect['type']) && isset($defect['qty'])) {
                            $type = $defect['type'];
                            $qty = (int)$defect['qty'];
                            
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
        $itemCycleTimes = [];
        $groupedByItem = $checksheets->groupBy('item_id');

        foreach ($groupedByItem as $itemId => $group) {
            $avg = $group->avg('cycle_time');
            // Assuming eager loaded item is available. Handle null item (soft deleted or orphan).
            $itemName = $group->first()->item->name ?? 'Unknown Item';
            $itemCycleTimes[$itemName] = round($avg, 1);
        }

        // Sort by Cycle Time descending
        arsort($itemCycleTimes);

        $itemLabels = array_keys($itemCycleTimes);
        $itemCycleTimeData = array_values($itemCycleTimes);

        // Prepare data for the chart
        // Sort by quantity descending for better visualization
        arsort($defectCounts);
        
        $defectLabels = array_keys($defectCounts);
        $defectData = array_values($defectCounts);

        return view('analysis.monthly_ng_in_process', compact('labels', 'data', 'dataPercentage', 'defectLabels', 'defectData', 'dataCycleTime', 'itemLabels', 'itemCycleTimeData'));
    }
}
