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
        // Users are now updated or created, no need to truncate.
        
        // Buat Akun Admin
        User::updateOrCreate(
            ['email' => 'admin@qc.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // Buat Akun Supervisor
        User::updateOrCreate(
            ['email' => 'supervisor@qc.com'],
            [
                'name' => 'Ms Mida',
                'password' => Hash::make('password'),
                'role' => 'supervisor',
            ]
        );

        User::updateOrCreate(
            ['email' => 'supervisor2@qc.com'],
            [
                'name' => 'Mr Arif Hidayat',
                'password' => Hash::make('password'),
                'role' => 'supervisor',
            ]
        );

        // Buat Akun Inspector
        User::updateOrCreate(
            ['email' => 'inspector@qc.com'],
            [
                'name' => 'Irfan Arfian Kusnadi',
                'password' => Hash::make('password'),
                'role' => 'inspector',
            ]
        );

        User::updateOrCreate(
            ['email' => 'anggi@qc.com'],
            [
                'name' => 'Anggi Purnama',
                'password' => Hash::make('password'),
                'role' => 'inspector',
            ]
        );

        User::updateOrCreate(
            ['email' => 'gugun@qc.com'],
            [
                'name' => 'Gugun Kurniadi',
                'password' => Hash::make('password'),
                'role' => 'inspector',
            ]
        );

        User::updateOrCreate(
            ['email' => 'dede@qc.com'],
            [
                'name' => 'Dede Supriyadi',
                'password' => Hash::make('password'),
                'role' => 'inspector',
            ]
        );

        User::updateOrCreate(
            ['email' => 'arga@qc.com'],
            [
                'name' => 'Arga Yudistira',
                'password' => Hash::make('password'),
                'role' => 'inspector',
            ]
        );

        // Buat Akun Ka. Shift
        User::updateOrCreate(
            ['email' => 'kashift@qc.com'],
            [
                'name' => 'Ka Shift',
                'password' => Hash::make('password'),
                'role' => 'kashift',
            ]
        );

        // Buat Akun Asst. Manager
        User::updateOrCreate(
            ['email' => 'manager@qc.com'],
            [
                'name' => 'Asst Manager User',
                'password' => Hash::make('password'),
                'role' => 'asst_manager',
            ]
        );

        // Buat Akun Manager
        User::updateOrCreate(
            ['email' => 'generalmanager@qc.com'],
            [
                'name' => 'Manager',
                'password' => Hash::make('password'),
                'role' => 'manager',
            ]
        );

        // Panggil ItemSeeder
        $this->call(ItemSeeder::class);
        $this->call(ItemDefectSeeder::class);
    }
}
