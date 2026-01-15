<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MigrateKarawangData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-karawang-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate legacy Karawang data from temporary database to main database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting migration for plant: karawang');

        // 1. Categories
        $this->info('Migrating Categories...');
        $oldCategories = \DB::connection('mysql_legacy')->table('categories')->get();
        $categoryMapping = []; // old_id => new_id

        foreach ($oldCategories as $oldCat) {
            $newCat = \App\Models\Category::updateOrCreate(
                ['name' => $oldCat->name, 'plant' => 'karawang'],
                ['created_at' => $oldCat->created_at, 'updated_at' => $oldCat->updated_at]
            );
            $categoryMapping[$oldCat->id] = $newCat->id;
        }

        // 2. Items
        $this->info('Migrating Items...');
        $oldItems = \DB::connection('mysql_legacy')->table('items')->get();
        $itemMapping = []; // old_id => new_id

        foreach ($oldItems as $oldItem) {
            // Determine match criteria for Karawang plant
            $criteria = ['plant' => 'karawang'];
            if ($oldItem->sap_code) {
                $criteria['sap_code'] = $oldItem->sap_code;
            } else {
                $criteria['part_number'] = $oldItem->part_number ?? $oldItem->name;
            }

            $newItem = \App\Models\Item::updateOrCreate(
                $criteria,
                [
                    'name' => $oldItem->name,
                    'category_id' => $categoryMapping[$oldItem->category_id] ?? null,
                    'file_path' => $oldItem->file_path,
                    'customer' => $oldItem->customer,
                    'part_number' => $oldItem->part_number,
                    'dimension_standards' => json_decode($oldItem->dimension_standards, true),
                    'defects' => json_decode($oldItem->defects, true),
                    'created_at' => $oldItem->created_at,
                    'updated_at' => $oldItem->updated_at,
                ]
            );
            $itemMapping[$oldItem->id] = $newItem->id;
        }

        // CLEANUP: Clean existing Karawang checksheets before import to avoid duplicates if re-running
        $this->info('Cleaning existing Karawang checksheets to prevent duplicates...');
        \App\Models\Checksheet::where('plant', 'karawang')->delete();
        \App\Models\InProcessChecksheet::where('plant', 'karawang')->delete();
        \App\Models\CrossCutChecksheet::where('plant', 'karawang')->delete();
        \App\Models\SortirChecksheet::where('plant', 'karawang')->delete();

        // 3. Sub Assy Checksheets
        $this->info('Migrating Sub Assy Checksheets...');
        $oldChecksheets = \DB::connection('mysql_legacy')->table('checksheets')->get();
        foreach ($oldChecksheets as $old) {
            if (!isset($itemMapping[$old->item_id]))
                continue;

            \App\Models\Checksheet::create(array_merge((array) $old, [
                'id' => null,
                'plant' => 'karawang',
                'item_id' => $itemMapping[$old->item_id],
                'defects' => json_decode($old->defects, true),
            ]));
        }

        // 4. In-Process Checksheets
        $this->info('Migrating In-Process Checksheets...');
        $oldInProcess = \DB::connection('mysql_legacy')->table('in_process_checksheets')->get();
        foreach ($oldInProcess as $old) {
            if (!isset($itemMapping[$old->item_id]))
                continue;

            \App\Models\InProcessChecksheet::create(array_merge((array) $old, [
                'id' => null,
                'plant' => 'karawang',
                'item_id' => $itemMapping[$old->item_id],
                'dimension_check' => json_decode($old->dimension_check, true),
                'defects' => json_decode($old->defects, true),
            ]));
        }

        // 5. Cross Cut Checksheets
        $this->info('Migrating Cross Cut Checksheets...');
        $oldCrossCut = \DB::connection('mysql_legacy')->table('cross_cut_checksheets')->get();
        foreach ($oldCrossCut as $old) {
            if (!isset($itemMapping[$old->item_id]))
                continue;

            \App\Models\CrossCutChecksheet::create(array_merge((array) $old, [
                'id' => null,
                'plant' => 'karawang',
                'item_id' => $itemMapping[$old->item_id],
                'defects' => json_decode($old->defects, true),
            ]));
        }

        // 6. Sortir Checksheets
        $this->info('Migrating Sortir Checksheets...');
        $oldSortirExists = \Schema::connection('mysql_legacy')->hasTable('sortir_checksheets');
        if ($oldSortirExists) {
            $oldSortir = \DB::connection('mysql_legacy')->table('sortir_checksheets')->get();
            foreach ($oldSortir as $old) {
                if (!isset($itemMapping[$old->item_id]))
                    continue;

                \App\Models\SortirChecksheet::create(array_merge((array) $old, [
                    'id' => null,
                    'plant' => 'karawang',
                    'item_id' => $itemMapping[$old->item_id],
                    'defects' => json_decode($old->defects, true),
                    'source_id' => null,
                ]));
            }
        }

        $this->info('Migration completed successfully!');
    }
}
