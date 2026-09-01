<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tambahkan generated virtual column dari JSON data->checksheet_id dan data->checksheet_type
     * beserta composite index agar DELETE di HasDeleteNotification jauh lebih cepat.
     *
     * Tanpa index ini, setiap hapus data checksheet membutuhkan full-table scan
     * di 100k+ baris notifikasi hanya untuk mencari 1-2 notifikasi terkait.
     */
    public function up(): void
    {
        // 1. Tambah generated virtual column untuk checksheet_id dan checksheet_type dari JSON
        DB::statement("
            ALTER TABLE notifications
            ADD COLUMN notif_checksheet_id    VARCHAR(20)  GENERATED ALWAYS AS (JSON_UNQUOTE(JSON_EXTRACT(data, '$.checksheet_id')))    VIRTUAL,
            ADD COLUMN notif_checksheet_type  VARCHAR(60)  GENERATED ALWAYS AS (JSON_UNQUOTE(JSON_EXTRACT(data, '$.checksheet_type')))  VIRTUAL
        ");

        // 2. Index composite: type + checksheet_id + checksheet_type
        // Urutan: type dulu (cardinality rendah tapi mem-filter ke 2% baris),
        // lalu checksheet_id + checksheet_type untuk exact lookup
        DB::statement("
            ALTER TABLE notifications
            ADD INDEX idx_notif_checksheet_lookup (type, notif_checksheet_id, notif_checksheet_type)
        ");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE notifications DROP INDEX idx_notif_checksheet_lookup");
        DB::statement("ALTER TABLE notifications DROP COLUMN notif_checksheet_id, DROP COLUMN notif_checksheet_type");
    }
};
