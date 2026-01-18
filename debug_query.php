<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING byCategory METHOD ===\n";

// Test 1: byCategory with string
$query1 = \App\Models\Item::withoutGlobalScope('plant')->byCategory('Sub Assy');
echo "Query 1 SQL: " . $query1->toSql() . "\n";
echo "Query 1 Bindings: " . json_encode($query1->getBindings()) . "\n";
$items1 = $query1->get();
echo "Query 1 Results: " . $items1->count() . "\n\n";

// Test 2: byCategory with string + where plant
$query2 = \App\Models\Item::withoutGlobalScope('plant')
    ->byCategory('Sub Assy')
    ->where('plant', 'jakarta');
echo "Query 2 SQL: " . $query2->toSql() . "\n";
echo "Query 2 Bindings: " . json_encode($query2->getBindings()) . "\n";
$items2 = $query2->get();
echo "Query 2 Results: " . $items2->count() . "\n";
foreach ($items2 as $i) {
    echo "  - " . $i->name . "\n";
}
