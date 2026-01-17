<?php

namespace App\Services;

use App\Models\InProcessChecksheet;
use App\Models\SubAssyChecksheet;
use App\Models\CrossCutChecksheet;
use App\Models\MachineStatus;
use App\Models\MonthlyReport;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;

class DashboardService extends BaseService
{
    /**
     * Get all dashboard data
     * 
     * @return array
     */
    public function getDashboardData(): array
    {
        $recentDate = now()->subDays(1)->toDateString();

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

            $productionJakarta = $this->getProductionMonitoring($recentDate, 'jakarta');
            $productionKarawang = $this->getProductionMonitoring($recentDate, 'karawang');
        }

        $productionMonitoring = $this->getProductionMonitoring($recentDate); // Default (request/user scoped via Global Scope usually, or if Admin without scope)
        // Note: For admin, default getProductionMonitoring fetches everything if no scope applied.
        // We'll trust the modified getProductionMonitoring to handle plant filtering if passed.

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

        // Optimization: Use separate counts instead of loading all models
        // However, logic below counts individual columns (Karu, Kashift, SPV). 
        // A single checksheet can contribute to multiple 'pending' or 'approved' counts?
        // Let's stick to the existing logic structure but optimize the query if possible, 
        // OR just keep iterating but over filtered set.
        // For safety and preserving exact original logic (which iterates columns):

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
    private function getProductionMonitoring(string $recentDate, ?string $plant = null): array
    {
        // Define query modifier for plant
        $applyPlant = function ($query) use ($plant) {
            if ($plant) {
                // Determine table name from query model
                // Use a trait or manually check. Since we use `with`, we are on the model builder.
                $query->where('plant', $plant);
            }
        };

        // Active Sub Assy Lines
        $linesQuery = SubAssyChecksheet::with('item')
            ->whereDate('date', '>=', $recentDate)
            ->whereNotNull('line')
            ->orderBy('created_at', 'desc');

        if ($plant)
            $linesQuery->where('plant', $plant);

        $activeLines = $linesQuery->get()
            ->unique('line')
            ->mapWithKeys(fn($item) => [(int) $item->line => $item]);

        // Active In Process Machines
        $machinesQuery = InProcessChecksheet::with('item')
            ->whereDate('date', '>=', $recentDate)
            ->whereNotNull('code_machine')
            ->orderBy('created_at', 'desc');

        if ($plant)
            $machinesQuery->where('plant', $plant);

        $activeMachines = $machinesQuery->get()
            ->unique('code_machine')
            ->mapWithKeys(fn($item) => [(int) $item->code_machine => $item]);

        // Status Overrides
        $statusQuery = MachineStatus::whereIn('status', ['maintenance', 'stopped', 'trouble']);
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
