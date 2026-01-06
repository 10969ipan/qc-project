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
        // Panggil UserSeeder untuk mengisi data pengguna
        $this->call(UserSeeder::class);

        // Panggil seeder lainnya
        // ItemSeeder dihapus - data master item dikelola via admin panel
        $this->call(ItemDefectSeeder::class);
    }
}
