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
            $table->string('module')->nullable()->after('plant_id');
            
            // Drop old unique index
            $table->dropUnique(['name', 'plant_id']);
            
            // Add new composite unique constraint
            $table->unique(['name', 'plant_id', 'module']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('next_processes', function (Blueprint $table) {
            $table->dropUnique(['name', 'plant_id', 'module']);
            $table->dropColumn('module');
            $table->unique(['name', 'plant_id']);
        });
    }
};
