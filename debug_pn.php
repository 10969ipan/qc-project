<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$item = App\Models\Item::where('name', 'like', '%CLAMP%')->first();
if ($item) {
    $pn = $item->part_number;
    echo "PN: [" . $pn . "]\n";
    echo "Hex: " . bin2hex($pn) . "\n";
}
