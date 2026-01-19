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
        Schema::dropIfExists('customer_claims');
        Schema::create('customer_claims', function (Blueprint $table) {
            $table->id();
            $table->uuid('plant_id');
            $table->integer('year');
            $table->integer('month'); // 1-12
            $table->decimal('ppm_value', 10, 2);
            $table->decimal('target_value', 10, 2)->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            // Unique constraint: one record per plant per month per year
            $table->unique(['plant_id', 'year', 'month']);

            // Indexes for performance
            $table->index(['year', 'month']);
            $table->index('plant_id');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_claims');
    }
};
