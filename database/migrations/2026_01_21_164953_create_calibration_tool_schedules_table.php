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
        Schema::dropIfExists('calibration_tool_schedules');
        Schema::create('calibration_tool_schedules', function (Blueprint $table) {
            $table->id();
            $table->uuid('tool_id')->index();
            $table->date('schedule_date');
            $table->timestamps();

            $table->foreign('tool_id')->references('id')->on('calibration_tools')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calibration_tool_schedules');
    }
};
