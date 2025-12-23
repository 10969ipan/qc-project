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
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        // Buat Akun Supervisor
        User::create([
            'name' => 'Ms Mida',
            'email' => 'spvqa@qc.com',
            'password' => Hash::make('indoplat2526'),
            'role' => 'supervisor',
        ]);

        User::create([
            'name' => 'Mr Arif Hidayat',
            'email' => 'spvqc@qc.com',
            'password' => Hash::make('indoplat2526'),
            'role' => 'supervisor',
        ]);

        // Buat Akun Inspector
        User::create([
            'name' => 'Irfan Arfian Kusnadi',
            'email' => 'irfan@qc.com',
            'password' => Hash::make('indoplat2526'),
            'role' => 'inspector',
        ]);

        User::create([
            'name' => 'Anggi Purnama',
            'email' => 'anggi@qc.com',
            'password' => Hash::make('indoplat2526'),
            'role' => 'inspector',
        ]);

        User::create([
            'name' => 'Gugun Kurniadi',
            'email' => 'gugun@qc.com',
            'password' => Hash::make('indoplat2526'),
            'role' => 'inspector',
        ]);

        User::create([
            'name' => 'Dede Supriyadi',
            'email' => 'dede@qc.com',
            'password' => Hash::make('indoplat2526'),
            'role' => 'inspector',
        ]);

        User::create([
            'name' => 'Arga Yudistira',
            'email' => 'arga@qc.com',
            'password' => Hash::make('indoplat2526'),
            'role' => 'inspector',
        ]);

        User::create([
            'name' => 'Sopian Hamdani',
            'email' => 'sopian@qc.com',
            'password' => Hash::make('indoplat2526'),
            'role' => 'inspector',
        ]);

        User::create([
            'name' => 'Yono Supriyanto',
            'email' => 'yono@qc.com',
            'password' => Hash::make('indoplat2526'),
            'role' => 'inspector',
        ]);

        User::create([
            'name' => 'Dinar Ashobar',
            'email' => 'dinar@qc.com',
            'password' => Hash::make('indoplat2526'),
            'role' => 'inspector',
        ]);

        // Buat Akun Ka. Shift
        User::create([
            'name' => 'Mr Ahmad Jaeni',
            'email' => 'kashift@qc.com',
            'password' => Hash::make('indoplat2526'),
            'role' => 'kashift',
        ]);

        // Buat Akun Asst. Manager
        User::create([
            'name' => 'Mr Iwan Setiawan',
            'email' => 'manager@qc.com',
            'password' => Hash::make('indoplat2526'),
            'role' => 'asst_manager',
        ]);

        // Buat Akun Manager
        User::create([
            'name' => 'Ms Desti Kurniasari',
            'email' => 'generalmanager@qc.com',
            'password' => Hash::make('indoplat2526'),
            'role' => 'manager',
        ]);

        // Panggil ItemSeeder
        $this->call(ItemSeeder::class);
        $this->call(ItemDefectSeeder::class);
    }
}
