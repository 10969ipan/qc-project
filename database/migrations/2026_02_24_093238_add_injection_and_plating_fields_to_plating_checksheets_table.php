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
        Schema::table('plating_checksheets', function (Blueprint $table) {
            $table->date('injection_date')->nullable()->after('item_id');
            $table->string('injection_shift')->nullable()->after('injection_date');
            $table->date('plating_date')->nullable()->after('injection_shift');
            $table->string('plating_shift')->nullable()->after('plating_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plating_checksheets', function (Blueprint $table) {
            $table->dropColumn(['injection_date', 'injection_shift', 'plating_date', 'plating_shift']);
        });
    }
};
