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
        Schema::table('plating_cabut_records', function (Blueprint $table) {
            $table->string('inisial_cabut')->nullable()->after('qty_original');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plating_cabut_records', function (Blueprint $table) {
            $table->dropColumn('inisial_cabut');
        });
    }
};
