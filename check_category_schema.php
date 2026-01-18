<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$sql = Illuminate\Support\Facades\DB::select('SHOW CREATE TABLE categories')[0]->{'Create Table'};
file_put_contents('schema_dump.txt', $sql);
