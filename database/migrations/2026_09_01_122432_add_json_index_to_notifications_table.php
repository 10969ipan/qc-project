<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan generated virtual column dari JSON data->checksheet_id dan data->checksheet_type
     * beserta composite index agar DELETE di HasDeleteNotification jauh lebih cepat.
     *
     * Menggunakan pengecekan IF NOT EXISTS agar aman dijalankan ulang jika sebelumnya
     * sempat gagal di tengah jalan (misal koneksi terputus).
     */
    public function up(): void
    {
        $columns = DB::select("SHOW COLUMNS FROM notifications LIKE 'notif_checksheet_id'");
        if (empty($columns)) {
            DB::statement("
                ALTER TABLE notifications
                ADD COLUMN notif_checksheet_id    VARCHAR(20)  GENERATED ALWAYS AS (JSON_UNQUOTE(JSON_EXTRACT(data, '$.checksheet_id')))    VIRTUAL,
                ADD COLUMN notif_checksheet_type  VARCHAR(60)  GENERATED ALWAYS AS (JSON_UNQUOTE(JSON_EXTRACT(data, '$.checksheet_type')))  VIRTUAL
            ");
        }

        $indexes = DB::select("SHOW INDEX FROM notifications WHERE Key_name = 'idx_notif_checksheet_lookup'");
        if (empty($indexes)) {
            DB::statement("
                ALTER TABLE notifications
                ADD INDEX idx_notif_checksheet_lookup (type, notif_checksheet_id, notif_checksheet_type)
            ");
        }
    }

    public function down(): void
    {
        $indexes = DB::select("SHOW INDEX FROM notifications WHERE Key_name = 'idx_notif_checksheet_lookup'");
        if (!empty($indexes)) {
            DB::statement("ALTER TABLE notifications DROP INDEX idx_notif_checksheet_lookup");
        }

        $columns = DB::select("SHOW COLUMNS FROM notifications LIKE 'notif_checksheet_id'");
        if (!empty($columns)) {
            DB::statement("ALTER TABLE notifications DROP COLUMN notif_checksheet_id, DROP COLUMN notif_checksheet_type");
        }
    }
};
