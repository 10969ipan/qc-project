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
        Schema::create('thickness_standards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('plant_id')->nullable()->index();
            
            $table->string('part_name');
            $table->string('customer')->nullable();
            $table->string('standard_code')->nullable(); // e.g. R4, R2
            $table->string('standard_name')->nullable(); // e.g. HES D 6001-04A
            
            // Standards can be exact numbers or ranges, store as string to be safe or float if exact
            // Since judgment is >= we can store as string or decimal. Let's use string to allow "120-160" for Step Test if needed,
            // but for Cr, Ni, Cu the user said it's minimum, so float is better for calculation.
            $table->decimal('thickness_cu_std', 8, 2)->nullable();
            $table->decimal('thickness_ni_std', 8, 2)->nullable();
            $table->decimal('thickness_cr_std', 8, 2)->nullable();
            
            $table->string('corrodkote')->nullable();
            $table->string('cass_test')->nullable();
            $table->string('salt_spray_test')->nullable();
            $table->string('porecount_test')->nullable();
            $table->string('cross_cut_test')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thickness_standards');
    }
};
