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
        Schema::create('verification_tool_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('verification_tool_id');
            $table->string('problem_type'); // ERROR, RUSAK, etc.
            $table->text('description');
            $table->date('reported_date');
            $table->string('action_taken')->nullable();
            $table->string('judgment_status')->nullable(); // OK, NG
            $table->text('judgment_remarks')->nullable();
            $table->unsignedBigInteger('judged_by')->nullable();
            $table->timestamp('judged_at')->nullable();
            $table->unsignedBigInteger('user_id'); // Reported by
            $table->string('evidence_report')->nullable();
            $table->string('evidence_judgment')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('verification_tool_id')->references('id')->on('verification_tools')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('judged_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_tool_logs');
    }
};
