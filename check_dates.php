<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CalibrationTool;

$tools = CalibrationTool::with('schedules')->whereHas('schedules', function ($q) {
    $q->whereYear('schedule_date', 2026)->whereMonth('schedule_date', 3);
})->get();

foreach ($tools as $tool) {
    echo "Tool ID: " . $tool->id . " | Nama: " . $tool->nama_alat . "\n";
    echo "  Planning (Legacy): " . $tool->schedule_planning . "\n";
    foreach ($tool->schedules as $sch) {
        echo "  Schedule ID: " . $sch->id . " | Date: " . $sch->schedule_date . " | PR: " . $sch->pr_number . "\n";
    }
    echo "---------------------------------\n";
}
