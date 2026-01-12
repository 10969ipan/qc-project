<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the status enum to include 'trouble'
        DB::statement("ALTER TABLE machine_statuses MODIFY COLUMN status ENUM('normal', 'maintenance', 'stopped', 'trouble') DEFAULT 'normal'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE machine_statuses MODIFY COLUMN status ENUM('normal', 'maintenance', 'stopped') DEFAULT 'normal'");
    }
};
