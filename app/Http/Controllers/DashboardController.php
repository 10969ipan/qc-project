<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InProcessChecksheet;
use App\Models\Checksheet; // Sub Assy
use App\Models\CrossCutChecksheet;
use App\Models\MachineStatus;
use App\Services\GoogleSheetService;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $plant = auth()->user()->plant;
        $recentDate = now()->subDays(1)->toDateString();

        // Initialize combined stats
        $combinedStats = ['pending' => 0, 'approved' => 0, 'rejected' => 0];

        // Helper to update stats
        $updateStat = function (&$stats, $value) {
            if ($value === 'REJECTED') {
                $stats['rejected']++;
            } elseif (!empty($value)) {
                $stats['approved']++;
            } else {
                $stats['pending']++;
            }
        };

        $processModel = function ($modelClass) use (&$combinedStats, $updateStat) {
            $table = (new $modelClass)->getTable();

            // Fetch ALL data without any filters (Date or Plant)
            $items = $modelClass::get();
            $hasKaru = Schema::hasColumn($table, 'karu_qc');

            foreach ($items as $item) {
                // Karu
                if ($hasKaru) {
                    $updateStat($combinedStats, $item->karu_qc);
                }
                // Kashift
                $updateStat($combinedStats, $item->kashift_qc);
                // Supervisor
                $updateStat($combinedStats, $item->supervisor_qc);
            }
        };

        $processModel(InProcessChecksheet::class);
        $processModel(Checksheet::class);
        $processModel(CrossCutChecksheet::class);

        // --- Production Monitoring ---
        // Active Sub Assy Lines - Get Latest from last 48 hours
        $activeLinesRaw = Checksheet::with('item')
            ->whereDate('date', '>=', $recentDate)
            ->whereNotNull('line')
            ->orderBy('created_at', 'desc')
            ->get();

        $activeLines = $activeLinesRaw->unique('line')->mapWithKeys(function ($item) {
            return [(int) $item->line => $item];
        });

        // Active In Process Machines - Get Latest from last 48 hours
        $activeMachinesRaw = InProcessChecksheet::with('item')
            ->whereDate('date', '>=', $recentDate)
            ->whereNotNull('code_machine')
            ->orderBy('created_at', 'desc')
            ->get();

        $activeMachines = $activeMachinesRaw->unique('code_machine')->mapWithKeys(function ($item) {
            return [(int) $item->code_machine => $item];
        });

        // Fetch Manual Status Overrides (trait handles plant)
        $manualStatuses = MachineStatus::whereIn('status', ['maintenance', 'stopped', 'trouble'])->get();
        $lineStatuses = $manualStatuses->where('type', 'line')->keyBy('number');
        $machineStatuses = $manualStatuses->where('type', 'machine')->keyBy('number');

        // Calculate Running Counts (Active - Overridden)
        $runningLinesCount = $activeLines->count() - $activeLines->keys()->intersect($lineStatuses->keys())->count();
        $runningMachinesCount = $activeMachines->count() - $activeMachines->keys()->intersect($machineStatuses->whereIn('status', ['stopped', 'trouble'])->keys())->count();

        // Fetch active monthly report
        $activeReport = \App\Models\MonthlyReport::where('is_active', true)->first();

        return view('layouts.dashboard', compact('combinedStats', 'activeLines', 'activeMachines', 'lineStatuses', 'machineStatuses', 'runningLinesCount', 'runningMachinesCount', 'activeReport'));
    }
}
