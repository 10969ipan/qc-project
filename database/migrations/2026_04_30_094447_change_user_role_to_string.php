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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 50)->default('inspector')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Reverting to the last known enum state
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('global','total','admin', 'supervisor', 'inspector', 'kashift', 'asst_manager', 'manager', 'karu_qc', 'kashift_plating', 'supervisor_plating', 'manager_plating', 'oshef') NOT NULL DEFAULT 'inspector'");
        });
    }
};
