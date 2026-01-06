<?php

namespace App\Console\Commands;

use App\Models\Item;
use Illuminate\Console\Command;

class MigrateItemFilePaths extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'items:migrate-paths';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate item file paths from items_files to master item folder structure';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting migration of item file paths...');
        $this->newLine();

        $items = Item::whereNotNull('file_path')->get();

        if ($items->isEmpty()) {
            $this->warn('No items found with file paths.');
            return 0;
        }

        $updated = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($items as $item) {
            // Skip if already migrated to master item structure
            if (str_starts_with($item->file_path, 'master item/')) {
                $this->line("  [SKIP] {$item->name} - already using new structure");
                $skipped++;
                continue;
            }

            // Only migrate from items_files
            if (!str_starts_with($item->file_path, 'items_files/')) {
                $this->warn("  [SKIP] {$item->name} - unknown path structure: {$item->file_path}");
                $skipped++;
                continue;
            }

            $oldPath = public_path($item->file_path);
            $filename = basename($item->file_path);

            // Determine customer folder
            $customerFolder = $this->getCustomerFolder($item->customer);
            $newFolderPath = public_path('master item/' . $customerFolder);
            $newFilePath = $newFolderPath . '/' . $filename;

            // Create folder if not exists
            if (!file_exists($newFolderPath)) {
                mkdir($newFolderPath, 0755, true);
            }

            // Check if old file exists
            if (file_exists($oldPath)) {
                // Move file to new location
                try {
                    rename($oldPath, $newFilePath);
                    $item->file_path = 'master item/' . $customerFolder . '/' . $filename;
                    $item->save();

                    $this->info("  [MOVED] {$item->name} -> master item/{$customerFolder}/");
                    $updated++;
                } catch (\Exception $e) {
                    $this->error("  [ERROR] {$item->name} - {$e->getMessage()}");
                    $errors++;
                }
            } else {
                // File doesn't exist, but update database path anyway
                $item->file_path = 'master item/' . $customerFolder . '/' . $filename;
                $item->save();

                $this->warn("  [UPDATED] {$item->name} - file not found, but DB path updated");
                $updated++;
            }
        }

        $this->newLine();
        $this->info('Migration completed!');
        $this->table(
            ['Status', 'Count'],
            [
                ['Updated/Moved', $updated],
                ['Skipped', $skipped],
                ['Errors', $errors],
            ]
        );

        return 0;
    }

    /**
     * Determine the customer folder based on customer name
     */
    private function getCustomerFolder($customer)
    {
        if (!$customer) {
            return 'others';
        }

        $customer = strtolower(trim($customer));

        if (strpos($customer, 'astra honda') !== false || strpos($customer, 'ahm') !== false) {
            return 'ahm';
        } elseif (strpos($customer, 'yamaha') !== false || strpos($customer, 'yimm') !== false) {
            return 'yimm';
        }

        return 'others';
    }
}
