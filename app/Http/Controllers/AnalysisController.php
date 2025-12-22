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
        $query = Checksheet::select('date', 'total_ng', 'defects', 'sampling_qty', 'cycle_time', 'item_id')
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

        // Calculate Average Cycle Time per User (Operator)
        $userCycleTimes = [];
        $groupedByUser = $checksheets->groupBy('operator_initials');

        foreach ($groupedByUser as $user => $group) {
            // Skip if operator_initials is null or empty
            if (empty($user)) {
                $user = 'Unknown';
            }
            $avg = $group->avg('cycle_time');
            $userCycleTimes[$user] = round($avg, 1);
        }

        // Sort by Cycle Time descending
        arsort($userCycleTimes);

        $userLabels = array_keys($userCycleTimes);
        $userCycleTimeData = array_values($userCycleTimes);

        // Prepare data for the chart
        // Sort by quantity descending for better visualization
        arsort($defectCounts);
        
        $defectLabels = array_keys($defectCounts);
        $defectData = array_values($defectCounts);

        return view('analysis.monthly_ng', compact('labels', 'data', 'dataPercentage', 'defectLabels', 'defectData', 'dataCycleTime', 'itemLabels', 'itemCycleTimeData', 'userLabels', 'userCycleTimeData'));
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
