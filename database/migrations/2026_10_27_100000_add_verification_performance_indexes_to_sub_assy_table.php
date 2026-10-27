<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('sub_assy_checksheets')) {
            $indexes = [
                'idx_subassy_plant_date_created' => ['plant_id', 'date', 'created_at'],
                'idx_subassy_date_created'       => ['date', 'created_at'],
                'idx_subassy_plant_unique_date'  => ['plant_id', 'unique_code_id', 'date'],
                'idx_subassy_plant_item'         => ['plant_id', 'item_id'],
                'idx_subassy_plant_line'         => ['plant_id', 'line'],
            ];

            foreach ($indexes as $indexName => $columns) {
                try {
                    Schema::table('sub_assy_checksheets', function (Blueprint $table) use ($columns, $indexName) {
                        $table->index($columns, $indexName);
                    });
                } catch (\Throwable $e) {
                    // Index may already exist
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sub_assy_checksheets')) {
            $indexes = [
                'idx_subassy_plant_date_created',
                'idx_subassy_date_created',
                'idx_subassy_plant_unique_date',
                'idx_subassy_plant_item',
                'idx_subassy_plant_line',
            ];

            foreach ($indexes as $indexName) {
                try {
                    Schema::table('sub_assy_checksheets', function (Blueprint $table) use ($indexName) {
                        $table->dropIndex($indexName);
                    });
                } catch (\Throwable $e) {}
            }
        }
    }
};
