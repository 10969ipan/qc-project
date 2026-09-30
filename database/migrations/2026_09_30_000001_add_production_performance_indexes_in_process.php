<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for additional production indexes on in_process_checksheets.
     */
    public function up(): void
    {
        if (Schema::hasTable('in_process_checksheets')) {
            $indexList = [
                'idx_inproc_date' => 'ADD INDEX idx_inproc_date (date)',
                'idx_inproc_plant_date_item' => 'ADD INDEX idx_inproc_plant_date_item (plant_id, date, item_id)',
                'idx_inproc_plant_judgment' => 'ADD INDEX idx_inproc_plant_judgment (plant_id, judgment, total_ng)',
                'idx_inproc_plant_initials' => 'ADD INDEX idx_inproc_plant_initials (plant_id, operator_initials)',
                'idx_inproc_approval_status' => 'ADD INDEX idx_inproc_approval_status (approval_status)',
            ];

            foreach ($indexList as $keyName => $sql) {
                $indexes = DB::select("SHOW INDEX FROM in_process_checksheets WHERE Key_name = ?", [$keyName]);
                if (empty($indexes)) {
                    DB::statement("ALTER TABLE in_process_checksheets " . $sql);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('in_process_checksheets')) {
            $keys = ['idx_inproc_date', 'idx_inproc_plant_date_item', 'idx_inproc_plant_judgment', 'idx_inproc_plant_initials', 'idx_inproc_approval_status'];
            foreach ($keys as $keyName) {
                $indexes = DB::select("SHOW INDEX FROM in_process_checksheets WHERE Key_name = ?", [$keyName]);
                if (!empty($indexes)) {
                    DB::statement("ALTER TABLE in_process_checksheets DROP INDEX " . $keyName);
                }
            }
        }
    }
};
