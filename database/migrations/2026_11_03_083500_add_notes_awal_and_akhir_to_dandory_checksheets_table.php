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
        Schema::table('dandory_checksheets', function (Blueprint $table) {
            $table->string('notes_awal', 255)->nullable()->after('akhir_completed_at');
            $table->string('notes_akhir', 255)->nullable()->after('notes_awal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dandory_checksheets', function (Blueprint $table) {
            $table->dropColumn(['notes_awal', 'notes_akhir']);
        });
    }
};
