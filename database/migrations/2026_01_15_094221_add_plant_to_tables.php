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
        // Add plant column to users table
        Schema::table('users', function (Blueprint $table) {
            $table->enum('plant', ['karawang', 'jakarta'])->default('karawang')->after('role');
        });

        // Add plant column to checksheets table
        Schema::table('checksheets', function (Blueprint $table) {
            $table->enum('plant', ['karawang', 'jakarta'])->default('karawang')->after('id');
            $table->index('plant');
        });

        // Add plant column to in_process_checksheets table
        Schema::table('in_process_checksheets', function (Blueprint $table) {
            $table->enum('plant', ['karawang', 'jakarta'])->default('karawang')->after('id');
            $table->index('plant');
        });

        // Add plant column to cross_cut_checksheets table
        Schema::table('cross_cut_checksheets', function (Blueprint $table) {
            $table->enum('plant', ['karawang', 'jakarta'])->default('karawang')->after('id');
            $table->index('plant');
        });

        // Add plant column to sortir_checksheets table
        Schema::table('sortir_checksheets', function (Blueprint $table) {
            $table->enum('plant', ['karawang', 'jakarta'])->default('karawang')->after('id');
            $table->index('plant');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('plant');
        });

        Schema::table('checksheets', function (Blueprint $table) {
            $table->dropIndex(['plant']);
            $table->dropColumn('plant');
        });

        Schema::table('in_process_checksheets', function (Blueprint $table) {
            $table->dropIndex(['plant']);
            $table->dropColumn('plant');
        });

        Schema::table('cross_cut_checksheets', function (Blueprint $table) {
            $table->dropIndex(['plant']);
            $table->dropColumn('plant');
        });

        Schema::table('sortir_checksheets', function (Blueprint $table) {
            $table->dropIndex(['plant']);
            $table->dropColumn('plant');
        });
    }
};
