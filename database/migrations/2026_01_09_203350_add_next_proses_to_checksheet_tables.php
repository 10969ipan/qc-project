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
        Schema::table('checksheets', function (Blueprint $table) {
            $table->string('next_proses', 50)->nullable()->after('remarks');
        });

        Schema::table('in_process_checksheets', function (Blueprint $table) {
            $table->string('next_proses', 50)->nullable()->after('remarks');
        });

        Schema::table('cross_cut_checksheets', function (Blueprint $table) {
            $table->string('next_proses', 50)->nullable()->after('keterangan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checksheets', function (Blueprint $table) {
            $table->dropColumn('next_proses');
        });

        Schema::table('in_process_checksheets', function (Blueprint $table) {
            $table->dropColumn('next_proses');
        });

        Schema::table('cross_cut_checksheets', function (Blueprint $table) {
            $table->dropColumn('next_proses');
        });
    }
};
