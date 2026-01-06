<?php

namespace App\Console\Commands;

use App\Models\Item;
use Illuminate\Console\Command;

class FixMissingPdfPaths extends Command
{
    protected $signature = 'items:fix-paths';
    protected $description = 'Fix missing PDF paths by checking actual file existence';

    public function handle()
    {
        $this->info('Checking for items with missing PDF files...');
        $this->newLine();

        $items = Item::whereNotNull('file_path')->get();
        $fixed = 0;
        $missing = 0;

        foreach ($items as $item) {
            $fullPath = public_path($item->file_path);

            // Check if file exists
            if (!file_exists($fullPath)) {
                $this->warn("Missing: {$item->name}");
                $this->line("  Expected: {$item->file_path}");

                // Try to find the file by name without timestamp
                $filename = basename($item->file_path);
                $folderPath = dirname(public_path($item->file_path));

                // Extract the original filename (without timestamp prefix)
                if (preg_match('/^\d+_(.+)$/', $filename, $matches)) {
                    $originalName = $matches[1];
                    $possiblePath = $folderPath . '/' . $originalName;

                    if (file_exists($possiblePath)) {
                        // Rename file to match database
                        rename($possiblePath, $fullPath);
                        $this->info("  Fixed: Renamed '{$originalName}' to '{$filename}'");
                        $fixed++;
                        continue;
                    }
                }

                // Try to find anywhere in master item folder
                $searchPattern = str_replace('_', ' ', pathinfo($filename, PATHINFO_FILENAME));
                $searchPattern = preg_replace('/^\d+\s*/', '', $searchPattern); // Remove timestamp

                $found = false;
                foreach (['ahm', 'yimm', 'others'] as $folder) {
                    $searchPath = public_path("master item/{$folder}");
                    if (is_dir($searchPath)) {
                        $files = scandir($searchPath);
                        foreach ($files as $file) {
                            if ($file === '.' || $file === '..')
                                continue;

                            $fileWithoutExt = pathinfo($file, PATHINFO_FILENAME);
                            if (
                                stripos($fileWithoutExt, $searchPattern) !== false ||
                                stripos($searchPattern, $fileWithoutExt) !== false
                            ) {

                                $actualPath = $searchPath . '/' . $file;
                                $this->line("  Found similar: master item/{$folder}/{$file}");

                                $choice = $this->choice(
                                    'Action?',
                                    ['rename-file' => 'Rename file to match DB', 'update-db' => 'Update DB to match file', 'skip' => 'Skip'],
                                    'update-db'
                                );

                                if ($choice === 'rename-file') {
                                    rename($actualPath, $fullPath);
                                    $this->info("  Fixed: File renamed");
                                    $fixed++;
                                } elseif ($choice === 'update-db') {
                                    $item->file_path = "master item/{$folder}/{$file}";
                                    $item->save();
                                    $this->info("  Fixed: Database updated");
                                    $fixed++;
                                }
                                $found = true;
                                break 2;
                            }
                        }
                    }
                }

                if (!$found) {
                    $this->error("  Not found anywhere!");
                    $missing++;
                }
            } else {
                $this->line("OK: {$item->name}");
            }
        }

        $this->newLine();
        $this->info("Fixed: {$fixed} | Missing: {$missing}");
        return 0;
    }
}
