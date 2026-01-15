<?php

/**
 * Script to add plant filtering to all checksheet controllers
 * Run this file once to update all controllers
 */

$controllers = [
    'ChecksheetController.php' => [
        'line' => 14,
        'search' => '        $query = Checksheet::with(\'item\')->orderBy(\'date\', \'desc\')->orderBy(\'created_at\', \'desc\');',
        'replace' => '        $query = Checksheet::with(\'item\')->orderBy(\'date\', \'desc\')->orderBy(\'created_at\', \'desc\');

        // Filter by plant parameter if provided
        if ($request->has(\'plant\')) {
            $query->withoutGlobalScope(\'plant\')->where(\'plant\', $request->get(\'plant\'));
        }'
    ],
    'InProcessChecksheetController.php' => [
        'line' => 43,
        'search' => '        $query = InProcessChecksheet::with(\'item\')->orderBy(\'date\', \'desc\')->orderBy(\'created_at\', \'desc\');',
        'replace' => '        $query = InProcessChecksheet::with(\'item\')->orderBy(\'date\', \'desc\')->orderBy(\'created_at\', \'desc\');

        // Filter by plant parameter if provided
        if ($request->has(\'plant\')) {
            $query->withoutGlobalScope(\'plant\')->where(\'plant\', $request->get(\'plant\'));
        }'
    ],
    'CrossCutChecksheetController.php' => [
        'line' => 18,
        'search' => '        $query = CrossCutChecksheet::with(\'item\')->orderBy(\'date\', \'desc\')->orderBy(\'created_at\', \'desc\');',
        'replace' => '        $query = CrossCutChecksheet::with(\'item\')->orderBy(\'date\', \'desc\')->orderBy(\'created_at\', \'desc\');

        // Filter by plant parameter if provided
        if ($request->has(\'plant\')) {
            $query->withoutGlobalScope(\'plant\')->where(\'plant\', $request->get(\'plant\'));
        }'
    ],
    'SortirChecksheetController.php' => [
        'line' => 16,
        'search' => '        $query = SortirChecksheet::with(\'item\')->orderBy(\'date\', \'desc\')->orderBy(\'created_at\', \'desc\');',
        'replace' => '        $query = SortirChecksheet::with(\'item\')->orderBy(\'date\', \'desc\')->orderBy(\'created_at\', \'desc\');

        // Filter by plant parameter if provided
        if ($request->has(\'plant\')) {
            $query->withoutGlobalScope(\'plant\')->where(\'plant\', $request->get(\'plant\'));
        }'
    ]
];

$basePath = __DIR__ . '/../app/Http/Controllers/';
$updated = [];
$errors = [];

foreach ($controllers as $filename => $config) {
    $filepath = $basePath . $filename;

    if (!file_exists($filepath)) {
        $errors[] = "File not found: $filename";
        continue;
    }

    $content = file_get_contents($filepath);

    // Check if already updated
    if (strpos($content, 'Filter by plant parameter if provided') !== false) {
        $updated[] = "$filename - Already updated, skipping";
        continue;
    }

    // Replace the content
    $newContent = str_replace($config['search'], $config['replace'], $content);

    if ($newContent === $content) {
        $errors[] = "$filename - Pattern not found, manual update required";
        continue;
    }

    // Backup original file
    $backupPath = $filepath . '.backup_' . date('YmdHis');
    copy($filepath, $backupPath);

    // Write updated content
    if (file_put_contents($filepath, $newContent)) {
        $updated[] = "$filename - Successfully updated (backup: " . basename($backupPath) . ")";
    } else {
        $errors[] = "$filename - Failed to write file";
    }
}

echo "=== Plant Filter Update Script ===\n\n";

if (!empty($updated)) {
    echo "✓ Updated files:\n";
    foreach ($updated as $msg) {
        echo "  - $msg\n";
    }
    echo "\n";
}

if (!empty($errors)) {
    echo "✗ Errors:\n";
    foreach ($errors as $msg) {
        echo "  - $msg\n";
    }
    echo "\n";
}

echo "Done!\n";
