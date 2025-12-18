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
            if (!Schema::hasColumn('checksheets', 'kashift_qc')) {
                $table->string('kashift_qc')->nullable()->after('operator_initials');
            }
            if (!Schema::hasColumn('checksheets', 'supervisor_qc')) {
                $table->string('supervisor_qc')->nullable()->after('kashift_qc');
            }
            if (!Schema::hasColumn('checksheets', 'asst_manager_qc')) {
                $table->string('asst_manager_qc')->nullable()->after('supervisor_qc');
            }
            if (!Schema::hasColumn('checksheets', 'approval_status')) {
                $table->string('approval_status')->nullable()->after('asst_manager_qc');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checksheets', function (Blueprint $table) {
            if (Schema::hasColumn('checksheets', 'approval_status')) {
                $table->dropColumn('approval_status');
            }
            // Note: We don't drop the QC columns here assuming they might be managed by another migration,
            // or we can drop them if we are sure we added them. 
            // Given the confusion, let's keep it safe and only drop what we definitely added as "new" in this logical step.
            // But if we want to be clean:
            // $table->dropColumn(['kashift_qc', 'supervisor_qc', 'asst_manager_qc']);
        });
    }
};
