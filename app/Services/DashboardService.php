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
        $productionMonitoring = $this->getProductionMonitoring($recentDate);
        $activeReport = MonthlyReport::where('is_active', true)->first();

        return array_merge(
            compact('combinedStats', 'activeReport'),
            $productionMonitoring
        );
    }

    /**
     * Calculate global approval statistics
     */
    private function calculateApprovalStats(): array
    {
        $stats = ['pending' => 0, 'approved' => 0, 'rejected' => 0];

        $this->processModelStats(InProcessChecksheet::class, $stats);
        $this->processModelStats(SubAssyChecksheet::class, $stats);
        $this->processModelStats(CrossCutChecksheet::class, $stats);

        return $stats;
    }

    private function processModelStats(string $modelClass, array &$stats): void
    {
        $table = (new $modelClass)->getTable();
        $items = $modelClass::all();
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
    private function getProductionMonitoring(string $recentDate): array
    {
        // Active Sub Assy Lines
        $activeLines = SubAssyChecksheet::with('item')
            ->whereDate('date', '>=', $recentDate)
            ->whereNotNull('line')
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('line')
            ->mapWithKeys(fn($item) => [(int) $item->line => $item]);

        // Active In Process Machines
        $activeMachines = InProcessChecksheet::with('item')
            ->whereDate('date', '>=', $recentDate)
            ->whereNotNull('code_machine')
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('code_machine')
            ->mapWithKeys(fn($item) => [(int) $item->code_machine => $item]);

        // Status Overrides
        $manualStatuses = MachineStatus::whereIn('status', ['maintenance', 'stopped', 'trouble'])->get();
        $lineStatuses = $manualStatuses->where('type', 'line')->keyBy('number');
        $machineStatuses = $manualStatuses->where('type', 'machine')->keyBy('number');

        // Running Counts
        $runningLinesCount = $activeLines->count() - $activeLines->keys()->intersect($lineStatuses->keys())->count();
        $runningMachinesCount = $activeMachines->count() - $activeMachines->keys()->intersect($machineStatuses->whereIn('status', ['stopped', 'trouble'])->keys())->count();

        return compact('activeLines', 'activeMachines', 'lineStatuses', 'machineStatuses', 'runningLinesCount', 'runningMachinesCount');
    }
}
