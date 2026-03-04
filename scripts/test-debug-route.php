<?php

use App\Models\CalibrationVerification;
use Illuminate\Support\Facades\Http;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$id = CalibrationVerification::first()->id ?? null;
if (!$id) {
    echo "No verification found to test.\n";
    exit;
}

echo "Testing DEBUG route for verification ID: $id\n";

// We use the application's internal request handling since we can't easily use external curl without a running server
$request = \Illuminate\Http\Request::create(
    "/calibration/verifications/$id",
    'PUT',
    [
        'tool_id' => CalibrationVerification::find($id)->tool_id,
        'name_alat' => 'Debug Tool',
        'plant' => 'jakarta',
        // 'lokasi_penyimpanan' => 'SHOULD NOT BE REQUIRED',
    ]
);

// Manually resolve the route
try {
    $response = app()->handle($request);
    echo "Response Status: " . $response->getStatusCode() . "\n";
    echo "Response Content: " . $response->getContent() . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    if (method_exists($e, 'errors')) {
        echo "Validation Errors: " . json_encode($e->errors(), JSON_PRETTY_PRINT) . "\n";
    }
}
