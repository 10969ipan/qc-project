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
            $table->string('action_taken')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calibration_tool_logs', function (Blueprint $table) {
            $table->enum('action_taken', ['SERVICE_INTERNAL', 'SERVICE_EXTERNAL', 'PO_GA'])->change();
        });
    }
};
