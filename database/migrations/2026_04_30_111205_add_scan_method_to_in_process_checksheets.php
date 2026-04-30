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
        Schema::table('in_process_checksheets', function (Blueprint $table) {
            $table->string('scan_method')->default('manual')->after('sap_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('in_process_checksheets', function (Blueprint $table) {
            $table->dropColumn('scan_method');
        });
    }
};
