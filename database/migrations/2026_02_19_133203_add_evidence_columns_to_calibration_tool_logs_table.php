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
        Schema::table('calibration_tool_logs', function (Blueprint $table) {
            $table->string('evidence_report')->nullable()->after('description');
            $table->string('evidence_judgment')->nullable()->after('judgment_remarks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calibration_tool_logs', function (Blueprint $table) {
            $table->dropColumn(['evidence_report', 'evidence_judgment']);
        });
    }
};
