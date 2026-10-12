<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Backfill any potential NULL scan_method to 'manual'
        DB::table('in_process_checksheets')
            ->whereNull('scan_method')
            ->update(['scan_method' => 'manual']);

        // 2. Add composite index for (plant_id, scan_method, date) if not already exists
        $existingIndexes = collect(Schema::getIndexes('in_process_checksheets'))->pluck('name')->all();

        if (!in_array('idx_inproc_plant_scan_date', $existingIndexes)) {
            Schema::table('in_process_checksheets', function (Blueprint $table) {
                $table->index(['plant_id', 'scan_method', 'date'], 'idx_inproc_plant_scan_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $existingIndexes = collect(Schema::getIndexes('in_process_checksheets'))->pluck('name')->all();

        if (in_array('idx_inproc_plant_scan_date', $existingIndexes)) {
            Schema::table('in_process_checksheets', function (Blueprint $table) {
                $table->dropIndex('idx_inproc_plant_scan_date');
            });
        }
    }
};
