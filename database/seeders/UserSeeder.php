<?php

namespace Database\Seeders;

// To run this seeder independently, use the command:
// php artisan db:seed --class=UserSeeder

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Buat Akun Admin
        User::updateOrCreate(
            ['email' => 'admin@qc.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ]
        );

        // Buat Akun Supervisor
        User::updateOrCreate(
            ['email' => 'spvqa@qc.com'],
            [
                'name' => 'Mida Herdiyani',
                'password' => Hash::make('indoplat2526'),
                'role' => 'supervisor',
            ]
        );

        User::updateOrCreate(
            ['email' => 'spvqc@qc.com'],
            [
                'name' => 'Arief Hidayat',
                'password' => Hash::make('indoplat2526'),
                'role' => 'supervisor',
            ]
        );

        // Buat Akun Inspector
        User::updateOrCreate(
            ['email' => 'irfan@qc.com'],
            [
                'name' => 'Irfan Arfian Kusnadi',
                'password' => Hash::make('indoplat2526'),
                'role' => 'inspector',
            ]
        );

        User::updateOrCreate(
            ['email' => 'anggi@qc.com'],
            [
                'name' => 'Anggi Purnama',
                'password' => Hash::make('indoplat2526'),
                'role' => 'inspector',
            ]
        );

        User::updateOrCreate(
            ['email' => 'gugun@qc.com'],
            [
                'name' => 'Gugun Kurniadi',
                'password' => Hash::make('indoplat2526'),
                'role' => 'inspector',
            ]
        );

        User::updateOrCreate(
            ['email' => 'dede@qc.com'],
            [
                'name' => 'Dede Supriyadi',
                'password' => Hash::make('indoplat2526'),
                'role' => 'inspector',
            ]
        );

        User::updateOrCreate(
            ['email' => 'arga@qc.com'],
            [
                'name' => 'Arga Yudistira',
                'password' => Hash::make('indoplat2526'),
                'role' => 'inspector',
            ]
        );

        User::updateOrCreate(
            ['email' => 'sopian@qc.com'],
            [
                'name' => 'Sopian Handani',
                'password' => Hash::make('indoplat2526'),
                'role' => 'inspector',
            ]
        );

        User::updateOrCreate(
            ['email' => 'yono@qc.com'],
            [
                'name' => 'Yono Supriatno',
                'password' => Hash::make('indoplat2526'),
                'role' => 'inspector',
            ]
        );

        User::updateOrCreate(
            ['email' => 'dinar@qc.com'],
            [
                'name' => 'Dinar Ashobar',
                'password' => Hash::make('indoplat2526'),
                'role' => 'inspector',
            ]
        );

        // Buat Akun Ka. Shift
        User::updateOrCreate(
            ['email' => 'kashift@qc.com'],
            [
                'name' => 'Ahmad Jaeni',
                'password' => Hash::make('indoplat2526'),
                'role' => 'kashift',
            ]
        );

        // Buat Akun Asst. Manager
        User::updateOrCreate(
            ['email' => 'manager@qc.com'],
            [
                'name' => 'Iwan Setiawan',
                'password' => Hash::make('indoplat2526'),
                'role' => 'asst_manager',
            ]
        );

        // Buat Akun Manager
        User::updateOrCreate(
            ['email' => 'generalmanager@qc.com'],
            [
                'name' => 'Desti Kurniasari',
                'password' => Hash::make('indoplat2526'),
                'role' => 'manager',
            ]
        );

        
    }
}
