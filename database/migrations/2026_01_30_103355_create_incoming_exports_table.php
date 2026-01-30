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
        Schema::create('incoming_exports', function (Blueprint $table) {
            $table->id();
            $table->char('plant_id', 36);
            $table->char('item_id', 36);
            $table->string('standard')->nullable();
            $table->date('date'); // tanggal check
            $table->date('tanggal_delivery');
            $table->string('judgment'); // OK / NG
            $table->json('defects')->nullable();
            $table->integer('total_ng')->default(0);
            $table->string('operator_initials')->nullable();
            $table->text('remarks')->nullable();

            // Approvals
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

            $table->foreign('plant_id')->references('id')->on('plants')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incoming_exports');
    }
};
