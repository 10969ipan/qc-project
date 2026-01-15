<?php
// Script to add plant filtering to AnalysisController

$file = __DIR__ . '/../app/Http/Controllers/AnalysisController.php';

if (!file_exists($file)) {
    die("File not found: $file\n");
}

$content = file_get_contents($file);

// Check if already added
if (strpos($content, 'Filter by plant parameter if provided') !== false) {
    echo "Already updated!\n";
    exit(0);
}

// Find the line and add plant filter
$search = "        \$query = \$modelClass::with('item')->orderBy(\$dateColumn);\n\n        // Selection optimization";
$replace = "        \$query = \$modelClass::with('item')->orderBy(\$dateColumn);\n\n        // Filter by plant parameter if provided\n        if (\$request->has('plant')) {\n            \$query->withoutGlobalScope('plant')->where('plant', \$request->get('plant'));\n        }\n\n        // Selection optimization";

$newContent = str_replace($search, $replace, $content);

if ($newContent === $content) {
    echo "Pattern not found. Manual update required.\n";
    echo "Please add this code after line 47 in AnalysisController.php:\n\n";
    echo "        // Filter by plant parameter if provided\n";
    echo "        if (\$request->has('plant')) {\n";
    echo "            \$query->withoutGlobalScope('plant')->where('plant', \$request->get('plant'));\n";
    echo "        }\n";
    exit(1);
}

// Backup
copy($file, $file . '.backup_' . date('YmdHis'));

// Write
if (file_put_contents($file, $newContent)) {
    echo "✓ Successfully updated AnalysisController.php\n";
    echo "✓ Backup created\n";
    echo "Plant filtering added to Report pages!\n";
} else {
    echo "✗ Failed to write file\n";
    exit(1);
}
