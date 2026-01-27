<?php

$sourceFile = 'FIX Checksheet YPQD Januari 2026 PT INP.xlsx';
$targetFile = 'FIX Checksheet YPQD Januari 2026 PT INP_UNLOCKED.xlsx';

if (!file_exists($sourceFile)) {
    die("Source file not found: $sourceFile\n");
}

copy($sourceFile, $targetFile);

$zip = new ZipArchive;
if ($zip->open($targetFile) === TRUE) {
    // 1. Remove Sheet Protection
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $filename = $zip->getNameIndex($i);
        if (preg_match('/xl\/worksheets\/sheet\d+\.xml/', $filename)) {
            $content = $zip->getFromName($filename);
            // Replace <sheetProtection ... /> tag
            $newContent = preg_replace('/<sheetProtection[^>]*\/>/', '', $content);
            if ($newContent !== $content) {
                $zip->addFromString($filename, $newContent);
                echo "Unprotected sheet: $filename\n";
            }
        }
    }

    // 2. Remove Workbook Protection
    $workbookFile = 'xl/workbook.xml';
    $workbookContent = $zip->getFromName($workbookFile);
    if ($workbookContent) {
        $newWorkbookContent = preg_replace('/<workbookProtection[^>]*\/>/', '', $workbookContent);
        if ($newWorkbookContent !== $workbookContent) {
            $zip->addFromString($workbookFile, $newWorkbookContent);
            echo "Unprotected workbook.\n";
        }
    }

    $zip->close();
    echo "Successfully created unlocked file: $targetFile\n";
} else {
    die("Failed to open $targetFile as ZIP archive.\n");
}
