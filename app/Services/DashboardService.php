<?php

namespace App\Services;

use App\Models\InProcessChecksheet;
use App\Models\SubAssyChecksheet;
use App\Models\CrossCutChecksheet;
use App\Models\CrossCutPaintingChecksheet;
use App\Models\SortirChecksheet;
use App\Models\MachineStatus;
use App\Models\MonthlyReport;
use App\Models\CustomerClaim;
use App\Models\CustomerClaimRecord;
use App\Models\Plant;
use App\Helpers\ShiftHelper;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService extends BaseService
{
    /**
     * Get all dashboard data
     * 
     * @return array
     */
    public function getDashboardData(): array
    {
        $authRole = auth()->user()->role;
        $plantId = auth()->user()->plant_id;
        $cacheKey = "dashboard_data_{$authRole}_{$plantId}_" . request('plant');

        // TEMPORARILY BYPASSING CACHE FOR DEBUGGING
        // return Cache::remember($cacheKey, now()->addMinutes(1), function () use ($authRole) {
        return (function () use ($authRole) {
            $combinedStats = $this->calculateApprovalStats();
            $dailyCombinedStats = $this->calculateApprovalStats(null, true);

            $statsJakarta = null;
            $statsKarawang = null;
            $dailyStatsJakarta = null;
            $dailyStatsKarawang = null;

            $dualViewRoles = ['admin', 'manager', 'asst_manager', 'manager_qc', 'asst_manager_qc', 'oshef'];

            $productionJakarta = [];
            $productionKarawang = [];

            if (in_array($authRole, $dualViewRoles)) {
                $statsJakarta = $this->calculateApprovalStats('jakarta');
                $statsKarawang = $this->calculateApprovalStats('karawang');

                $dailyStatsJakarta = $this->calculateApprovalStats('jakarta', true);
                $dailyStatsKarawang = $this->calculateApprovalStats('karawang', true);

                $productionJakarta = $this->getProductionMonitoring('jakarta');
                $productionKarawang = $this->getProductionMonitoring('karawang');
            }

            $productionMonitoring = $this->getProductionMonitoring(); // Default

            $activeReport = MonthlyReport::where('is_active', true)->first();

            // NG Rate Data for Charts
            $ngRateData = $this->getNgRateData();

            $currentPlant = auth()->user()->plant?->name ?? 'unknown';

            // Operator Map (Initials -> Name)
            $users = \App\Models\User::all();
            $operatorMap = [];
            foreach ($users as $u) {
                // Using accessor to ensure we get initials even if not in DB column explicit
                $init = $u->initials;
                if ($init) {
                    $operatorMap[$init] = $u->name;
                }
            }

            // Flag for dual view mode
            $isDualView = in_array($authRole, $dualViewRoles);

            // Claim Frequency Data (from list claim records)
            $claimFrequency = $this->getClaimFrequencyData();

            return array_merge(
                compact('combinedStats', 'statsJakarta', 'statsKarawang', 'dailyCombinedStats', 'dailyStatsJakarta', 'dailyStatsKarawang', 'activeReport', 'productionJakarta', 'productionKarawang', 'ngRateData', 'currentPlant', 'operatorMap', 'isDualView', 'claimFrequency'),
                $productionMonitoring
            );
        })();
    }

    /**
     * Calculate global approval statistics
     */
    private function calculateApprovalStats(?string $plantOverride = null, bool $dailyOnly = false): array
    {
        $stats = ['pending' => 0, 'approved' => 0, 'rejected' => 0];
        // Use override if provided, otherwise check request or auth user
        $plantIdentifier = $plantOverride ?? request('plant') ?? auth()->user()->plant_id;
        $plantId = $this->resolvePlantId($plantIdentifier);

        // Determine plant code for conditional filtering
        $plantCode = null;
        if ($plantId) {
            $plantCode = Plant::where('id', $plantId)->value('code');
        }

        $this->processModelStats(SubAssyChecksheet::class, $stats, $plantId, $dailyOnly);
        $this->processModelStats(InProcessChecksheet::class, $stats, $plantId, $dailyOnly);

        // Jakarta only shows Sub Assy and In Process per user request
        if ($plantCode !== 'jakarta') {
            $this->processModelStats(CrossCutChecksheet::class, $stats, $plantId, $dailyOnly);
            $this->processModelStats(CrossCutPaintingChecksheet::class, $stats, $plantId, $dailyOnly);
        }

        return $stats;
    }

    private function processModelStats(string $modelClass, array &$stats, ?string $plantId = null, bool $dailyOnly = false): void
    {
        $table = (new $modelClass)->getTable();
        $hasKaru = Schema::hasColumn($table, 'karu_qc');

        $columns = ['kashift_qc', 'supervisor_qc'];
        if ($hasKaru)
            $columns[] = 'karu_qc';

        $dateColumn = in_array($modelClass, [CrossCutChecksheet::class, CrossCutPaintingChecksheet::class]) ? 'production_datetime' : 'date';

        foreach ($columns as $column) {
            $query = DB::table($table);
            if ($plantId) {
                $query->where('plant_id', $plantId);
            }

            if ($dailyOnly) {
                $query->whereDate($dateColumn, now()->toDateString());
            }

            $results = $query->selectRaw("
                SUM(CASE WHEN $column = 'REJECTED' THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN $column IS NOT NULL AND $column != '' AND $column != 'REJECTED' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN $column IS NULL OR $column = '' THEN 1 ELSE 0 END) as pending
            ")->first();

            $stats['rejected'] += (int) $results->rejected;
            $stats['approved'] += (int) $results->approved;
            $stats['pending'] += (int) $results->pending;
        }
    }

    private function updateStat(array &$stats, $value): void
    {
        if ($value === 'REJECTED') {
            $stats['rejected']++;
        } elseif (!empty($value)) {
            $stats['approved']++;
        } else {
            $stats['pending']++;
        }
    }

    /**
     * Get production monitoring data
     */
    private function getProductionMonitoring(?string $plantIdentifier = null): array
    {
        $now = now();
        $hour = $now->hour;

        $plantId = $this->resolvePlantId($plantIdentifier ?? request('plant') ?? auth()->user()->plant_id);

        $plantId = $this->resolvePlantId($plantIdentifier ?? request('plant') ?? auth()->user()->plant_id);

        // Determine current production date and shift using ShiftHelper
        $currentProductionDate = ShiftHelper::getProductionDate($now);
        $currentShift = ShiftHelper::getShift($now);
        $shiftStartTime = ShiftHelper::getShiftStartTime($currentProductionDate, $currentShift, $now->dayOfWeek);

        // Active Sub Assy Lines - Filter by current shift and date
        $linesQuery = SubAssyChecksheet::with('item')
            ->whereDate('date', $currentProductionDate)
            ->where('shift', $currentShift)
            ->whereNotNull('line')
            ->orderBy('created_at', 'desc');

        if ($plantId)
            $linesQuery->where('plant_id', $plantId);

        $activeLines = $linesQuery->get()
            ->unique('line')
            ->mapWithKeys(fn($item) => [(int) $item->line => $item]);

        // Active In Process Machines - Filter by current shift and date
        $machinesQuery = InProcessChecksheet::with('item')
            ->whereDate('date', $currentProductionDate)
            ->where('shift', $currentShift)
            ->whereNotNull('code_machine')
            ->orderBy('created_at', 'desc');

        if ($plantId)
            $machinesQuery->where('plant_id', $plantId);

        $activeMachines = $machinesQuery->get()
            ->unique('code_machine')
            ->mapWithKeys(fn($item) => [(int) $item->code_machine => $item]);

        // Status Overrides - Filter by shift start time to reset automatically when shift changes
        $statusQuery = MachineStatus::whereIn('status', ['maintenance', 'stopped', 'trouble']);

        if ($shiftStartTime) {
            $statusQuery->where('updated_at', '>=', $shiftStartTime);
        }

        if ($plantId)
            $statusQuery->where('plant_id', $plantId);

        $manualStatuses = $statusQuery->get();
        $lineStatuses = $manualStatuses->where('type', 'line')->keyBy('number');
        $machineStatuses = $manualStatuses->where('type', 'machine')->keyBy('number');

        // Running Counts
        $runningLinesCount = $activeLines->count() - $activeLines->keys()->intersect($lineStatuses->keys())->count();
        $runningMachinesCount = $activeMachines->count() - $activeMachines->keys()->intersect($machineStatuses->whereIn('status', ['stopped', 'trouble'])->keys())->count();

        return compact('activeLines', 'activeMachines', 'lineStatuses', 'machineStatuses', 'runningLinesCount', 'runningMachinesCount');
    }

    /**
     * Get Customer Claim data for CanvasJS chart
     * 
     * @param int|null $year
     * @return array
     */
    public function getCustomerClaimData($year = null)
    {
        if (!$year || $year === 'combined') {
            return $this->getCombinedClaimData();
        }

        if ($year === 'all') {
            return $this->getYearlyClaimTrend();
        }

        $year = $year ?? date('Y');
        $claims = CustomerClaim::withoutGlobalScope('plant')
            ->where('year', $year)
            ->orderBy('month', 'asc')
            ->get();

        $jakartaPlantId = Plant::resolveId('jakarta');
        $karawangPlantId = Plant::resolveId('karawang');
        $totalPlantId = Plant::resolveId('total');

        $labels = [];
        $jakartaPpm = [];
        $karawangPpm = [];
        $jakartaClaims = [];
        $karawangClaims = [];
        $combinedTotal = [];
        $targets = [];

        for ($m = 1; $m <= 12; $m++) {
            $labels[] = \Carbon\Carbon::createFromDate($year, $m, 1)->format('M');
            $jkt = $claims->where('month', $m)->where('plant_id', $jakartaPlantId)->first();
            $krw = $claims->where('month', $m)->where('plant_id', $karawangPlantId)->first();
            $totalData = $claims->where('month', $m)->where('plant_id', $totalPlantId)->first();

            // Get target from any record in that month, or default to 0
            $target = ($jkt ? $jkt->target_value : ($krw ? $krw->target_value : 0));

            $jakartaPpm[] = (float) ($jkt->ppm_value ?? 0);
            $karawangPpm[] = (float) ($krw->ppm_value ?? 0);
            $jakartaClaims[] = (int) ($jkt->total_claims ?? 0);
            $karawangClaims[] = (int) ($krw->total_claims ?? 0);
            $combinedTotal[] = (int) ($totalData->total_claims ?? 0);
            $targets[] = (float) $target;
        }

        return [
            'year' => $year,
            'labels' => $labels,
            'jakarta' => $jakartaPpm,
            'karawang' => $karawangPpm,
            'jakarta_claims' => $jakartaClaims,
            'karawang_claims' => $karawangClaims,
            'combined_total' => $combinedTotal,
            'target' => $targets,
            'is_yearly' => false
        ];
    }

    /**
     * Get yearly average trend for customer claims
     */
    private function getYearlyClaimTrend()
    {
        // Get last 5 years
        $currentYear = (int) date('Y');
        $years = range($currentYear - 4, $currentYear);

        $jakartaPlantId = Plant::where('code', 'jakarta')->value('id');
        $karawangPlantId = Plant::where('code', 'karawang')->value('id');
        $totalPlantId = Plant::where('code', 'total')->value('id');

        $labels = [];
        $jakartaPpm = [];
        $karawangPpm = [];
        $jakartaClaims = [];
        $karawangClaims = [];
        $combinedTotal = [];
        $targets = [];

        foreach ($years as $year) {
            $labels[] = (string) $year;

            // Jakarta average for the year
            $jktData = CustomerClaim::withoutGlobalScope('plant')
                ->where('plant_id', $jakartaPlantId)
                ->where('year', $year)
                ->selectRaw('AVG(ppm_value) as avg_ppm, AVG(target_value) as avg_target, SUM(total_claims) as sum_claims')
                ->first();

            $jakartaPpm[] = $jktData->avg_ppm ? round($jktData->avg_ppm, 2) : 0;
            $jakartaClaims[] = (int) ($jktData->sum_claims ?? 0);

            // Karawang average for the year
            $krwData = CustomerClaim::withoutGlobalScope('plant')
                ->where('plant_id', $karawangPlantId)
                ->where('year', $year)
                ->selectRaw('AVG(ppm_value) as avg_ppm, AVG(target_value) as avg_target, SUM(total_claims) as sum_claims')
                ->first();

            $karawangPpm[] = $krwData->avg_ppm ? round($krwData->avg_ppm, 2) : 0;
            $karawangClaims[] = (int) ($krwData->sum_claims ?? 0);

            // Total claims from plant 'total' (Month 0 for yearly summary)
            $totalYearlyData = CustomerClaim::withoutGlobalScope('plant')
                ->where('plant_id', $totalPlantId)
                ->where('year', $year)
                ->where('month', 0)
                ->first();

            $combinedTotal[] = (float) ($totalYearlyData->total_claims ?? 0);

            // Use average target between plants or from one if other is missing
            $target = 0;
            if ($jktData->avg_target && $krwData->avg_target) {
                $target = ($jktData->avg_target + $krwData->avg_target) / 2;
            } else {
                $target = $jktData->avg_target ?: ($krwData->avg_target ?: 0);
            }
            $targets[] = round($target, 2);
        }

        return [
            'year' => 'all',
            'labels' => $labels,
            'jakarta' => $jakartaPpm,
            'karawang' => $karawangPpm,
            'jakarta_claims' => $jakartaClaims,
            'karawang_claims' => $karawangClaims,
            'combined_total' => $combinedTotal,
            'target' => $targets,
            'is_yearly' => true
        ];
    }

    /**
     * Get combined historical (yearly) and current (monthly) data.
     */
    public function getCombinedClaimData()
    {
        $currentYear = (int) date('Y');
        $monthlyYear = $currentYear;

        // Smart detect: If current year is empty and it's early (Jan-Mar), 
        // use previous year for the monthly section
        $hasCurrentYearData = CustomerClaim::withoutGlobalScope('plant')
            ->where('year', $currentYear)
            ->exists();

        if (!$hasCurrentYearData && date('n') <= 3) {
            $hasPrevYearData = CustomerClaim::withoutGlobalScope('plant')
                ->where('year', $currentYear - 1)
                ->exists();
            if ($hasPrevYearData) {
                $monthlyYear = $currentYear - 1;
            }
        }

        $historicalYears = range(2022, $monthlyYear - 1);

        $jakartaPlantId = Plant::where('code', 'jakarta')->value('id');
        $karawangPlantId = Plant::where('code', 'karawang')->value('id');
        $totalPlantId = Plant::where('code', 'total')->value('id');

        $labels = [];
        $jakartaPpm = [];
        $karawangPpm = [];
        $jakartaClaims = [];
        $karawangClaims = [];
        $combinedTotal = [];
        $targets = [];

        // 1. Get Historical Yearly Data (Month = 0)
        foreach ($historicalYears as $year) {
            $labels[] = (string) $year;

            $jkt = CustomerClaim::withoutGlobalScope('plant')
                ->where('year', $year)
                ->where('month', 0)
                ->where('plant_id', $jakartaPlantId)
                ->first();

            $krw = CustomerClaim::withoutGlobalScope('plant')
                ->where('year', $year)
                ->where('month', 0)
                ->where('plant_id', $karawangPlantId)
                ->first();

            $totalData = CustomerClaim::withoutGlobalScope('plant')
                ->where('year', $year)
                ->where('month', 0)
                ->where('plant_id', $totalPlantId)
                ->first();

            $jakartaPpm[] = (float) ($jkt->ppm_value ?? 0);
            $karawangPpm[] = (float) ($krw->ppm_value ?? 0);
            $jakartaClaims[] = (int) ($jkt->total_claims ?? 0);
            $karawangClaims[] = (int) ($krw->total_claims ?? 0);
            $combinedTotal[] = (float) ($totalData->total_claims ?? 0);
            $targets[] = (float) ($jkt ? $jkt->target_value : ($krw ? $krw->target_value : 0));
        }

        // 2. Get Current Year Monthly Data (Month 1-12)
        $monthlyYearClaims = CustomerClaim::withoutGlobalScope('plant')
            ->where('year', $monthlyYear)
            ->where('month', '>', 0)
            ->get();

        for ($m = 1; $m <= 12; $m++) {
            $labels[] = \Carbon\Carbon::createFromDate($monthlyYear, $m, 1)->format('M');

            $jkt = $monthlyYearClaims->where('month', $m)->where('plant_id', $jakartaPlantId)->first();
            $krw = $monthlyYearClaims->where('month', $m)->where('plant_id', $karawangPlantId)->first();
            $totalData = $monthlyYearClaims->where('month', $m)->where('plant_id', $totalPlantId)->first();

            $jakartaPpm[] = (float) ($jkt->ppm_value ?? 0);
            $karawangPpm[] = (float) ($krw->ppm_value ?? 0);
            $jakartaClaims[] = (int) ($jkt->total_claims ?? 0);
            $karawangClaims[] = (int) ($krw->total_claims ?? 0);
            $combinedTotal[] = (float) ($totalData->total_claims ?? 0);
            $targets[] = (float) ($jkt ? $jkt->target_value : ($krw ? $krw->target_value : 0));
        }

        return [
            'year' => 'combined',
            'active_year' => $monthlyYear,
            'labels' => $labels,
            'jakarta' => $jakartaPpm,
            'karawang' => $karawangPpm,
            'jakarta_claims' => $jakartaClaims,
            'karawang_claims' => $karawangClaims,
            'combined_total' => $combinedTotal,
            'target' => $targets,
            'is_yearly' => false // Using monthly-style rendering but with custom labels
        ];
    }

    /**
     * Get Claim Frequency Data from CustomerClaimRecord (list claim)
     * Counts how many claim records per month per plant
     *
     * @param int|null $year
     * @return array
     */
    public function getClaimFrequencyData($year = null): array
    {
        $year = $year ?? (int) date('Y');

        $jakartaPlantId = Plant::resolveId('jakarta');
        $karawangPlantId = Plant::resolveId('karawang');

        // Query claim records grouped by month and plant
        $records = CustomerClaimRecord::withoutGlobalScope('plant')
            ->whereYear('tanggal_claim', $year)
            ->selectRaw('MONTH(tanggal_claim) as month, plant_id, COUNT(*) as total')
            ->groupBy('month', 'plant_id')
            ->get();

        $labels = [];
        $jakartaCounts = [];
        $karawangCounts = [];

        for ($m = 1; $m <= 12; $m++) {
            $labels[] = \Carbon\Carbon::createFromDate($year, $m, 1)->format('M');

            $jkt = $records->where('month', $m)->where('plant_id', $jakartaPlantId)->first();
            $krw = $records->where('month', $m)->where('plant_id', $karawangPlantId)->first();

            $jakartaCounts[] = (int) ($jkt->total ?? 0);
            $karawangCounts[] = (int) ($krw->total ?? 0);
        }

        return [
            'year' => $year,
            'labels' => $labels,
            'jakarta' => $jakartaCounts,
            'karawang' => $karawangCounts,
        ];
    }

    /**
     * Get Daily NG Rate Data for Spline Charts
     */
    public function getNgRateData(): array
    {
        $days = 30;
        $endDate = now()->endOfDay();
        $startDate = now()->subDays($days - 1)->startOfDay();

        $dates = [];
        for ($i = 0; $i < $days; $i++) {
            $dates[] = now()->subDays($days - 1 - $i)->format('Y-m-d');
        }

        $jakartaPlantId = Plant::resolveId('jakarta');
        $karawangPlantId = Plant::resolveId('karawang');

        return [
            'labels' => $dates,
            'jakarta' => $this->getPlantNgRate($jakartaPlantId, $startDate, $endDate, $dates, ['sub_assy', 'in_process']),
            'karawang' => $this->getPlantNgRate($karawangPlantId, $startDate, $endDate, $dates, ['sub_assy', 'in_process', 'cross_cut_plating', 'cross_cut_painting']),
        ];
    }

    private function getPlantNgRate($plantId, $start, $end, $dates, $types): array
    {
        $result = [];
        foreach ($types as $type) {
            $data = [];
            $records = [];

            if ($type === 'sub_assy') {
                $records = SubAssyChecksheet::where('plant_id', $plantId)
                    ->whereBetween('date', [$start, $end])
                    ->selectRaw('date as group_date, SUM(total_ng) as ng, SUM(total_qty) as total')
                    ->groupBy('group_date')
                    ->get()
                    ->keyBy(fn($i) => \Carbon\Carbon::parse($i->group_date)->format('Y-m-d'));
            } elseif ($type === 'in_process') {
                $records = InProcessChecksheet::where('plant_id', $plantId)
                    ->whereBetween('date', [$start, $end])
                    ->selectRaw('date as group_date, SUM(total_ng) as ng, SUM(total_qty) as total')
                    ->groupBy('group_date')
                    ->get()
                    ->keyBy(fn($i) => \Carbon\Carbon::parse($i->group_date)->format('Y-m-d'));
            } elseif ($type === 'cross_cut_plating') {
                $records = CrossCutChecksheet::where('plant_id', $plantId)
                    ->whereBetween('production_datetime', [$start, $end])
                    ->selectRaw('DATE(production_datetime) as group_date, SUM(total_ng) as ng, SUM(sampling_qty) as total')
                    ->groupBy('group_date')
                    ->get()
                    ->keyBy('group_date');
            } elseif ($type === 'cross_cut_painting') {
                $records = CrossCutPaintingChecksheet::where('plant_id', $plantId)
                    ->whereBetween('production_datetime', [$start, $end])
                    ->selectRaw('DATE(production_datetime) as group_date, SUM(total_ng) as ng, SUM(sampling_qty) as total')
                    ->groupBy('group_date')
                    ->get()
                    ->keyBy('group_date');
            } elseif ($type === 'sortir') {
                $records = SortirChecksheet::where('plant_id', $plantId)
                    ->whereBetween('date', [$start, $end])
                    ->selectRaw('date as group_date, SUM(total_ng) as ng, SUM(sampling_qty) as total')
                    ->groupBy('group_date')
                    ->get()
                    ->keyBy(fn($i) => \Carbon\Carbon::parse($i->group_date)->format('Y-m-d'));
            }

            foreach ($dates as $date) {
                $rec = $records->get($date);
                if ($rec && $rec->total > 0) {
                    $data[] = round(($rec->ng / $rec->total) * 100, 2);
                } else {
                    $data[] = 0;
                }
            }
            $result[$type] = $data;
        }

        return $result;
    }
}
