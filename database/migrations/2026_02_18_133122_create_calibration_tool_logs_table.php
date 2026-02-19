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
        Schema::create('calibration_tool_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('calibration_tool_id');
            $table->enum('problem_type', ['ERROR', 'RUSAK']);
            $table->enum('action_taken', ['SERVICE_INTERNAL', 'SERVICE_EXTERNAL', 'PO_GA']);
            $table->text('description')->nullable();
            $table->date('reported_date');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('calibration_tool_id')->references('id')->on('calibration_tools')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calibration_tool_logs');
    }
};
