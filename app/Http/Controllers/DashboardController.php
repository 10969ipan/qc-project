<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InProcessChecksheet;
use App\Models\Checksheet; // Sub Assy
use App\Models\CrossCutChecksheet;
use App\Models\MachineStatus;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // --- Karu / Kashift Level ---
        $karuStats = [
            'pending' => 0,
            'approved' => 0,
            'rejected' => 0,
        ];

        // In Process (kashift_qc)
        $karuStats['pending'] += InProcessChecksheet::whereNull('kashift_qc')->count();
        $karuStats['approved'] += InProcessChecksheet::whereNotNull('kashift_qc')->where('kashift_qc', '!=', 'REJECTED')->count();
        $karuStats['rejected'] += InProcessChecksheet::where('kashift_qc', 'REJECTED')->count();

        // Sub Assy (kashift_qc) - Model is Checksheet
        $karuStats['pending'] += Checksheet::whereNull('kashift_qc')->count();
        $karuStats['approved'] += Checksheet::whereNotNull('kashift_qc')->where('kashift_qc', '!=', 'REJECTED')->count();
        $karuStats['rejected'] += Checksheet::where('kashift_qc', 'REJECTED')->count();

        // Cross Cut (karu_qc)
        $karuStats['pending'] += CrossCutChecksheet::whereNull('karu_qc')->count();
        $karuStats['approved'] += CrossCutChecksheet::whereNotNull('karu_qc')->where('karu_qc', '!=', 'REJECTED')->count();
        $karuStats['rejected'] += CrossCutChecksheet::where('karu_qc', 'REJECTED')->count();


        // --- Supervisor Level ---
        $spvStats = [
            'pending' => 0,
            'approved' => 0,
            'rejected' => 0,
        ];

        // In Process (supervisor_qc)
        $spvStats['pending'] += InProcessChecksheet::whereNull('supervisor_qc')->count();
        $spvStats['approved'] += InProcessChecksheet::whereNotNull('supervisor_qc')->where('supervisor_qc', '!=', 'REJECTED')->count();
        $spvStats['rejected'] += InProcessChecksheet::where('supervisor_qc', 'REJECTED')->count();

        // Sub Assy (supervisor_qc)
        $spvStats['pending'] += Checksheet::whereNull('supervisor_qc')->count();
        $spvStats['approved'] += Checksheet::whereNotNull('supervisor_qc')->where('supervisor_qc', '!=', 'REJECTED')->count();
        $spvStats['rejected'] += Checksheet::where('supervisor_qc', 'REJECTED')->count();

        // Cross Cut (supervisor_qc)
        $spvStats['pending'] += CrossCutChecksheet::whereNull('supervisor_qc')->count();
        $spvStats['approved'] += CrossCutChecksheet::whereNotNull('supervisor_qc')->where('supervisor_qc', '!=', 'REJECTED')->count();
        $spvStats['rejected'] += CrossCutChecksheet::where('supervisor_qc', 'REJECTED')->count();

        // --- Combine Stats ---
        $combinedStats = [
            'pending' => $karuStats['pending'] + $spvStats['pending'],
            'approved' => $karuStats['approved'] + $spvStats['approved'],
            'rejected' => $karuStats['rejected'] + $spvStats['rejected'],
        ];

        // --- Production Monitoring ---
        // Active Sub Assy Lines (1-15) - Get Latest per Line
        $activeLinesRaw = Checksheet::with('item')
            ->whereDate('date', now()->today())
            ->whereNotNull('line')
            ->orderBy('created_at', 'desc')
            ->get();

        $activeLines = $activeLinesRaw->unique('line')->mapWithKeys(function ($item) {
            return [(int) $item->line => $item];
        });

        // Active In Process Machines (1-18) - Get Latest per Machine
        $activeMachinesRaw = InProcessChecksheet::with('item')
            ->whereDate('date', now()->today())
            ->whereNotNull('code_machine')
            ->orderBy('created_at', 'desc')
            ->get();

        $activeMachines = $activeMachinesRaw->unique('code_machine')->mapWithKeys(function ($item) {
            return [(int) $item->code_machine => $item];
        });

        // Fetch Manual Status Overrides
        $manualStatuses = MachineStatus::whereIn('status', ['maintenance', 'stopped'])->get();
        $lineStatuses = $manualStatuses->where('type', 'line')->keyBy('number');
        $machineStatuses = $manualStatuses->where('type', 'machine')->keyBy('number');

        // Calculate Running Counts (Active - Overridden)
        $runningLinesCount = $activeLines->count() - $activeLines->keys()->intersect($lineStatuses->keys())->count();
        $runningMachinesCount = $activeMachines->count() - $activeMachines->keys()->intersect($machineStatuses->keys())->count();

        // Fetch active monthly report for dashboard display
        $activeReport = \App\Models\MonthlyReport::where('is_active', true)->first();

        return view('layouts.dashboard', compact('combinedStats', 'activeLines', 'activeMachines', 'lineStatuses', 'machineStatuses', 'runningLinesCount', 'runningMachinesCount', 'activeReport'));
    }
}
