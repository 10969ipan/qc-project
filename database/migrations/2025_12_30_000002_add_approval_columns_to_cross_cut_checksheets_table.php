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
        Schema::table('cross_cut_checksheets', function (Blueprint $table) {
            $table->string('operator_initials')->nullable()->after('item_id');
            $table->string('approval_status')->nullable()->after('cycle_time');
            $table->string('kashift_qc')->nullable()->after('approval_status');
            $table->timestamp('kashift_approved_at')->nullable()->after('kashift_qc');
            $table->string('supervisor_qc')->nullable()->after('kashift_approved_at');
            $table->timestamp('supervisor_approved_at')->nullable()->after('supervisor_qc');
            $table->string('asst_manager_qc')->nullable()->after('supervisor_approved_at');
            $table->timestamp('asst_manager_approved_at')->nullable()->after('asst_manager_qc');
            $table->string('manager_qc')->nullable()->after('asst_manager_approved_at');
            $table->timestamp('manager_approved_at')->nullable()->after('manager_qc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cross_cut_checksheets', function (Blueprint $table) {
            $table->dropColumn([
                'operator_initials',
                'approval_status',
                'kashift_qc',
                'kashift_approved_at',
                'supervisor_qc',
                'supervisor_approved_at',
                'asst_manager_qc',
                'asst_manager_approved_at',
                'manager_qc',
                'manager_approved_at',
            ]);
        });
    }
};
