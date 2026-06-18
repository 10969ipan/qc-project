<?php
$files = [
    'app/Http/Controllers/InProcessChecksheetController.php',
    'app/Http/Controllers/PlatingChecksheetController.php',
    'app/Http/Controllers/PaintingChecksheetController.php'
];

foreach ($files as $file) {
    $content = file_get_contents($file);
    // Replace array definition exactly
    $oldStr = "\$preservationKeys = ['page', 'plant', 'start_date', 'end_date', 'approval_status', 'search', 'shift'];";
    $newStr = "\$preservationKeys = ['page', 'plant', 'start_date', 'end_date', 'approval_status', 'search', 'shift', 'view_mode'];";
    
    $newContent = str_replace($oldStr, $newStr, $content);
        
    if ($newContent !== $content) {
        file_put_contents($file, $newContent);
        echo 'Updated ' . $file . PHP_EOL;
    } else {
        echo 'No match in ' . $file . PHP_EOL;
    }
}
