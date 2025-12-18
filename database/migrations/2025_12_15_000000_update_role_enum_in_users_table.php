<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Because modifying ENUMs in some databases (like MySQL/MariaDB) requires raw SQL 
        // or installing doctrine/dbal (which we might not have), we will use a raw statement.
        // We are adding 'kashift' and 'asst_manager' to the existing list: 'admin', 'supervisor', 'inspector'.
        
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'supervisor', 'inspector', 'kashift', 'asst_manager') DEFAULT 'inspector'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original list
        // Note: If there are users with 'kashift' or 'asst_manager', this might fail or truncate data depending on strict mode.
        // We generally assume this won't be reversed in production with live data without data migration.
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'supervisor', 'inspector') DEFAULT 'inspector'");
    }
};
