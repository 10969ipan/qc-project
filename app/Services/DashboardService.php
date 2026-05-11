<?php

namespace App\Services;

use App\Models\InProcessChecksheet;
use App\Models\FirstPieceApproval;
use App\Models\SubAssyChecksheet;
use App\Models\CrossCutChecksheet;
use App\Models\CrossCutPaintingChecksheet;
use App\Models\SortirChecksheet;
use App\Models\MachineStatus;
use App\Models\MonthlyReport;
use App\Models\CustomerClaim;
use App\Models\CustomerClaimRecord;
use App\Models\DoubleTapeChecksheet;
use App\Models\PlatingChecksheet;
use App\Models\Plant;
use App\Helpers\ShiftHelper;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService extends BaseService
{
    /**
     * Get lightweight live data for TV real-time polling.
     * Only returns production monitoring data (station cards) — no heavy stats.
     */
    public function getLiveDashboardData(): array
    {
        $productionMonitoring = $this->getProductionMonitoring('karawang');

        // Build a compact, serializable structure for the station cards
        $serializeStation = function ($item) {
            if (!$item) return null;
            return [
                'item_name'         => optional($item->item)->name,
                'part_number'       => optional($item->item)->part_number,
                'judgment'          => $item->judgment,
                'operator_initials' => $item->operator_initials,
                'created_at'        => $item->created_at?->format('H:i'),
            ];
        };

        $activeLines    = $productionMonitoring['activeLines']->map($serializeStation);
        $activeMachines = $productionMonitoring['activeMachines']->map($serializeStation);

        $serializeStatus = fn ($s) => $s ? ['status' => $s->status, 'description' => $s->description] : null;
        $lineStatuses    = $productionMonitoring['lineStatuses']->map($serializeStatus);
        $machineStatuses = $productionMonitoring['machineStatuses']->map($serializeStatus);

        // Operator map (initials -> name)
        $users = \App\Models\User::all();
        $operatorMap = [];
        foreach ($users as $u) {
            if ($u->initials) {
                $operatorMap[strtoupper($u->initials)] = $u->name;
            }
        }

        return [
            'timestamp'      => now()->toIso8601String(),
            'activeLines'    => $activeLines,
            'activeMachines' => $activeMachines,
            'lineStatuses'   => $lineStatuses,
            'machineStatuses'=> $machineStatuses,
            'operatorMap'    => $operatorMap,
        ];
    }

    /**
     * Get all dashboard data
     * 
     * @return array
     */
    public function getDashboardData($month = null, $year = null): array
    {
        $month = is_numeric($month) ? (int)$month : date('n');
        $year = is_numeric($year) ? (int)$year : date('Y');

        $authRole = auth()->user()->role;
        $plantId = auth()->user()->plant_id;
        $cacheKey = "dashboard_data_{$authRole}_{$plantId}_" . request('plant') . "_{$year}_{$month}";

        // TEMPORARILY BYPASSING CACHE FOR DEBUGGING
        // return Cache::remember($cacheKey, now()->addMinutes(1), function () use ($authRole, $month, $year) {
        return (function () use ($authRole, $month, $year) {
            $combinedStats = $this->calculateApprovalStats('all', false, null, $month, $year);
            $dailyCombinedStats = $this->calculateApprovalStats('all', true);

            $statsJakarta = null;
            $statsKarawang = null;
            $dailyStatsJakarta = null;
            $dailyStatsKarawang = null;

            $dualViewRoles = ['admin', 'manager', 'asst_manager', 'manager_qc', 'asst_manager_qc', 'oshef'];

            $productionJakarta = [];
            $productionKarawang = [];

            $dailyStatsSubAssy = null;
            $dailyStatsInProcess = null;

            if (in_array($authRole, $dualViewRoles)) {
                $statsJakarta = $this->calculateApprovalStats('jakarta', false, null, $month, $year);
                $statsKarawang = $this->calculateApprovalStats('karawang', false, null, $month, $year);
                
                $dailyStatsJakarta = $this->calculateApprovalStats('jakarta', true);
                $dailyStatsKarawang = $this->calculateApprovalStats('karawang', true);

                $targetPlant = request('plant') ?: 'karawang';
                $dailyStatsSubAssy = $this->calculateApprovalStats($targetPlant, true, 'sub_assy');
                $dailyStatsInProcess = $this->calculateApprovalStats($targetPlant, true, 'in_process');

                $productionJakarta = $this->getProductionMonitoring('jakarta');
                $productionKarawang = $this->getProductionMonitoring('karawang');
            } else {
                $dailyStatsSubAssy = $this->calculateApprovalStats(null, true, 'sub_assy');
                $dailyStatsInProcess = $this->calculateApprovalStats(null, true, 'in_process');
            }
            $productionMonitoring = $this->getProductionMonitoring(); // Default

            $activeReport = MonthlyReport::where('is_active', true)->first();

            // NG Rate Data for Charts
            $ngRateData = $this->getNgRateData();

            $currentPlant = auth()->user()->plant?->name ?? 'unknown';

            // Operator Map (Initials -> Name)
            // Maps both by stored initials AND by full name for consistent display
            $users = \App\Models\User::all();
            $operatorMap = [];
            foreach ($users as $u) {
                // Map by stored initials (e.g. "SH" => "Sopian Handani")
                $init = $u->initials;
                if ($init) {
                    $operatorMap[$init] = $u->name;
                    // Also map case-insensitive variants
                    $operatorMap[strtoupper($init)] = $u->name;
                    $operatorMap[strtolower($init)] = $u->name;
                }
                // Map by full name so if operator_initials already contains full name it still resolves
                if ($u->name) {
                    $operatorMap[$u->name] = $u->name;
                    // Also map abbreviated first+last name combos (e.g. "Sopian H" => "Sopian Handani")
                    $nameParts = explode(' ', $u->name);
                    if (count($nameParts) > 1) {
                        $shortName = $nameParts[0] . ' ' . strtoupper(substr(end($nameParts), 0, 1));
                        $operatorMap[$shortName] = $u->name;
                    }
                }
            }

            // Flag for dual view mode
            $isDualView = in_array($authRole, $dualViewRoles);

            // Claim Frequency Data (from list claim records)
            $claimFrequency = $this->getClaimFrequencyData();

            return array_merge(
                compact('combinedStats', 'statsJakarta', 'statsKarawang', 'dailyCombinedStats', 'dailyStatsJakarta', 'dailyStatsKarawang', 'dailyStatsSubAssy', 'dailyStatsInProcess', 'activeReport', 'productionJakarta', 'productionKarawang', 'ngRateData', 'currentPlant', 'operatorMap', 'isDualView', 'claimFrequency'),
                $productionMonitoring
            );
        })();
    }

    /**
     * Get the numeric approval rate (%) for a specific plant
     * Based on Karu & Kashift approval on H-1 and Today's data
     */
    public function getDailyApprovalRate($plantId): float
    {
        // Using existing calculation logic but specifically for daily stats
        $stats = $this->calculateApprovalStats($plantId, true);
        
        $total = $stats['approved'] + $stats['rejected'] + $stats['pending'];
        if ($total === 0) return 100.0; // If no data, consider it 100% (not blocking)
        
        return round((($stats['approved'] + $stats['rejected']) / $total) * 100, 2);
    }

    /**
     * Calculate global approval statistics
     */
    private function calculateApprovalStats($plantOverride = null, bool $dailyOnly = false, ?string $type = null, $month = null, $year = null): array
    {
        $stats = ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'pending_late' => 0];
        
        // Use override if provided, otherwise check request or auth user
        // If plantOverride is 'all', we want global stats (no plant filtering)
        $plantIdentifier = $plantOverride === 'all' ? null : ($plantOverride ?? request('plant') ?? auth()->user()->plant_id);
        $plantId = $plantIdentifier ? $this->resolvePlantId($plantIdentifier) : null;

        // Determine plant code for conditional filtering
        $plantCode = null;
        if ($plantId) {
            $plantCode = Plant::where('id', $plantId)->value('code');
        }

        if (!$type || $type === 'sub_assy') {
            $this->processModelStats(SubAssyChecksheet::class, $stats, $plantId, $dailyOnly, $month, $year);
        }

        if (!$type || $type === 'in_process') {
            $this->processModelStats(InProcessChecksheet::class, $stats, $plantId, $dailyOnly, $month, $year);
            $this->processModelStats(FirstPieceApproval::class, $stats, $plantId, $dailyOnly, $month, $year);
        }

        if (!$type) {
            // Jakarta only shows Sub Assy and In Process per user request
            if ($plantCode !== 'jakarta') {
                $this->processModelStats(CrossCutChecksheet::class, $stats, $plantId, $dailyOnly, $month, $year);
                $this->processModelStats(CrossCutPaintingChecksheet::class, $stats, $plantId, $dailyOnly, $month, $year);
                $this->processModelStats(DoubleTapeChecksheet::class, $stats, $plantId, $dailyOnly, $month, $year);
                $this->processModelStats(PlatingChecksheet::class, $stats, $plantId, $dailyOnly, $month, $year);
            }
        }

        return $stats;
    }

    private function processModelStats(string $modelClass, array &$stats, ?string $plantId = null, bool $dailyOnly = false, $month = null, $year = null): void
    {
        $table = (new $modelClass)->getTable();
        
        // Define possible signature columns. For daily stats, we only record Karu and Kashift per user request.
        $potentialColumns = $dailyOnly 
            ? ['kashift_qc', 'karu_qc'] 
            : ['kashift_qc', 'supervisor_qc', 'karu_qc', 'asst_manager_qc', 'manager_qc'];
        $columns = [];
        foreach ($potentialColumns as $col) {
            if (Schema::hasColumn($table, $col)) {
                $columns[] = $col;
            }
        }
        
        if (empty($columns)) return;

        // Default column names are 'date' or 'check_date' depending on model implementation
        $dateColumn = 'date';
        if (Schema::hasColumn($table, 'check_date')) {
            $dateColumn = 'check_date';
        } elseif (Schema::hasColumn($table, 'production_datetime')) {
            $dateColumn = 'production_datetime';
        }

        // Define what is considered "Late" (more than 24 hours since creation)
        $lateThreshold = now()->subHours(24);

        $query = DB::table($table);
        if ($plantId) {
            $query->where('plant_id', $plantId);
        }

        // Filter data H-1 saja (Hari ini tidak termasuk) per user request
        if ($dailyOnly) {
            $query->whereDate($dateColumn, '=', now()->subDay()->toDateString());
        } elseif ($month && $year) {
            // Apply Month and Year filtering for main statistics
            $query->whereMonth($dateColumn, $month)
                  ->whereYear($dateColumn, $year);
        }

        // Build a single aggregated query for all columns to ensure atomicity and performance
        $selects = [];
        foreach ($columns as $column) {
            $selects[] = "SUM(CASE WHEN UPPER($column) = 'REJECTED' THEN 1 ELSE 0 END) as {$column}_rejected";
            $selects[] = "SUM(CASE WHEN $column IS NOT NULL AND $column != '' AND UPPER($column) != 'REJECTED' THEN 1 ELSE 0 END) as {$column}_approved";
            $selects[] = "SUM(CASE WHEN ($column IS NULL OR $column = '') THEN 1 ELSE 0 END) as {$column}_pending";
            $selects[] = "SUM(CASE WHEN ($column IS NULL OR $column = '') AND created_at < '{$lateThreshold}' THEN 1 ELSE 0 END) as {$column}_pending_late";
        }

        // Add total row count to help with debugging if needed
        $selects[] = "COUNT(*) as total_rows";

        $results = $query->selectRaw(implode(', ', $selects))->first();

        \Illuminate\Support\Facades\Log::info("DEBUG: Stats for {$modelClass} ({$plantId}) dailyOnly={$dailyOnly}: " . json_encode($results));

        if ($results && $results->total_rows > 0) {
            foreach ($columns as $column) {
                $stats['rejected'] += (int) ($results->{"{$column}_rejected"} ?? 0);
                $stats['approved'] += (int) ($results->{"{$column}_approved"} ?? 0);
                $stats['pending'] += (int) ($results->{"{$column}_pending"} ?? 0);
                $stats['pending_late'] = ($stats['pending_late'] ?? 0) + (int) ($results->{"{$column}_pending_late"} ?? 0);
            }
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
        $plantId = $this->resolvePlantId($plantIdentifier ?? request('plant') ?? auth()->user()->plant_id);

        $currentProductionDate = ShiftHelper::getProductionDate($now);
        $currentShift = ShiftHelper::getShift($now);
        $shiftStartTime = ShiftHelper::getShiftStartTime($currentProductionDate, $currentShift, $now->dayOfWeek);

        $activeLines = $this->fetchActiveLines($currentProductionDate, $currentShift, $plantId);
        $activeMachines = $this->fetchActiveMachines($currentProductionDate, $currentShift, $plantId);

        $manualStatuses = $this->fetchManualStatuses($shiftStartTime, $plantId);
        $lineStatuses = $manualStatuses->where('type', 'line')->keyBy('number');
        $machineStatuses = $manualStatuses->where('type', 'machine')->keyBy('number');

        $runningLinesCount = $activeLines->count() - $activeLines->keys()->intersect($lineStatuses->keys())->count();
        $runningMachinesCount = $activeMachines->count() - $activeMachines->keys()->intersect($machineStatuses->whereIn('status', ['stopped', 'trouble'])->keys())->count();

        return compact('activeLines', 'activeMachines', 'lineStatuses', 'machineStatuses', 'runningLinesCount', 'runningMachinesCount');
    }

    private function fetchActiveLines($date, $shift, $plantId)
    {
        // Change logic to fetch LATEST record for each line within last 48 hours
        // This ensures the TV always has "Active" data similar to what's at the top of the index page
        $query = SubAssyChecksheet::with('item')
            ->where('date', $date)
            ->where('shift', $shift)
            ->whereNotNull('line')
            ->orderBy('created_at', 'desc');

        if ($plantId) {
            $query->where('plant_id', $plantId);
        }

        return $query->get()
            ->unique('line')
            ->mapWithKeys(fn($item) => [(int) $item->line => $item]);
    }

    private function fetchActiveMachines($date, $shift, $plantId)
    {
        // Change logic to fetch LATEST record for each machine within last 48 hours
        $inProcessQuery = InProcessChecksheet::with('item')
            ->where('date', $date)
            ->where('shift', $shift)
            ->whereNotNull('code_machine')
            ->orderBy('created_at', 'desc');

        if ($plantId) {
            $inProcessQuery->where('plant_id', $plantId);
        }

        $fpaQuery = FirstPieceApproval::with('item')
            ->where('date', $date)
            ->where('shift', $shift)
            ->whereNotNull('code_machine')
            ->orderBy('created_at', 'desc');

        if ($plantId) {
            $fpaQuery->where('plant_id', $plantId);
        }

        return $inProcessQuery->get()
            ->concat($fpaQuery->get())
            ->sortByDesc('created_at')
            ->unique('code_machine')
            ->mapWithKeys(fn($item) => [(int) $item->code_machine => $item]);
    }

    private function fetchManualStatuses($shiftStartTime, $plantId)
    {
        $query = MachineStatus::whereIn('status', ['maintenance', 'stopped', 'trouble', 'standby']);

        if ($plantId) {
            $query->where('plant_id', $plantId);
        }

        return $query->get();
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
            'jakarta' => $this->getPlantNgRate($jakartaPlantId, $startDate, $endDate, $dates, ['sub_assy', 'in_process', 'fpa']),
            'karawang' => $this->getPlantNgRate($karawangPlantId, $startDate, $endDate, $dates, ['sub_assy', 'in_process', 'fpa', 'cross_cut_plating', 'cross_cut_painting', 'double_tape', 'plating']),
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
            } elseif ($type === 'fpa') {
                $records = FirstPieceApproval::where('plant_id', $plantId)
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
            } elseif ($type === 'double_tape') {
                $records = DoubleTapeChecksheet::where('plant_id', $plantId)
                    ->whereBetween('date', [$start, $end])
                    ->selectRaw('date as group_date, SUM(total_ng) as ng, SUM(total_qty) as total')
                    ->groupBy('group_date')
                    ->get()
                    ->keyBy(fn($i) => \Carbon\Carbon::parse($i->group_date)->format('Y-m-d'));
            } elseif ($type === 'plating') {
                $records = PlatingChecksheet::where('plant_id', $plantId)
                    ->whereBetween('date', [$start, $end])
                    ->selectRaw('date as group_date, SUM(total_ng) as ng, SUM(total_qty) as total')
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
