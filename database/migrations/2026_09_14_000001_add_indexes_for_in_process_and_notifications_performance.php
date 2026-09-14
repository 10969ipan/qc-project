<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Safely add generated virtual column for plant_id on notifications and composite indexes for fast queries.
     */
    public function up(): void
    {
        // 1. Add notif_plant_id virtual column to notifications if not exists
        if (Schema::hasTable('notifications')) {
            $columns = DB::select("SHOW COLUMNS FROM notifications LIKE 'notif_plant_id'");
            if (empty($columns)) {
                DB::statement("
                    ALTER TABLE notifications
                    ADD COLUMN notif_plant_id VARCHAR(50) GENERATED ALWAYS AS (JSON_UNQUOTE(JSON_EXTRACT(data, '$.plant_id'))) VIRTUAL
                ");
            }

            $indexes = DB::select("SHOW INDEX FROM notifications WHERE Key_name = 'idx_notif_user_plant_lookup'");
            if (empty($indexes)) {
                DB::statement("
                    ALTER TABLE notifications
                    ADD INDEX idx_notif_user_plant_lookup (user_id, is_read, notif_plant_id, created_at)
                ");
            }
        }

        // 2. Add performance composite indexes to in_process_checksheets if not exists
        if (Schema::hasTable('in_process_checksheets')) {
            $indexes1 = DB::select("SHOW INDEX FROM in_process_checksheets WHERE Key_name = 'idx_inproc_plant_date_created'");
            if (empty($indexes1)) {
                DB::statement("
                    ALTER TABLE in_process_checksheets
                    ADD INDEX idx_inproc_plant_date_created (plant_id, date, created_at)
                ");
            }

            $indexes2 = DB::select("SHOW INDEX FROM in_process_checksheets WHERE Key_name = 'idx_inproc_plant_scan_method'");
            if (empty($indexes2) && Schema::hasColumn('in_process_checksheets', 'scan_method')) {
                DB::statement("
                    ALTER TABLE in_process_checksheets
                    ADD INDEX idx_inproc_plant_scan_method (plant_id, scan_method)
                ");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('notifications')) {
            $indexes = DB::select("SHOW INDEX FROM notifications WHERE Key_name = 'idx_notif_user_plant_lookup'");
            if (!empty($indexes)) {
                DB::statement("ALTER TABLE notifications DROP INDEX idx_notif_user_plant_lookup");
            }

            $columns = DB::select("SHOW COLUMNS FROM notifications LIKE 'notif_plant_id'");
            if (!empty($columns)) {
                DB::statement("ALTER TABLE notifications DROP COLUMN notif_plant_id");
            }
        }

        if (Schema::hasTable('in_process_checksheets')) {
            $indexes1 = DB::select("SHOW INDEX FROM in_process_checksheets WHERE Key_name = 'idx_inproc_plant_date_created'");
            if (!empty($indexes1)) {
                DB::statement("ALTER TABLE in_process_checksheets DROP INDEX idx_inproc_plant_date_created");
            }

            $indexes2 = DB::select("SHOW INDEX FROM in_process_checksheets WHERE Key_name = 'idx_inproc_plant_scan_method'");
            if (!empty($indexes2)) {
                DB::statement("ALTER TABLE in_process_checksheets DROP INDEX idx_inproc_plant_scan_method");
            }
        }
    }
};
