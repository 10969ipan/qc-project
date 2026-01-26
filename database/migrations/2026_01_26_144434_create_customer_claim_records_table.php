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
        Schema::create('customer_claim_records', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_claim')->nullable();
            $table->string('customer')->nullable();
            $table->string('plant_up_customer')->nullable();
            $table->string('claim_type')->nullable(); // Officially / Non Officially / Suspect
            $table->string('no_report')->nullable(); // No. Dokumen (Report)
            $table->string('source_type')->nullable(); // Internal/ Eksternal
            $table->string('project')->nullable(); // Project (NM/MP)
            $table->string('nama_part')->nullable();
            $table->text('problem')->nullable();
            $table->string('kategori_defect')->nullable(); // Qty, Appearance, Function, Performance, Handling
            $table->string('kategori_penyimpangan')->nullable(); // 4M/IPQ/OTHER
            $table->integer('qty')->default(0);
            $table->string('initial_operator')->nullable();
            $table->string('initial_inspektor')->nullable();
            $table->string('frek')->nullable();
            $table->string('persen_frek')->nullable();
            $table->string('action_taken')->nullable(); // Report / No Report / Replacement / etc
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->text('feedback')->nullable();
            $table->string('status_feedback')->nullable();
            $table->string('status_cm')->nullable(); // Status (C/M etc.)
            $table->string('monitoring')->nullable();
            $table->text('evaluasi')->nullable();
            $table->string('monitoring_status')->nullable();

            $table->foreignUuid('plant_id')->constrained('plants')->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_claim_records');
    }
};
