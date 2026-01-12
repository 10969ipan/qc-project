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
        Schema::create('machine_statuses', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['line', 'machine'])->index();
            $table->integer('number');
            $table->enum('status', ['normal', 'maintenance', 'stopped'])->default('normal');
            $table->string('description')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();

            // Unique constraint to prevent duplicate status for same unit
            $table->unique(['type', 'number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machine_statuses');
    }
};
