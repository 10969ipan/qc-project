<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('durability_thickness_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('durability_thickness_reports', 'supervisor_qc')) {
                $table->string('supervisor_qc')->nullable()->after('description_porecount');
                $table->timestamp('supervisor_approved_at')->nullable()->after('supervisor_qc');
            }
            if (!Schema::hasColumn('durability_thickness_reports', 'supervisor_plating')) {
                $table->string('supervisor_plating')->nullable()->after('supervisor_approved_at');
                $table->timestamp('supervisor_plating_approved_at')->nullable()->after('supervisor_plating');
            }
            if (!Schema::hasColumn('durability_thickness_reports', 'asst_manager_qc')) {
                $table->string('asst_manager_qc')->nullable()->after('supervisor_plating_approved_at');
                $table->timestamp('asst_manager_approved_at')->nullable()->after('asst_manager_qc');
            }
            if (!Schema::hasColumn('durability_thickness_reports', 'asst_manager_plating')) {
                $table->string('asst_manager_plating')->nullable()->after('asst_manager_approved_at');
                $table->timestamp('asst_manager_plating_approved_at')->nullable()->after('asst_manager_plating');
            }
            if (!Schema::hasColumn('durability_thickness_reports', 'rejection_remarks')) {
                $table->text('rejection_remarks')->nullable()->after('asst_manager_plating_approved_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('durability_thickness_reports', function (Blueprint $table) {
            $columns = [
                'supervisor_qc', 'supervisor_approved_at',
                'supervisor_plating', 'supervisor_plating_approved_at',
                'asst_manager_qc', 'asst_manager_approved_at',
                'asst_manager_plating', 'asst_manager_plating_approved_at',
                'rejection_remarks'
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('durability_thickness_reports', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
