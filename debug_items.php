<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CATEGORIES ===\n";
foreach (\App\Models\Category::all() as $c) {
    echo $c->name . " (ID: " . $c->id . ")\n";
}

echo "\n=== JAKARTA ITEMS ===\n";
$items = \App\Models\Item::withoutGlobalScope('plant')->where('plant', 'jakarta')->get();
echo "Total: " . $items->count() . "\n";
foreach ($items as $i) {
    echo $i->name . " - Category ID: " . $i->category_id . " - Category Name: " . ($i->category ? $i->category->name : 'NULL') . "\n";
}

echo "\n=== SUB ASSY CATEGORY ===\n";
$subAssyCategory = \App\Models\Category::where('name', 'Sub Assy')->first();
if ($subAssyCategory) {
    echo "Found: ID " . $subAssyCategory->id . "\n";
} else {
    echo "NOT FOUND!\n";
}

echo "\n=== JAKARTA ITEMS WITH SUB ASSY CATEGORY ===\n";
if ($subAssyCategory) {
    $subAssyItems = \App\Models\Item::withoutGlobalScope('plant')
        ->where('plant', 'jakarta')
        ->where('category_id', $subAssyCategory->id)
        ->get();
    echo "Total: " . $subAssyItems->count() . "\n";
    foreach ($subAssyItems as $i) {
        echo "  - " . $i->name . "\n";
    }
}
