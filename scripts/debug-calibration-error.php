<?php

use App\Models\CalibrationVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$id = CalibrationVerification::first()->id ?? null;
if (!$id) {
    echo "No verification found to test.\n";
    exit;
}

echo "Testing update for verification ID: $id\n";

$request = new Request([
    'tool_id' => CalibrationVerification::find($id)->tool_id,
    'name_alat' => 'Test Tool',
    'merk' => 'Test Merk',
    'serial_number' => '123456',
    'rentang_ukur' => '0-100',
    'resolusi' => '0.01',
    'frekuensi_kalibrasi' => '1 Year',
    'tanggal_kalibrasi' => '2025-01-01',
    'tanggal_verifikasi' => '2025-01-02',
    'next_kalibrasi' => '2026-01-01',
    'judgment' => 'OK',
    'std_toleransi' => '10',
    'acuan_toleransi' => 'ASTM',
    'plant' => 'Jakarta',
]);

try {
    echo "Running validation...\n";
    $request->validate([
        'tool_id' => 'required|exists:calibration_tools,id',
        'name_alat' => 'required|string',
        'merk' => 'required|string',
        'serial_number' => 'required|string',
        'rentang_ukur' => 'required|string',
        'resolusi' => 'required|string',
        'frekuensi_kalibrasi' => 'required|string',
        'tanggal_kalibrasi' => 'required|date',
        'tanggal_verifikasi' => 'required|date',
        'next_kalibrasi' => 'required|date',
        'judgment' => 'required|string',
        'std_toleransi' => 'required|string',
        'acuan_toleransi' => 'required|string',
        'plant' => 'required|string',
    ]);
    echo "Validation passed.\n";

    echo "Attempting to update model...\n";
    $verification = CalibrationVerification::find($id);
    $verification->update($request->except(['plant', '_token', '_method']));
    echo "Update successful.\n";

} catch (ValidationException $e) {
    echo "Validation failed: " . json_encode($e->errors(), JSON_PRETTY_PRINT) . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
