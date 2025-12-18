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
        Schema::table('checksheets', function (Blueprint $table) {
            if (!Schema::hasColumn('checksheets', 'kashift_approved_at')) {
                $table->timestamp('kashift_approved_at')->nullable()->after('kashift_qc');
            }
            if (!Schema::hasColumn('checksheets', 'supervisor_approved_at')) {
                $table->timestamp('supervisor_approved_at')->nullable()->after('supervisor_qc');
            }
            if (!Schema::hasColumn('checksheets', 'asst_manager_approved_at')) {
                $table->timestamp('asst_manager_approved_at')->nullable()->after('asst_manager_qc');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checksheets', function (Blueprint $table) {
            $table->dropColumn([
                'kashift_approved_at',
                'supervisor_approved_at',
                'asst_manager_approved_at',
            ]);
        });
    }
};
