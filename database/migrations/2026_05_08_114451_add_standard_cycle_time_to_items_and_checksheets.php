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
        Schema::table('items', function (Blueprint $table) {
            $table->decimal('standard_cycle_time', 8, 2)->nullable()->after('sap_code');
        });

        Schema::table('plating_checksheets', function (Blueprint $table) {
            $table->decimal('standard_cycle_time', 8, 2)->nullable()->after('cycle_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('standard_cycle_time');
        });

        Schema::table('plating_checksheets', function (Blueprint $table) {
            $table->dropColumn('standard_cycle_time');
        });
    }
};
