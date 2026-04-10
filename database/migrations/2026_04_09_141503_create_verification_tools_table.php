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
        Schema::create('verification_tools', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name_part');
            $table->string('no_part');
            $table->string('tool_type'); // JENIS ALAT
            $table->string('customer');
            $table->integer('quantity'); // QTY (UNIT)
            $table->string('verification_frequency'); // FREKUENSI VERIFIKASI
            $table->text('calibration_history')->nullable(); // RIWAYAT KALIBRASI
            $table->string('verification_type'); // JENIS VERIFIKASI
            $table->string('tool_judgment')->nullable(); // JUDGMENT ALAT
            $table->string('verification_date_remarks')->nullable(); // TANGGAL VERIFIKASI / KETERANGAN
            $table->string('certification_path')->nullable();
            $table->uuid('plant_id');
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('plant_id')->references('id')->on('plants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_tools');
    }
};
