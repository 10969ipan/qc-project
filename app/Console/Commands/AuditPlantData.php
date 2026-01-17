<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AuditPlantData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:audit-plant-data {--fix : Arround to fix the data automatically}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fix = $this->option('fix');
        $this->info('Auditing Items created today...');
        $items = \App\Models\Item::withoutGlobalScope('plant')
            ->where('created_at', '>=', now()->startOfDay())
            ->get();

        foreach ($items as $item) {
            $this->line("Item: ID {$item->id} | Name: {$item->name} | Plant: {$item->plant}");

            // Heuristic: If item was added today and plant is Karawang, it might be the one the Admin added for Jakarta
            if ($item->plant === 'karawang') {
                if ($fix) {
                    $item->plant = 'jakarta';
                    $item->save();
                    $this->info("FIXED: Moved item {$item->id} to Jakarta.");
                } else {
                    $this->warn("POTENTIAL ISSUE: Item {$item->id} ({$item->name}) is Karawang but might should be Jakarta.");
                }
            }
        }

        $this->info("\nAuditing Sub Assy Checksheets created today...");
        $checksheets = \App\Models\SubAssyChecksheet::withoutGlobalScope('plant')
            ->where('created_at', '>=', now()->startOfDay())
            ->get();

        foreach ($checksheets as $c) {
            $itemName = $c->item->name ?? 'N/A';
            $this->line("Checksheet: ID {$c->id} | Item: {$itemName} | Plant: {$c->plant}");
            if ($c->plant === 'karawang') {
                // If it was created for an item that is now in Jakarta, it should probably be in Jakarta too
                $itemPlant = $c->item->plant ?? null;
                if ($itemPlant === 'jakarta') {
                    if ($fix) {
                        $c->plant = 'jakarta';
                        $c->save();
                        $this->info("FIXED: Moved checksheet {$c->id} to Jakarta.");
                    } else {
                        $this->warn("POTENTIAL ISSUE: Checksheet {$c->id} is Karawang but item {$c->item_id} is Jakarta.");
                    }
                }
            }
        }

        if (!$fix) {
            $this->info("\nRun with --fix to apply changes.");
        }

        $this->info("\nAudit complete.");
    }
}
