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
        Schema::create('double_tape_checksheets', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('plant_id')->constrained('plants')->restrictOnDelete();
            $table->foreignUuid('item_id')->constrained('items')->onDelete('cascade');
            $table->date('date');
            $table->string('shift');
            // 'line' column is removed as per requirements for Double Tape
            $table->integer('total_qty');
            $table->integer('sampling_qty');
            $table->integer('total_ok');
            $table->integer('total_ng');
            $table->string('judgment'); // OK / NG
            $table->string('operator_initials')->nullable();
            $table->text('remarks')->nullable();
            $table->string('next_proses')->nullable();
            $table->json('defects')->nullable(); // Store defects as JSON array
            $table->integer('cycle_time')->nullable();

            // Approval columns
            $table->string('approval_status')->nullable(); // Pending, Approved, Rejected
            $table->string('kashift_qc')->nullable();
            $table->timestamp('kashift_approved_at')->nullable();
            $table->string('supervisor_qc')->nullable();
            $table->timestamp('supervisor_approved_at')->nullable();
            $table->string('asst_manager_qc')->nullable();
            $table->timestamp('asst_manager_approved_at')->nullable();
            $table->string('manager_qc')->nullable();
            $table->timestamp('manager_approved_at')->nullable();
            $table->text('rejection_remarks')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('double_tape_checksheets');
    }
};
