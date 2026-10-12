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
        $existingColumns = Schema::getColumnListing('in_process_checksheets');

        Schema::table('in_process_checksheets', function (Blueprint $table) use ($existingColumns) {
            if (!in_array('has_dimension', $existingColumns)) {
                $table->boolean('has_dimension')->default(false)->after('dimension_check');
            }
            if (!in_array('is_dimension_ng', $existingColumns)) {
                $table->boolean('is_dimension_ng')->default(false)->after('has_dimension');
            }
        });

        // Backfill data yang sudah ada:
        // has_dimension = 1 jika dimension_check terisi dan bukan array/string kosong
        DB::table('in_process_checksheets')
            ->whereNotNull('dimension_check')
            ->whereNotIn('dimension_check', ['', '[]', '{}', 'null', '""'])
            ->update(['has_dimension' => 1]);

        // has_dimension = 1 juga untuk scan hardware/camera atau memiliki qrcode/unique_code_id
        DB::table('in_process_checksheets')
            ->where(function ($q) {
                $q->whereIn('scan_method', ['hardware', 'camera'])
                  ->orWhereNotNull('qrcode')
                  ->orWhereNotNull('unique_code_id');
            })
            ->update(['has_dimension' => 1]);

        // is_dimension_ng = 1 jika judgment adalah 'NG' atau total_ng > 0
        DB::table('in_process_checksheets')
            ->where(function ($q) {
                $q->where('judgment', 'NG')
                  ->orWhere('total_ng', '>', 0);
            })
            ->update(['is_dimension_ng' => 1]);

        // Tambah composite index untuk percepat query filter non-admin
        $existingIndexes = collect(Schema::getIndexes('in_process_checksheets'))->pluck('name')->all();
        if (!in_array('idx_inproc_plant_scan_dim_date', $existingIndexes)) {
            Schema::table('in_process_checksheets', function (Blueprint $table) {
                $table->index(['plant_id', 'scan_method', 'has_dimension', 'date'], 'idx_inproc_plant_scan_dim_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $existingIndexes = collect(Schema::getIndexes('in_process_checksheets'))->pluck('name')->all();
        if (in_array('idx_inproc_plant_scan_dim_date', $existingIndexes)) {
            Schema::table('in_process_checksheets', function (Blueprint $table) {
                $table->dropIndex('idx_inproc_plant_scan_dim_date');
            });
        }

        $existingColumns = Schema::getColumnListing('in_process_checksheets');
        Schema::table('in_process_checksheets', function (Blueprint $table) use ($existingColumns) {
            $colsToDrop = [];
            if (in_array('has_dimension', $existingColumns)) {
                $colsToDrop[] = 'has_dimension';
            }
            if (in_array('is_dimension_ng', $existingColumns)) {
                $colsToDrop[] = 'is_dimension_ng';
            }
            if (!empty($colsToDrop)) {
                $table->dropColumn($colsToDrop);
            }
        });
    }
};
