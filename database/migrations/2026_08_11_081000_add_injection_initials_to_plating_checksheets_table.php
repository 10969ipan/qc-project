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
            $table->string('injection_initials')->nullable()->after('injection_shift');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plating_checksheets', function (Blueprint $table) {
            $table->dropColumn('injection_initials');
        });
    }
};
