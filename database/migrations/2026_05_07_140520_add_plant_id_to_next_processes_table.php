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
        Schema::table('next_processes', function (Blueprint $table) {
            $table->uuid('plant_id')->nullable()->after('id');
            $table->foreign('plant_id')->references('id')->on('plants')->onDelete('cascade');
            
            // Drop existing unique constraint on name
            $table->dropUnique(['name']);
            
            // Add composite unique constraint
            $table->unique(['name', 'plant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('next_processes', function (Blueprint $table) {
            $table->dropUnique(['name', 'plant_id']);
            $table->dropForeign(['plant_id']);
            $table->dropColumn('plant_id');
            $table->unique('name');
        });
    }
};
