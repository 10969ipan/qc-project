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
        Schema::table('calibration_tool_logs', function (Blueprint $table) {
            // SPV Judgment
            $table->enum('spv_judgment_status', ['OK', 'NG'])->nullable()->after('judged_at');
            $table->text('spv_judgment_remarks')->nullable()->after('spv_judgment_status');
            $table->unsignedBigInteger('spv_judged_by')->nullable()->after('spv_judgment_remarks');
            $table->timestamp('spv_judged_at')->nullable()->after('spv_judged_by');

            // Manager Judgment
            $table->enum('mgr_judgment_status', ['OK', 'NG'])->nullable()->after('spv_judged_at');
            $table->text('mgr_judgment_remarks')->nullable()->after('mgr_judgment_status');
            $table->unsignedBigInteger('mgr_judged_by')->nullable()->after('mgr_judgment_remarks');
            $table->timestamp('mgr_judged_at')->nullable()->after('mgr_judged_by');

            // Foreign keys
            $table->foreign('spv_judged_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('mgr_judged_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('calibration_tool_logs', function (Blueprint $table) {
            $table->dropForeign(['spv_judged_by']);
            $table->dropForeign(['mgr_judged_by']);
            $table->dropColumn([
                'spv_judgment_status', 'spv_judgment_remarks', 'spv_judged_by', 'spv_judged_at',
                'mgr_judgment_status', 'mgr_judgment_remarks', 'mgr_judged_by', 'mgr_judged_at'
            ]);
        });
    }
};
