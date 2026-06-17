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
        Schema::create('durability_plating_checksheets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('plant_id')->nullable()->index();
            
            $table->date('date');
            $table->date('tanggal_produksi');
            $table->integer('shift')->nullable();
            
            $table->uuid('thickness_standard_id')->nullable()->index();
            $table->string('no_lot_produksi')->nullable();
            
            $table->decimal('thickness_cr', 8, 2)->nullable();
            $table->decimal('thickness_ni', 8, 2)->nullable();
            $table->decimal('thickness_cu', 8, 2)->nullable();
            
            $table->string('step_test_sb')->nullable();
            $table->string('step_test_mp')->nullable();
            
            $table->string('result')->nullable(); // OK / NG
            
            $table->string('analis')->nullable();
            $table->text('keterangan')->nullable();
            
            // Standard Checksheet Approval Fields
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->string('approval_status')->default('pending');
            $table->string('operator_name')->nullable();
            $table->string('leader_name')->nullable();
            $table->string('supervisor_name')->nullable();
            $table->string('manager_name')->nullable();
            $table->timestamp('leader_approved_at')->nullable();
            $table->timestamp('supervisor_approved_at')->nullable();
            $table->timestamp('manager_approved_at')->nullable();
            $table->string('rejected_by')->nullable();
            $table->string('reject_reason')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('durability_plating_checksheets');
    }
};
