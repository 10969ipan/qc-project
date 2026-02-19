<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('calibration_tool_logs', function (Blueprint $table) {
            $table->enum('judgment_status', ['OK', 'NG'])->nullable()->after('reported_date');
            $table->text('judgment_remarks')->nullable()->after('judgment_status');
            $table->unsignedBigInteger('judged_by')->nullable()->after('judgment_remarks');
            $table->timestamp('judged_at')->nullable()->after('judged_by');

            $table->foreign('judged_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('calibration_tool_logs', function (Blueprint $table) {
            $table->dropForeign(['judged_by']);
            $table->dropColumn(['judgment_status', 'judgment_remarks', 'judged_by', 'judged_at']);
        });
    }
};
