<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plant;

class TotalPlantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Plant::updateOrCreate(
            ['code' => 'total'],
            [
                'name' => 'TOTAL',
                'is_active' => true,
            ]
        );
    }
}
