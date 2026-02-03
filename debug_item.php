<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$item = App\Models\Item::where('name', 'like', '%CLAMP%')->first();
if ($item) {
    file_put_contents('debug_output.txt', json_encode($item->dimension_standards));
}
