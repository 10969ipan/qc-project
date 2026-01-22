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
        Schema::create('calibration_verifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tool_id');
            $table->string('name_alat');
            $table->string('merk');
            $table->string('serial_number');
            $table->string('rentang_ukur');
            $table->string('resolusi');
            $table->string('frekuensi_kalibrasi');
            $table->string('lokasi_penyimpanan');
            $table->date('tanggal_kalibrasi');
            $table->date('tanggal_verifikasi');
            $table->date('next_kalibrasi');
            $table->decimal('nilai_alat', 12, 4);
            $table->decimal('nilai_koreksi', 12, 4);
            $table->decimal('nilai_ketidakpastian', 12, 4);
            $table->string('hasil_verifikasi');
            $table->string('judgment'); // OK/NG
            $table->string('std_toleransi');
            $table->string('acuan_toleransi');
            $table->string('certification_path')->nullable();
            $table->uuid('plant_id');
            $table->timestamps();

            $table->foreign('tool_id')->references('id')->on('calibration_tools')->onDelete('cascade');
            $table->foreign('plant_id')->references('id')->on('plants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calibration_verifications');
    }
};
