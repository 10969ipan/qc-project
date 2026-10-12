<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Perbaikan hasil diagnosis production (209k baris):
 * 1. Query filter inisial (plant_id + date>=90 hari + operator_initials) 6,9 dtk -> index covering (plant_id, date, operator_initials).
 * 2. idx_inproc_plant_scan_date (plant_id, scan_method, date) tidak mencakup created_at -> bila dipilih optimizer
 *    terjadi filesort (1,6 dtk). Diganti index lengkap yang memenuhi ORDER BY date DESC, created_at DESC.
 * Hanya menyentuh index, tidak mengubah data.
 */
return new class extends Migration
{
    private function indexNames(): array
    {
        return collect(Schema::getIndexes('in_process_checksheets'))->pluck('name')->all();
    }

    public function up(): void
    {
        $existing = $this->indexNames();

        Schema::table('in_process_checksheets', function (Blueprint $table) use ($existing) {
            if (!in_array('idx_inproc_plant_date_initials', $existing)) {
                $table->index(['plant_id', 'date', 'operator_initials'], 'idx_inproc_plant_date_initials');
            }
            if (!in_array('idx_inproc_plant_scan_date_created', $existing)) {
                $table->index(['plant_id', 'scan_method', 'date', 'created_at'], 'idx_inproc_plant_scan_date_created');
            }
        });

        if (in_array('idx_inproc_plant_scan_date', $existing)) {
            Schema::table('in_process_checksheets', function (Blueprint $table) {
                $table->dropIndex('idx_inproc_plant_scan_date');
            });
        }
    }

    public function down(): void
    {
        $existing = $this->indexNames();

        if (!in_array('idx_inproc_plant_scan_date', $existing)) {
            Schema::table('in_process_checksheets', function (Blueprint $table) {
                $table->index(['plant_id', 'scan_method', 'date'], 'idx_inproc_plant_scan_date');
            });
        }

        Schema::table('in_process_checksheets', function (Blueprint $table) use ($existing) {
            if (in_array('idx_inproc_plant_date_initials', $existing)) {
                $table->dropIndex('idx_inproc_plant_date_initials');
            }
            if (in_array('idx_inproc_plant_scan_date_created', $existing)) {
                $table->dropIndex('idx_inproc_plant_scan_date_created');
            }
        });
    }
};
