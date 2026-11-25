<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leveling_recap_approvals', function (Blueprint $table) {
            $cols = array_filter(
                ['rejected_stage', 'rejected_by', 'rejected_at', 'reject_reason'],
                fn($c) => Schema::hasColumn('leveling_recap_approvals', $c)
            );
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }

    public function down(): void
    {
        Schema::table('leveling_recap_approvals', function (Blueprint $table) {
            if (!Schema::hasColumn('leveling_recap_approvals', 'rejected_stage')) {
                $table->string('rejected_stage', 20)->nullable()->after('asst_mgr_approved_at');
                $table->unsignedBigInteger('rejected_by')->nullable()->after('rejected_stage');
                $table->timestamp('rejected_at')->nullable()->after('rejected_by');
                $table->text('reject_reason')->nullable()->after('rejected_at');
            }
        });
    }
};
