<?php

namespace App\Services;

use App\Models\Checksheet;
use App\Models\InProcessChecksheet;
use App\Models\CrossCutChecksheet;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AnalysisService extends BaseService
{
    /**
     * Get monthly NG analysis data
     * 
     * @param string $type sub_assy, in_process, or cross_cut
     * @param array $filters
     * @return array
     */
    public function getMonthlyAnalysis(string $type, array $filters): array
    {
        $query = $this->getQueryForType($type, $filters);
        $checksheets = $query->get();

        $monthlyData = $this->calculateMonthlyData($checksheets, $type);
        $defectDistribution = $this->calculateDefectDistribution($checksheets, $type);
        $itemCycleTimeData = $this->calculateItemCycleTimeData($checksheets, $type);
        $inspectorData = $this->calculateInspectorData($checksheets, $type, $itemCycleTimeData['itemLabels']);

        return array_merge(
            $monthlyData,
            $defectDistribution,
            $itemCycleTimeData,
            $inspectorData
        );
    }

    /**
     * Build query based on type and filters
     */
    private function getQueryForType(string $type, array $filters)
    {
        $dateField = ($type === 'cross_cut') ? 'qc_datetime' : 'date';
        $model = $this->getModelForType($type);

        $query = $model::select($dateField, 'item_id', 'operator_initials', 'cycle_time', 'plant');

        if ($type === 'cross_cut') {
            $query->addSelect('position_remark_judgment');
        } else {
            $query->addSelect('total_ng', 'defects', 'sampling_qty');
        }

        $query->with('item')->orderBy($dateField);

        if (auth()->user()->role === 'admin' && !empty($filters['plant'])) {
            $query->withoutGlobalScope('plant')->where('plant', $filters['plant']);
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate($dateField, '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate($dateField, '<=', $filters['end_date']);
        } elseif ($type === 'in_process' && empty($filters['start_date'])) {
            // Default for In Process if no dates provided
            $query->whereDate($dateField, '>=', Carbon::now()->subMonths(12));
        }

        return $query;
    }

    private function getModelForType(string $type)
    {
        switch ($type) {
            case 'sub_assy':
                return Checksheet::class;
            case 'in_process':
                return InProcessChecksheet::class;
            case 'cross_cut':
                return CrossCutChecksheet::class;
            default:
                throw new \InvalidArgumentException("Invalid analysis type: $type");
        }
    }

    /**
     * Calculate monthly sums and averages for charts
     */
    private function calculateMonthlyData(Collection $checksheets, string $type): array
    {
        $dateField = ($type === 'cross_cut') ? 'qc_datetime' : 'date';
        $grouped = $checksheets->groupBy(fn($item) => Carbon::parse($item->$dateField)->format('Y-m'));

        $labels = [];
        $data = [];
        $dataPercentage = [];
        $dataCycleTime = [];

        foreach ($grouped as $key => $group) {
            $labels[] = Carbon::createFromFormat('Y-m', $key)->format('F Y');

            if ($type === 'cross_cut') {
                $ngCount = $group->where('position_remark_judgment', 'NG')->count();
                $totalCount = $group->count();
                $sumNG = $ngCount;
                $sumSampling = $totalCount;
            } else {
                $sumNG = $group->sum('total_ng');
                $sumSampling = $group->sum('sampling_qty');
            }

            $avgCycleTime = $group->avg('cycle_time');

            $data[] = $sumNG;
            $dataPercentage[] = round($sumSampling > 0 ? ($sumNG / $sumSampling) * 100 : 0, 2);
            $dataCycleTime[] = round($avgCycleTime, 1);
        }

        return compact('labels', 'data', 'dataPercentage', 'dataCycleTime');
    }

    /**
     * Calculate defect distribution
     */
    private function calculateDefectDistribution(Collection $checksheets, string $type): array
    {
        $defectCounts = [];

        if ($type === 'cross_cut') {
            $defectCounts = [
                'NG' => $checksheets->where('position_remark_judgment', 'NG')->count(),
                'OK' => $checksheets->where('position_remark_judgment', 'OK')->count()
            ];
        } else {
            foreach ($checksheets as $checksheet) {
                if (!empty($checksheet->defects)) {
                    $defectsList = is_string($checksheet->defects) ? json_decode($checksheet->defects, true) : $checksheet->defects;
                    if (is_array($defectsList)) {
                        foreach ($defectsList as $defect) {
                            if (isset($defect['type']) && isset($defect['qty'])) {
                                $defectCounts[$defect['type']] = ($defectCounts[$defect['type']] ?? 0) + (int) $defect['qty'];
                            }
                        }
                    }
                }
            }
        }

        arsort($defectCounts);
        return [
            'defectLabels' => array_keys($defectCounts),
            'defectData' => array_values($defectCounts)
        ];
    }

    /**
     * Calculate cycle time data per item
     */
    private function calculateItemCycleTimeData(Collection $checksheets, string $type): array
    {
        $itemCycleTimes = [];
        $itemTotalPcs = [];
        $itemTotalSeconds = [];
        $groupedByItem = $checksheets->groupBy('item_id');

        foreach ($groupedByItem as $itemId => $group) {
            $sumCycleTime = $group->sum('cycle_time');
            $count = ($type === 'cross_cut') ? $group->count() : $group->sum('sampling_qty');

            $avg = $count > 0 ? $sumCycleTime / $count : 0;
            $itemName = $group->first()->item->name ?? 'Unknown Item';

            $itemCycleTimes[$itemName] = round($avg, 1);
            $itemTotalPcs[$itemName] = $count;
            $itemTotalSeconds[$itemName] = $sumCycleTime;
        }

        arsort($itemCycleTimes);

        $itemLabels = array_keys($itemCycleTimes);
        $itemCycleTimeData = array_values($itemCycleTimes);
        $sortedItemTotalPcs = [];
        $sortedItemTotalSeconds = [];

        foreach ($itemLabels as $label) {
            $sortedItemTotalPcs[] = $itemTotalPcs[$label] ?? 0;
            $sortedItemTotalSeconds[] = $itemTotalSeconds[$label] ?? 0;
        }

        return compact('itemLabels', 'itemCycleTimeData', 'sortedItemTotalPcs', 'sortedItemTotalSeconds');
    }

    /**
     * Calculate inspector data for grouped charts
     */
    private function calculateInspectorData(Collection $checksheets, string $type, array $itemLabels): array
    {
        $model = $this->getModelForType($type);
        $users = $model::select('operator_initials')
            ->distinct()
            ->whereNotNull('operator_initials')
            ->where('operator_initials', '!=', '')
            ->orderBy('operator_initials')
            ->pluck('operator_initials');

        $inspectorItemLabels = $itemLabels;
        $inspectorItemDatasets = [];
        $colors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#5a5c69', '#6f42c1', '#fd7e14', '#20c9a6'];

        foreach ($users as $index => $user) {
            $userData = [];
            foreach ($inspectorItemLabels as $itemName) {
                $filtered = $checksheets->filter(function ($item) use ($user, $itemName) {
                    return $item->operator_initials == $user && ($item->item->name ?? 'Unknown Item') == $itemName;
                });

                if ($filtered->count() > 0) {
                    $uSumCycle = $filtered->sum('cycle_time');
                    $uCount = ($type === 'cross_cut') ? $filtered->count() : $filtered->sum('sampling_qty');
                    $userData[] = round($uCount > 0 ? $uSumCycle / $uCount : 0, 1);
                } else {
                    $userData[] = 0;
                }
            }

            $inspectorItemDatasets[] = [
                'label' => $user ?: 'Unknown',
                'data' => $userData,
                'backgroundColor' => $colors[$index % count($colors)],
                'borderColor' => $colors[$index % count($colors)],
                'borderWidth' => 1
            ];
        }

        return compact('inspectorItemLabels', 'inspectorItemDatasets');
    }
}
