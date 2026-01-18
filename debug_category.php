<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING CATEGORY NAME ===\n";
$cat = \App\Models\Category::where('name', 'Sub Assy')->first();
echo "Category 'Sub Assy' found: " . ($cat ? 'YES (ID: ' . $cat->id . ')' : 'NO') . "\n\n";

echo "All categories:\n";
foreach (\App\Models\Category::all() as $c) {
    echo "  [" . $c->id . "] '" . $c->name . "' (length: " . strlen($c->name) . ", bytes: " . json_encode($c->name) . ")\n";
}

echo "\n=== TESTING byCategory SCOPE ===\n";
$categoryId = \App\Models\Category::where('name', 'Sub Assy')->value('id');
echo "Category ID from query: " . ($categoryId ?? 'NULL') . "\n";

// Test the scope directly
$query = \App\Models\Item::withoutGlobalScope('plant')->byCategory('Sub Assy');
echo "Query SQL: " . $query->toSql() . "\n";
echo "Query Bindings: " . json_encode($query->getBindings()) . "\n";
