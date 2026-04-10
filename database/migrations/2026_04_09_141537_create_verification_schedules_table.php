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
        Schema::create('verification_schedules', function (Blueprint $table) {
            $table->id();
            $table->uuid('tool_id');
            $table->integer('year');
            $table->integer('month');
            $table->integer('week'); // 1-4
            $table->string('planning_status')->nullable(); // P
            $table->string('actual_status')->nullable(); // A/OK
            $table->date('actual_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tool_id')->references('id')->on('verification_tools')->onDelete('cascade');
            $table->unique(['tool_id', 'year', 'month', 'week'], 'tool_schedule_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_schedules');
    }
};
