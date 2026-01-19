<?php

namespace App\Services;

use App\Models\InProcessChecksheet;
use App\Models\SubAssyChecksheet;
use App\Models\CrossCutChecksheet;
use App\Models\MachineStatus;
use App\Models\MonthlyReport;
use App\Models\CustomerClaim;
use App\Models\Plant;
use Illuminate\Support\Facades\Schema;

class DashboardService extends BaseService
{
    /**
     * Get all dashboard data
     * 
     * @return array
     */
    public function getDashboardData(): array
    {
        $combinedStats = $this->calculateApprovalStats();

        $statsJakarta = null;
        $statsKarawang = null;

        $authRole = auth()->user()->role;
        $dualViewRoles = ['admin', 'manager', 'asst_manager', 'manager_qc', 'asst_manager_qc']; // Covers potentially both role naming conventions

        $productionJakarta = [];
        $productionKarawang = [];

        if (in_array($authRole, $dualViewRoles)) {
            $statsJakarta = $this->calculateApprovalStats('jakarta');
            $statsKarawang = $this->calculateApprovalStats('karawang');

            $productionJakarta = $this->getProductionMonitoring('jakarta');
            $productionKarawang = $this->getProductionMonitoring('karawang');
        }

        $productionMonitoring = $this->getProductionMonitoring(); // Default

        $activeReport = MonthlyReport::where('is_active', true)->first();

        // Merge everything. 
        // We nest the plant-specific production data to avoid collision with standard 'activeLines' etc.
        return array_merge(
            compact('combinedStats', 'statsJakarta', 'statsKarawang', 'activeReport', 'productionJakarta', 'productionKarawang'),
            $productionMonitoring
        );
    }

    /**
     * Calculate global approval statistics
     */
    private function calculateApprovalStats(?string $plantOverride = null): array
    {
        $stats = ['pending' => 0, 'approved' => 0, 'rejected' => 0];
        // Use override if provided, otherwise check request or auth user
        $plantId = $this->resolvePlantId($plantOverride ?? request('plant') ?? auth()->user()->plant_id);

        $this->processModelStats(InProcessChecksheet::class, $stats, $plantId);
        $this->processModelStats(SubAssyChecksheet::class, $stats, $plantId);
        $this->processModelStats(CrossCutChecksheet::class, $stats, $plantId);

        return $stats;
    }

    private function processModelStats(string $modelClass, array &$stats, ?string $plantId = null): void
    {
        $table = (new $modelClass)->getTable();
        $query = $modelClass::query();

        if ($plantId) {
            $query->where($query->getModel()->getTable() . '.plant_id', $plantId);
        }

        // Count individual columns (Karu, Kashift, SPV). 
        $items = $query->get();
        $hasKaru = Schema::hasColumn($table, 'karu_qc');

        foreach ($items as $item) {
            if ($hasKaru)
                $this->updateStat($stats, $item->karu_qc);
            $this->updateStat($stats, $item->kashift_qc);
            $this->updateStat($stats, $item->supervisor_qc);
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

        // Determine current production date and shift
        // Day starts at 07:00
        $currentProductionDate = ($hour < 7) ? $now->copy()->subDay()->toDateString() : $now->toDateString();

        if ($hour >= 7 && $hour < 15) {
            $currentShift = 1;
            $shiftStartTime = \Carbon\Carbon::parse($currentProductionDate . ' 07:00:00');
        } elseif ($hour >= 15 && $hour < 23) {
            $currentShift = 2;
            $shiftStartTime = \Carbon\Carbon::parse($currentProductionDate . ' 15:00:00');
        } else {
            $currentShift = 3;
            $shiftStartTime = \Carbon\Carbon::parse($currentProductionDate . ' 23:00:00');
        }

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
        $statusQuery = MachineStatus::whereIn('status', ['maintenance', 'stopped', 'trouble'])
            ->where('updated_at', '>=', $shiftStartTime);

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

        $labels = [];
        $jakartaPpm = [];
        $karawangPpm = [];
        $targets = [];

        for ($m = 1; $m <= 12; $m++) {
            $labels[] = \Carbon\Carbon::createFromDate($year, $m, 1)->format('M');
            $jkt = $claims->where('month', $m)->where('plant_id', $jakartaPlantId)->first();
            $krw = $claims->where('month', $m)->where('plant_id', $karawangPlantId)->first();

            // Get target from any record in that month, or default to 0
            $target = ($jkt ? $jkt->target_value : ($krw ? $krw->target_value : 0));

            $jakartaPpm[] = (float) ($jkt->ppm_value ?? 0);
            $karawangPpm[] = (float) ($krw->ppm_value ?? 0);
            $targets[] = (float) $target;
        }

        return [
            'year' => $year,
            'labels' => $labels,
            'jakarta' => $jakartaPpm,
            'karawang' => $karawangPpm,
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

        $jakartaPlant = Plant::where('code', 'jakarta')->first();
        $karawangPlant = Plant::where('code', 'karawang')->first();

        $labels = [];
        $jakartaPpm = [];
        $karawangPpm = [];
        $targets = [];

        foreach ($years as $year) {
            $labels[] = (string) $year;

            // Jakarta average for the year
            $jktData = CustomerClaim::withoutGlobalScope('plant')
                ->where('plant_id', $jakartaPlant->id ?? null)
                ->where('year', $year)
                ->selectRaw('AVG(ppm_value) as avg_ppm, AVG(target_value) as avg_target')
                ->first();

            $jakartaPpm[] = $jktData->avg_ppm ? round($jktData->avg_ppm, 2) : 0;

            // Karawang average for the year
            $krwData = CustomerClaim::withoutGlobalScope('plant')
                ->where('plant_id', $karawangPlant->id ?? null)
                ->where('year', $year)
                ->selectRaw('AVG(ppm_value) as avg_ppm, AVG(target_value) as avg_target')
                ->first();

            $karawangPpm[] = $krwData->avg_ppm ? round($krwData->avg_ppm, 2) : 0;

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

        $jakartaPlantId = Plant::resolveId('jakarta');
        $karawangPlantId = Plant::resolveId('karawang');

        $labels = [];
        $jakartaPpm = [];
        $karawangPpm = [];
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

            $jakartaPpm[] = (float) ($jkt->ppm_value ?? 0);
            $karawangPpm[] = (float) ($krw->ppm_value ?? 0);
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

            $jakartaPpm[] = (float) ($jkt->ppm_value ?? 0);
            $karawangPpm[] = (float) ($krw->ppm_value ?? 0);
            $targets[] = (float) ($jkt ? $jkt->target_value : ($krw ? $krw->target_value : 0));
        }

        return [
            'year' => 'combined',
            'active_year' => $monthlyYear,
            'labels' => $labels,
            'jakarta' => $jakartaPpm,
            'karawang' => $karawangPpm,
            'target' => $targets,
            'is_yearly' => false // Using monthly-style rendering but with custom labels
        ];
    }
}
