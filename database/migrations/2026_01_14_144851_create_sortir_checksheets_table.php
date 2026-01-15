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
        Schema::create('sortir_checksheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->enum('source_type', ['sub_assy', 'in_process']);
            $table->unsignedBigInteger('source_id');
            $table->date('date');
            $table->string('shift');
            $table->string('line')->nullable();
            $table->integer('total_qty');
            $table->integer('sampling_qty');
            $table->json('defects')->nullable();
            $table->integer('total_ok');
            $table->integer('total_ng');
            $table->enum('judgment', ['OK', 'NG']);
            $table->string('operator_initials')->nullable();
            $table->text('remarks')->nullable();
            $table->integer('cycle_time')->nullable();
            $table->enum('next_proses', ['HOLD', 'REPAIR'])->nullable();

            // Approval fields
            $table->string('kashift_qc')->nullable();
            $table->timestamp('kashift_qc_time')->nullable();
            $table->string('supervisor_qc')->nullable();
            $table->timestamp('supervisor_qc_time')->nullable();
            $table->string('asst_manager_qc')->nullable();
            $table->timestamp('asst_manager_qc_time')->nullable();
            $table->string('manager_qc')->nullable();
            $table->timestamp('manager_qc_time')->nullable();
            $table->text('rejection_remarks')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('source_type');
            $table->index('source_id');
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sortir_checksheets');
    }
};
