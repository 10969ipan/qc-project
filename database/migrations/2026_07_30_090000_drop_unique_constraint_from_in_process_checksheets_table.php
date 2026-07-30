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
        if (Schema::hasTable('in_process_checksheets') && Schema::hasColumn('in_process_checksheets', 'unique_code_id')) {
            try {
                Schema::table('in_process_checksheets', function (Blueprint $table) {
                    $table->dropUnique('in_process_checksheets_unique_code_id_unique');
                });
            } catch (\Throwable $e) {
                // ponytail: ignore if unique index was not present in this database instance
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('in_process_checksheets') && Schema::hasColumn('in_process_checksheets', 'unique_code_id')) {
            try {
                Schema::table('in_process_checksheets', function (Blueprint $table) {
                    $table->unique('unique_code_id', 'in_process_checksheets_unique_code_id_unique');
                });
            } catch (\Throwable $e) {
                // Ignore rollback errors if already exists
            }
        }
    }
};
