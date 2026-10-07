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
        Schema::create('operator_compliance_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('plant', 50)->default('karawang');
            $table->unsignedBigInteger('operator_id');
            $table->string('bagian', 100)->nullable();
            $table->string('shift', 50)->nullable();
            $table->date('schedule_date');
            $table->timestamps();

            // Optionally add index for faster query
            $table->index(['plant', 'operator_id', 'schedule_date'], 'op_comp_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operator_compliance_schedules');
    }
};
