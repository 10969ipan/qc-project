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
        if (DB::getDriverName() === 'sqlite') {
            // SQLite does not support MODIFY COLUMN.
            // We must recreate the table to update the CHECK constraint for the ENUM.
            // Based on analyzing existing migrations, the users table has:
            // id, name, email, email_verified_at, password, remember_token, timestamps, role
            
            Schema::disableForeignKeyConstraints();

            Schema::create('users_temp', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email'); // Constraint will be added separately or implicitly handled if needed, but uniqueness is tricky in temp tables if indexes conflict
                $table->enum('role', ['admin', 'supervisor', 'inspector', 'kashift', 'asst_manager', 'manager'])
                      ->default('inspector');
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
                
                // Explicitly name the unique index to avoid collisions if auto-naming is the issue,
                // or rely on the table rename. 
                // However, the error says "index users_temp_email_unique already exists".
                // This suggests the migration might have run partially or the index name logic is conflicting.
                // We will add the unique constraint *after* ensuring it doesn't exist, or just use the column definition.
                // The issue is likely that `string('email')->unique()` creates an index named `users_temp_email_unique`.
                // If the migration runs multiple times (like in tests), it might be lingering?
                // Actually, RefreshDatabase usually handles this.
                // Let's try explicitly defining it.
                $table->unique('email', 'users_temp_email_unique_fixed');
            });

            // Copy data
            $users = DB::table('users')->get();
            foreach ($users as $user) {
                DB::table('users_temp')->insert((array) $user);
            }

            Schema::drop('users');
            Schema::rename('users_temp', 'users');
            
            Schema::enableForeignKeyConstraints();
        } else {
            // For MySQL/MariaDB, use raw SQL to modify the ENUM column.
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'supervisor', 'inspector', 'kashift', 'asst_manager', 'manager') DEFAULT 'inspector'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::disableForeignKeyConstraints();

            Schema::create('users_temp', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                // Keep the extended enum list to prevent data loss on rollback
                $table->enum('role', ['admin', 'supervisor', 'inspector', 'kashift', 'asst_manager', 'manager'])
                      ->default('inspector');
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
            });

            $users = DB::table('users')->get();
            foreach ($users as $user) {
                DB::table('users_temp')->insert((array) $user);
            }

            Schema::drop('users');
            Schema::rename('users_temp', 'users');

            Schema::enableForeignKeyConstraints();
        } else {
            // Keep the extended enum list to prevent data loss on rollback
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'supervisor', 'inspector', 'kashift', 'asst_manager', 'manager') DEFAULT 'inspector'");
        }
    }
};
