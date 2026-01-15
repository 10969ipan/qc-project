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
        Schema::table('sortir_checksheets', function (Blueprint $table) {
            // Change source_type to string to handle cross_cut without enum constraints
            $table->string('source_type', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sortir_checksheets', function (Blueprint $table) {
            $table->enum('source_type', ['sub_assy', 'in_process'])->change();
        });
    }
};
