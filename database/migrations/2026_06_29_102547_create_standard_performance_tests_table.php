<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop old tables
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::dropIfExists('durability_plating_checksheets');
        Schema::dropIfExists('thickness_standards');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        Schema::create('standard_performance_tests', function (Blueprint $table) {
            $table->id();
            $table->string('part_name')->nullable();
            $table->string('customer_standard')->nullable();
            
            // Thickness (mµ)
            $table->string('thickness_cu')->nullable();
            $table->string('thickness_ni')->nullable();
            $table->string('thickness_cr')->nullable();
            $table->string('thickness_freq')->nullable();
            
            // Corrodkote
            $table->string('corrodkote_time')->nullable();
            $table->string('corrodkote_std_max_corrosion')->nullable();
            $table->string('corrodkote_freq')->nullable();
            
            // Cass Test
            $table->string('cass_time')->nullable();
            $table->string('cass_std_min_rn')->nullable();
            $table->string('cass_freq')->nullable();
            
            // Salt Spray Test
            $table->string('salt_spray_time')->nullable();
            $table->string('salt_spray_std_rusting')->nullable();
            $table->string('salt_spray_freq')->nullable();
            
            // Porecount Test (Min Porous)
            $table->string('porecount_std_min')->nullable();
            $table->string('porecount_freq')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('standard_performance_tests');
    }
};
