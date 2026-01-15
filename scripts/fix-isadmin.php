<?php
// Quick fix script for undefined $isAdmin variable in sortir/index.blade.php

$file = __DIR__ . '/../resources/views/sortir/index.blade.php';

if (!file_exists($file)) {
    die("File not found: $file\n");
}

$content = file_get_contents($file);

// Find the line with @php and $canReject = false;
// and add $isAdmin definition before it
$search = "                                @php\r\n                                    \$canReject = false;";
$replace = "                                @php\r\n                                    \$isAdmin = auth()->user()->role === 'admin';\r\n                                    \$canReject = false;";

if (strpos($content, '$isAdmin = auth()->user()->role') !== false) {
    echo "Already fixed!\n";
    exit(0);
}

$newContent = str_replace($search, $replace, $content);

if ($newContent === $content) {
    echo "Pattern not found. Trying alternative pattern...\n";

    // Try without \r
    $search = "                                @php\n                                    \$canReject = false;";
    $replace = "                                @php\n                                    \$isAdmin = auth()->user()->role === 'admin';\n                                    \$canReject = false;";
    $newContent = str_replace($search, $replace, $content);
}

if ($newContent === $content) {
    echo "Could not find pattern to replace.\n";
    echo "Please manually add this line after line 303 (@php):\n";
    echo "    \$isAdmin = auth()->user()->role === 'admin';\n";
    exit(1);
}

// Backup
copy($file, $file . '.backup_' . date('YmdHis'));

// Write
if (file_put_contents($file, $newContent)) {
    echo "✓ Successfully fixed $file\n";
    echo "✓ Backup created\n";
    echo "Please refresh your browser!\n";
} else {
    echo "✗ Failed to write file\n";
    exit(1);
}
