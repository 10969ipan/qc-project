<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin',
            'email' => 'admin@qc.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        echo "Admin user created successfully!\n";
        echo "Email: admin@qc.com\n";
        echo "Password: admin123\n";
    }
}
