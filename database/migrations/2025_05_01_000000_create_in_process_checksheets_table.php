<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('in_process_checksheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->date('date');
            $table->string('shift');
            $table->integer('total_qty');
            $table->integer('sampling_qty');
            $table->integer('total_ok');
            $table->integer('total_ng');
            $table->string('judgment'); // OK / NG
            $table->text('remarks')->nullable();
            $table->text('dimension_check')->nullable(); // New column for In Process Checksheet
            $table->json('defects')->nullable(); // Store defects as JSON array
            $table->string('operator_initials')->nullable();
            $table->integer('cycle_time')->nullable();
            
            // Approval columns
            $table->string('approval_status')->nullable(); // Pending, Approved, Rejected
            $table->string('kashift_qc')->nullable();
            $table->timestamp('kashift_approved_at')->nullable();
            $table->string('supervisor_qc')->nullable();
            $table->timestamp('supervisor_approved_at')->nullable();
            $table->string('asst_manager_qc')->nullable();
            $table->timestamp('asst_manager_approved_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('in_process_checksheets');
    }
};
