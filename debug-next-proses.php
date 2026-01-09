<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

echo "<h2>Debug Next Proses Feature</h2>";
echo "<hr>";

// 1. Check Database Columns
echo "<h3>1. Database Columns Check</h3>";
try {
    $hasChecksheets = Schema::hasColumn('checksheets', 'next_proses');
    $hasInProcess = Schema::hasColumn('in_process_checksheets', 'next_proses');
    $hasCrossCut = Schema::hasColumn('cross_cut_checksheets', 'next_proses');

    echo "✅ checksheets.next_proses: " . ($hasChecksheets ? 'EXISTS' : 'MISSING') . "<br>";
    echo "✅ in_process_checksheets.next_proses: " . ($hasInProcess ? 'EXISTS' : 'MISSING') . "<br>";
    echo "✅ cross_cut_checksheets.next_proses: " . ($hasCrossCut ? 'EXISTS' : 'MISSING') . "<br>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// 2. Check Model Fillable
echo "<h3>2. Model Fillable Check</h3>";
$checksheet = new App\Models\Checksheet();
$inProcess = new App\Models\InProcessChecksheet();
$crossCut = new App\Models\CrossCutChecksheet();

echo "Checksheet fillable has next_proses: " . (in_array('next_proses', $checksheet->getFillable()) ? '✅ YES' : '❌ NO') . "<br>";
echo "InProcessChecksheet fillable has next_proses: " . (in_array('next_proses', $inProcess->getFillable()) ? '✅ YES' : '❌ NO') . "<br>";
echo "CrossCutChecksheet fillable has next_proses: " . (in_array('next_proses', $crossCut->getFillable()) ? '✅ YES' : '❌ NO') . "<br>";

// 3. Check View Files
echo "<h3>3. View Files Check</h3>";
$subAssyCreate = file_get_contents(__DIR__ . '/resources/views/sub_assy/create.blade.php');
$hasDropdown = strpos($subAssyCreate, 'nextProsesContainer') !== false;
$hasJS = strpos($subAssyCreate, 'toggleNextProsesDropdown') !== false;

echo "Sub Assy create.blade.php has dropdown: " . ($hasDropdown ? '✅ YES' : '❌ NO') . "<br>";
echo "Sub Assy create.blade.php has JavaScript: " . ($hasJS ? '✅ YES' : '❌ NO') . "<br>";

// 4. Check if dropdown HTML exists
if ($hasDropdown) {
    echo "<h3>4. Dropdown HTML Preview</h3>";
    preg_match('/<div class="form-group mb-2" id="nextProsesContainer".*?<\/div>/s', $subAssyCreate, $matches);
    if (!empty($matches)) {
        echo "<pre>" . htmlspecialchars($matches[0]) . "</pre>";
    }
}

// 5. Git Status
echo "<h3>5. Last Git Commit</h3>";
$gitLog = shell_exec('git log -1 --oneline 2>&1');
echo "<pre>" . htmlspecialchars($gitLog) . "</pre>";

echo "<hr>";
echo "<p><strong>Setelah cek debug ini, hapus file debug-next-proses.php untuk keamanan!</strong></p>";
