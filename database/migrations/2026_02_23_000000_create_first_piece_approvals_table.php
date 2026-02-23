<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('first_piece_approvals', function (Blueprint $table) {
            $table->id();
            $table->char('plant_id', 36)->nullable();
            $table->foreign('plant_id')->references('id')->on('plants')->onDelete('cascade');
            $table->char('item_id', 36);
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            $table->date('date');
            $table->string('shift');
            $table->string('code_machine')->nullable();
            $table->integer('total_qty')->default(0);
            $table->integer('sampling_qty')->default(0);
            $table->integer('total_ok')->default(0);
            $table->integer('total_ng')->default(0);
            $table->string('judgment'); // OK / NG
            $table->string('operator_initials')->nullable();
            $table->text('remarks')->nullable();
            $table->string('next_proses')->nullable();
            $table->text('dimension_check')->nullable();
            $table->json('defects')->nullable();
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
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('first_piece_approvals');
    }
};
