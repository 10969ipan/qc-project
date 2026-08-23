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
        // 1. activity_logs
        if (Schema::hasTable('activity_logs')) {
            try {
                Schema::table('activity_logs', function (Blueprint $table) {
                    $table->index(['model_type', 'model_id'], 'idx_actlog_model');
                    $table->index(['user_id', 'created_at'], 'idx_actlog_user_created');
                });
            } catch (\Throwable $e) {}
        }

        // 2. durability_thickness_reports
        if (Schema::hasTable('durability_thickness_reports')) {
            try {
                Schema::table('durability_thickness_reports', function (Blueprint $table) {
                    $table->index(['is_trial', 'data1_id'], 'idx_durability_trial_data1');
                    $table->index(['standard_performance_test_id'], 'idx_durability_std_test');
                });
            } catch (\Throwable $e) {}
        }

        // 3. calibration_tools
        if (Schema::hasTable('calibration_tools')) {
            try {
                Schema::table('calibration_tools', function (Blueprint $table) {
                    $table->index(['plant_id', 'status'], 'idx_calib_plant_status');
                });
            } catch (\Throwable $e) {}
        }

        // 4. customer_claims
        if (Schema::hasTable('customer_claims')) {
            try {
                Schema::table('customer_claims', function (Blueprint $table) {
                    $table->index(['plant_id', 'year', 'month'], 'idx_claim_plant_ym');
                });
            } catch (\Throwable $e) {}
        }

        // 5. verification_tools
        if (Schema::hasTable('verification_tools')) {
            try {
                Schema::table('verification_tools', function (Blueprint $table) {
                    $table->index(['plant_id', 'name_part'], 'idx_verif_plant_name');
                });
            } catch (\Throwable $e) {}
        }

        // 6. kakotoras
        if (Schema::hasTable('kakotoras')) {
            try {
                Schema::table('kakotoras', function (Blueprint $table) {
                    $table->index(['plant_id', 'issue_date'], 'idx_kakotora_plant_date');
                });
            } catch (\Throwable $e) {}
        }

        // 7. items
        if (Schema::hasTable('items')) {
            try {
                Schema::table('items', function (Blueprint $table) {
                    $table->index(['plant_id', 'category_id'], 'idx_items_plant_category');
                });
            } catch (\Throwable $e) {}
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'activity_logs' => ['idx_actlog_model', 'idx_actlog_user_created'],
            'durability_thickness_reports' => ['idx_durability_trial_data1', 'idx_durability_std_test'],
            'calibration_tools' => ['idx_calib_plant_status'],
            'customer_claims' => ['idx_claim_plant_ym'],
            'verification_tools' => ['idx_verif_plant_name'],
            'kakotoras' => ['idx_kakotora_plant_date'],
            'items' => ['idx_items_plant_category'],
        ];

        foreach ($tables as $tableName => $indexes) {
            if (Schema::hasTable($tableName)) {
                foreach ($indexes as $indexName) {
                    try {
                        Schema::table($tableName, function (Blueprint $table) use ($indexName) {
                            $table->dropIndex($indexName);
                        });
                    } catch (\Throwable $e) {}
                }
            }
        }
    }
};
