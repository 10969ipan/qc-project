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
        Schema::create('verification_verifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tool_id');
            $table->string('name_part');
            $table->string('no_part');
            $table->date('tanggal_verifikasi')->nullable();
            $table->date('next_verifikasi')->nullable();
            $table->string('judgment')->nullable();
            $table->text('remarks')->nullable();
            $table->string('certification_path')->nullable();
            $table->uuid('plant_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tool_id')->references('id')->on('verification_tools')->onDelete('cascade');
            $table->foreign('plant_id')->references('id')->on('plants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_verifications');
    }
};
