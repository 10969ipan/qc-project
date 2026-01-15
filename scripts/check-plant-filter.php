<?php
// Script to verify and fix plant filtering in all controllers

$controllers = [
    'ChecksheetController.php',
    'InProcessChecksheetController.php',
    'CrossCutChecksheetController.php',
    'SortirChecksheetController.php'
];

$basePath = __DIR__ . '/../app/Http/Controllers/';

echo "=== Checking Plant Filter Implementation ===\n\n";

foreach ($controllers as $filename) {
    $filepath = $basePath . $filename;

    if (!file_exists($filepath)) {
        echo "✗ $filename - File not found\n";
        continue;
    }

    $content = file_get_contents($filepath);

    // Check if plant filter exists
    if (strpos($content, "Filter by plant parameter if provided") !== false) {
        echo "✓ $filename - Plant filter found\n";

        // Check the implementation
        if (strpos($content, "->withoutGlobalScope('plant')->where('plant'") !== false) {
            echo "  → Using withoutGlobalScope approach\n";
        } elseif (strpos($content, "->where('plant'") !== false) {
            echo "  → Using direct where approach\n";
        }
    } else {
        echo "✗ $filename - Plant filter NOT found\n";
    }
}

echo "\n=== Analysis ===\n";
echo "The issue: Global scope doesn't filter for multi-plant roles,\n";
echo "so withoutGlobalScope() has no effect for them.\n\n";
echo "Solution: The where('plant', ...) clause should work,\n";
echo "but we need to ensure it's applied BEFORE other filters.\n\n";
echo "Recommendation: Check if pagination is preserving the plant parameter.\n";
