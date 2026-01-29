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
        Schema::table('cross_cut_checksheets', function (Blueprint $table) {
            $table->renameColumn('chemical_copper', 'chemical_catalyst');
            $table->dropColumn(['chemical_nikel', 'chemical_eching']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cross_cut_checksheets', function (Blueprint $table) {
            $table->renameColumn('chemical_catalyst', 'chemical_copper');
            $table->string('chemical_nikel')->nullable();
            $table->string('chemical_eching')->nullable();
        });
    }
};
