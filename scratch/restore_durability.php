<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$sqlFile = 'D:\laragon\backup\mysql\mysql-8.4-2026-08-13_161448.sql';
if (!file_exists($sqlFile)) {
    die("File backup tidak ditemukan: $sqlFile\n");
}

echo "Membaca file backup SQL...\n";
$lines = file($sqlFile);

$inTargetTable = false;
$sqlStatements = [];
$currentSql = '';

DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

// Read lines and look for INSERT INTO `standard_performance_tests` and `durability_thickness_reports`
foreach ($lines as $line) {
    if (str_contains($line, 'INSERT INTO `standard_performance_tests`') || str_contains($line, 'INSERT INTO `durability_thickness_reports`')) {
        try {
            DB::statement($line);
            echo "Successfully executed INSERT statement!\n";
        } catch (\Exception $e) {
            echo "Error executing statement: " . $e->getMessage() . "\n";
        }
    }
}

// Re-enable foreign key checks
DB::statement('SET FOREIGN_KEY_CHECKS = 1;');

$stdCount = DB::table('standard_performance_tests')->count();
$reportCount = DB::table('durability_thickness_reports')->count();

echo "SELESAI! Data berhasil direstore:\n";
echo "- standard_performance_tests: $stdCount rows\n";
echo "- durability_thickness_reports: $reportCount rows\n";
