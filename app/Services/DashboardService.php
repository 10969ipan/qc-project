<?php

namespace App\Services;

use App\Models\InProcessChecksheet;
use App\Models\SubAssyChecksheet;
use App\Models\CrossCutChecksheet;
use App\Models\MachineStatus;
use App\Models\MonthlyReport;
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
        $plant = $plantOverride ?? request('plant') ?? auth()->user()->plant;

        $this->processModelStats(InProcessChecksheet::class, $stats, $plant);
        $this->processModelStats(SubAssyChecksheet::class, $stats, $plant);
        $this->processModelStats(CrossCutChecksheet::class, $stats, $plant);

        return $stats;
    }

    private function processModelStats(string $modelClass, array &$stats, ?string $plant = null): void
    {
        $table = (new $modelClass)->getTable();
        $query = $modelClass::query();

        if ($plant) {
            $query->where('plant', $plant);
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
    private function getProductionMonitoring(?string $plant = null): array
    {
        $now = now();
        $hour = $now->hour;

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

        if ($plant)
            $linesQuery->where('plant', $plant);

        $activeLines = $linesQuery->get()
            ->unique('line')
            ->mapWithKeys(fn($item) => [(int) $item->line => $item]);

        // Active In Process Machines - Filter by current shift and date
        $machinesQuery = InProcessChecksheet::with('item')
            ->whereDate('date', $currentProductionDate)
            ->where('shift', $currentShift)
            ->whereNotNull('code_machine')
            ->orderBy('created_at', 'desc');

        if ($plant)
            $machinesQuery->where('plant', $plant);

        $activeMachines = $machinesQuery->get()
            ->unique('code_machine')
            ->mapWithKeys(fn($item) => [(int) $item->code_machine => $item]);

        // Status Overrides - Filter by shift start time to reset automatically when shift changes
        $statusQuery = MachineStatus::whereIn('status', ['maintenance', 'stopped', 'trouble'])
            ->where('updated_at', '>=', $shiftStartTime);

        if ($plant)
            $statusQuery->where('plant', $plant);

        $manualStatuses = $statusQuery->get();
        $lineStatuses = $manualStatuses->where('type', 'line')->keyBy('number');
        $machineStatuses = $manualStatuses->where('type', 'machine')->keyBy('number');

        // Running Counts
        $runningLinesCount = $activeLines->count() - $activeLines->keys()->intersect($lineStatuses->keys())->count();
        $runningMachinesCount = $activeMachines->count() - $activeMachines->keys()->intersect($machineStatuses->whereIn('status', ['stopped', 'trouble'])->keys())->count();

        return compact('activeLines', 'activeMachines', 'lineStatuses', 'machineStatuses', 'runningLinesCount', 'runningMachinesCount');
    }
}
