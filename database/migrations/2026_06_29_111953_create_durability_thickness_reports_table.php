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
        Schema::create('durability_thickness_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('standard_performance_test_id')->constrained('standard_performance_tests', 'id', 'fk_std_perf_test')->onDelete('cascade');
            $table->string('actual_cu')->nullable();
            $table->string('actual_ni')->nullable();
            $table->string('actual_cr')->nullable();
            $table->date('tanggal_cek')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('durability_thickness_reports');
    }
};
