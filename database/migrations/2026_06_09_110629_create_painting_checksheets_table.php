<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('painting_checksheets', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('plant_id')->constrained('plants')->restrictOnDelete();
            $table->foreignUuid('item_id')->constrained('items')->onDelete('cascade');
            
            // QR fields
            $table->string('qrcode')->nullable();
            $table->string('part_code')->nullable();
            $table->string('supplier_id')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('unique_code_id')->nullable();
            $table->string('sap_code')->nullable();
            $table->string('qrcode_verifikasi')->nullable();
            
            // Painting specific (was plating)
            $table->date('injection_date')->nullable();
            $table->string('injection_shift')->nullable();
            $table->date('painting_date')->nullable();
            $table->string('painting_shift')->nullable();
            $table->string('no_lot')->nullable();

            $table->date('date');
            $table->string('shift');
            $table->string('line'); // Meja
            $table->integer('total_qty');
            $table->integer('sampling_qty');
            $table->integer('total_ok');
            $table->integer('total_ng');
            $table->string('judgment'); // OK / NG
            $table->string('operator_initials')->nullable();
            $table->text('remarks')->nullable();
            $table->string('next_proses')->nullable();
            $table->json('defects')->nullable();
            $table->integer('cycle_time')->nullable();
            $table->integer('standard_cycle_time')->nullable();

            // Approval columns
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

            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('painting_checksheets');
    }
};
