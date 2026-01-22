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
        Schema::create('calibration_tools', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('bagian');
            $table->string('name_alat');
            $table->string('serial_number');
            $table->string('range');
            $table->string('resolusi');
            $table->string('lokasi_pakai');
            $table->date('tanggal_beli');
            $table->string('frekuensi_kalibrasi');
            $table->text('riwayat_kalibrasi')->nullable();
            $table->string('jenis_kalibrasi');
            $table->date('schedule_planning');
            $table->string('certification_path')->nullable();
            $table->uuid('plant_id');
            $table->timestamps();

            $table->foreign('plant_id')->references('id')->on('plants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calibration_tools');
    }
};
