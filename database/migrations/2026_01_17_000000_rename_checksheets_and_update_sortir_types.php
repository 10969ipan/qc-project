<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Rename checksheets to sub_assy_checksheets
        Schema::rename('checksheets', 'sub_assy_checksheets');

        // 2. Update sortir_checksheets source_type enum
        // Note: Changing ENUMs in MySQL requires a statement if not using a library like doctrine/dbal
        // But since we might be using SQLite or MySQL, let's use a safe approach.

        // For MySQL, we can use DB::statement
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE sortir_checksheets MODIFY COLUMN source_type ENUM('sub_assy', 'in_process', 'cross_cut') NOT NULL");
        } else {
            // For other drivers (like sqlite in tests), we might need more complex logic, 
            // but usually a simple change works if supported.
            Schema::table('sortir_checksheets', function (Blueprint $table) {
                $table->enum('source_type', ['sub_assy', 'in_process', 'cross_cut'])->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Rename back
        Schema::rename('sub_assy_checksheets', 'checksheets');

        // 2. Revert sortir_checksheets source_type enum
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE sortir_checksheets MODIFY COLUMN source_type ENUM('sub_assy', 'in_process') NOT NULL");
        } else {
            Schema::table('sortir_checksheets', function (Blueprint $table) {
                $table->enum('source_type', ['sub_assy', 'in_process'])->change();
            });
        }
    }
};
