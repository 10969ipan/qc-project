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
        Schema::create('cross_cut_painting_checksheets', function (Blueprint $table) {
            $table->id();
            $table->char('plant_id', 36);
            $table->char('item_id', 36);
            $table->string('operator_initials')->nullable();

            $table->foreign('plant_id')->references('id')->on('plants')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');

            // Approval Status Columns (same as plating)
            $table->string('karu_qc')->nullable();
            $table->timestamp('karu_qc_approved_at')->nullable();

            $table->string('kashift_plating')->nullable();
            $table->timestamp('kashift_plating_approved_at')->nullable();

            $table->string('supervisor_plating')->nullable();
            $table->timestamp('supervisor_plating_approved_at')->nullable();

            $table->string('manager_plating')->nullable();
            $table->timestamp('manager_plating_approved_at')->nullable();

            $table->string('production_shift');
            $table->string('qc_shift');
            $table->dateTime('production_datetime');
            $table->dateTime('qc_datetime');
            $table->string('image_path');
            $table->string('chemical_copper')->nullable();
            $table->string('chemical_nikel')->nullable();
            $table->string('chemical_eching')->nullable();
            $table->string('chemical_abu')->nullable();
            $table->enum('position_remark_judgment', ['OK', 'NG']);
            $table->string('position_remark_no_lot');
            $table->json('defects')->nullable();
            $table->integer('total_ng')->default(0);
            $table->integer('sampling_qty')->default(0);
            $table->string('result_remark')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('next_proses')->nullable();
            $table->integer('cycle_time')->nullable();
            $table->string('approval_status')->nullable();

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
        Schema::dropIfExists('cross_cut_painting_checksheets');
    }
};
