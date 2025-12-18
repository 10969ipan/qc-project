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
        Schema::table('checksheets', function (Blueprint $table) {
            $table->string('operator_initials')->nullable()->after('judgment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checksheets', function (Blueprint $table) {
            $table->dropColumn('operator_initials');
        });
    }
};
