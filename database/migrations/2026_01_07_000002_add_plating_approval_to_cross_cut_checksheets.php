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
        Schema::table('cross_cut_checksheets', function (Blueprint $table) {
            // Add new approval columns for the new workflow
            $table->string('karu_qc')->nullable()->after('operator_initials');
            $table->timestamp('karu_qc_approved_at')->nullable()->after('karu_qc');

            $table->string('kashift_plating')->nullable()->after('karu_qc_approved_at');
            $table->timestamp('kashift_plating_approved_at')->nullable()->after('kashift_plating');

            $table->string('supervisor_plating')->nullable()->after('supervisor_approved_at');
            $table->timestamp('supervisor_plating_approved_at')->nullable()->after('supervisor_plating');

            $table->string('manager_plating')->nullable()->after('manager_approved_at');
            $table->timestamp('manager_plating_approved_at')->nullable()->after('manager_plating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cross_cut_checksheets', function (Blueprint $table) {
            $table->dropColumn([
                'karu_qc',
                'karu_qc_approved_at',
                'kashift_plating',
                'kashift_plating_approved_at',
                'supervisor_plating',
                'supervisor_plating_approved_at',
                'manager_plating',
                'manager_plating_approved_at',
            ]);
        });
    }
};
