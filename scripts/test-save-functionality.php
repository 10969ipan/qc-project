<?php

/**
 * Script Test Save Functionality
 * Tujuan: Test apakah fungsi save verification berfungsi dengan benar
 * 
 * Cara pakai:
 * php scripts/test-save-functionality.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║        Test Save Functionality - QC Project                ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Test 1: Database Connection
echo "→ Test 1: Database Connection\n";
try {
    DB::connection()->getPdo();
    echo "  ✓ Database connection OK\n";
    echo "  Database: " . DB::connection()->getDatabaseName() . "\n";
} catch (\Exception $e) {
    echo "  ✗ Database connection FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Check calibration_verifications table
echo "\n→ Test 2: Check calibration_verifications table\n";
try {
    $tableExists = DB::select("SHOW TABLES LIKE 'calibration_verifications'");
    if (count($tableExists) > 0) {
        echo "  ✓ Table calibration_verifications exists\n";

        // Check columns
        $columns = DB::select("DESCRIBE calibration_verifications");
        $arrayColumns = ['nilai_alat', 'nilai_koreksi', 'nilai_ketidakpastian', 'hasil_verifikasi'];

        echo "  Checking array columns:\n";
        foreach ($columns as $col) {
            if (in_array($col->Field, $arrayColumns)) {
                echo "    - {$col->Field}: {$col->Type}\n";
            }
        }
    } else {
        echo "  ✗ Table calibration_verifications NOT FOUND\n";
    }
} catch (\Exception $e) {
    echo "  ✗ Error checking table: " . $e->getMessage() . "\n";
}

// Test 3: Test JSON encoding/decoding
echo "\n→ Test 3: Test JSON encoding/decoding\n";
$testData = [
    'nilai_alat' => ['10.5', '20.3', '30.1'],
    'nilai_koreksi' => ['0.1', '0.2', '0.15'],
    'nilai_ketidakpastian' => ['±0.05', '±0.08', '±0.06'],
    'hasil_verifikasi' => ['OK', 'OK', 'NG']
];

foreach ($testData as $field => $values) {
    $encoded = json_encode($values);
    $decoded = json_decode($encoded, true);

    if ($decoded === $values) {
        echo "  ✓ {$field}: JSON encode/decode OK\n";
    } else {
        echo "  ✗ {$field}: JSON encode/decode FAILED\n";
    }
}

// Test 4: Test actual save (dry run)
echo "\n→ Test 4: Test save simulation (dry run)\n";
try {
    DB::beginTransaction();

    // Simulate verification data
    $testVerification = [
        'plant_id' => 1,
        'tool_id' => 1,
        'name_alat' => 'Test Alat',
        'merk' => 'Test Merk',
        'serial_number' => 'TEST-001',
        'rentang_ukur' => '0-100mm',
        'resolusi' => '0.01mm',
        'frekuensi_kalibrasi' => '6 bulan',
        'lokasi_penyimpanan' => 'QC Room',
        'tanggal_kalibrasi' => now()->subMonths(1),
        'tanggal_verifikasi' => now(),
        'next_kalibrasi' => now()->addMonths(5),
        'nilai_alat' => json_encode(['10.5', '20.3', '30.1']),
        'nilai_koreksi' => json_encode(['0.1', '0.2', '0.15']),
        'nilai_ketidakpastian' => json_encode(['±0.05', '±0.08', '±0.06']),
        'hasil_verifikasi' => json_encode(['OK', 'OK', 'NG']),
        'judgment' => 'OK',
        'std_toleransi' => '±0.1mm',
        'acuan_toleransi' => 'ISO 9001',
        'created_at' => now(),
        'updated_at' => now(),
    ];

    // Try to insert (will be rolled back)
    $id = DB::table('calibration_verifications')->insertGetId($testVerification);

    echo "  ✓ Test insert successful (ID: {$id})\n";
    echo "  ✓ Array data can be saved as JSON\n";

    // Verify the data can be read back
    $saved = DB::table('calibration_verifications')->find($id);

    if ($saved) {
        echo "  ✓ Data can be read back from database\n";

        // Check if JSON fields can be decoded
        $nilaiAlat = json_decode($saved->nilai_alat, true);
        if (is_array($nilaiAlat)) {
            echo "  ✓ nilai_alat can be decoded as array\n";
        } else {
            echo "  ✗ nilai_alat decode FAILED\n";
        }
    }

    // Rollback - we don't want to keep test data
    DB::rollBack();
    echo "  ✓ Test data rolled back (not saved permanently)\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "  ✗ Save test FAILED: " . $e->getMessage() . "\n";
    echo "  Stack trace:\n";
    echo "  " . $e->getTraceAsString() . "\n";
}

// Test 5: Check IncomingSubPartService
echo "\n→ Test 5: Check IncomingSubPartService class\n";
try {
    $serviceClass = 'App\\Services\\IncomingSubPartService';
    if (class_exists($serviceClass)) {
        echo "  ✓ IncomingSubPartService class exists\n";

        $reflection = new ReflectionClass($serviceClass);
        $methods = ['createChecksheet', 'updateChecksheet', 'processDefects'];

        foreach ($methods as $method) {
            if ($reflection->hasMethod($method)) {
                echo "  ✓ Method {$method} exists\n";
            } else {
                echo "  ✗ Method {$method} NOT FOUND\n";
            }
        }
    } else {
        echo "  ✗ IncomingSubPartService class NOT FOUND\n";
    }
} catch (\Exception $e) {
    echo "  ✗ Error checking service: " . $e->getMessage() . "\n";
}

// Summary
echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║                  TEST SELESAI                              ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\nJika semua test menunjukkan ✓, maka fungsi save seharusnya berfungsi.\n";
echo "Jika ada ✗, silakan periksa error message di atas.\n\n";
