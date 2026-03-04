<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tables = DB::select('SHOW TABLES');
foreach ($tables as $table) {
    $name = array_values((array) $table)[0];
    if (Schema::hasColumn($name, 'lokasi_penyimpanan')) {
        echo "$name\n";
    }
}
