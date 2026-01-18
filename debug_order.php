<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING DIFFERENT QUERY ORDERS ===\n\n";

// Test 1: Current controller order
echo "Test 1: withoutGlobalScope -> byCategory -> where -> orderBy\n";
$query1 = \App\Models\Item::withoutGlobalScope('plant')
    ->byCategory('Sub Assy')
    ->where('plant', 'jakarta')
    ->orderBy('name');
echo "SQL: " . $query1->toSql() . "\n";
echo "Results: " . $query1->count() . "\n\n";

// Test 2: Different order
echo "Test 2: byCategory -> withoutGlobalScope -> where -> orderBy\n";
$query2 = \App\Models\Item::byCategory('Sub Assy')
    ->withoutGlobalScope('plant')
    ->where('plant', 'jakarta')
    ->orderBy('name');
echo "SQL: " . $query2->toSql() . "\n";
echo "Results: " . $query2->count() . "\n\n";

// Test 3: Check if global scope is being applied
echo "Test 3: Just byCategory (with global scope)\n";
$query3 = \App\Models\Item::byCategory('Sub Assy');
echo "SQL: " . $query3->toSql() . "\n";
echo "Bindings: " . json_encode($query3->getBindings()) . "\n";
echo "Results: " . $query3->count() . "\n";
