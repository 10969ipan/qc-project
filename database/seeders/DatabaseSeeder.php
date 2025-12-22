<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Truncate users table to avoid duplicate entry errors during re-seeding
        DB::table('users')->truncate();

        // Buat Akun Admin
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@qc.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Buat Akun Supervisor
        User::create([
            'name' => 'Ms Mida',
            'email' => 'supervisor@qc.com',
            'password' => Hash::make('password'),
            'role' => 'supervisor',
        ]);

        // Buat Akun Inspector
        User::create([
            'name' => 'Irfan Arfian Kusnadi',
            'email' => 'inspector@qc.com',
            'password' => Hash::make('password'),
            'role' => 'inspector',
        ]);

        User::create([
            'name' => 'Anggi Purnama',
            'email' => 'anggi@qc.com',
            'password' => Hash::make('password'),
            'role' => 'inspector',
        ]);

        User::create([
            'name' => 'Gugun Kurniadi',
            'email' => 'gugun@qc.com',
            'password' => Hash::make('password'),
            'role' => 'inspector',
        ]);

        User::create([
            'name' => 'Dede Supriyadi',
            'email' => 'dede@qc.com',
            'password' => Hash::make('password'),
            'role' => 'inspector',
        ]);

        User::create([
            'name' => 'Arga Yudistira',
            'email' => 'arga@qc.com',
            'password' => Hash::make('password'),
            'role' => 'inspector',
        ]);

        // Buat Akun Ka. Shift
        User::create([
            'name' => 'Ka Shift User',
            'email' => 'kashift@qc.com',
            'password' => Hash::make('password'),
            'role' => 'kashift',
        ]);

        // Buat Akun Asst. Manager
        User::create([
            'name' => 'Asst Manager User',
            'email' => 'manager@qc.com',
            'password' => Hash::make('password'),
            'role' => 'asst_manager',
        ]);

        // Panggil ItemSeeder
        $this->call(ItemSeeder::class);
        $this->call(ItemDefectSeeder::class);
    }
}
