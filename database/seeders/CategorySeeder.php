<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoryNames = [
            'Sub Assy',
            'Inprosess',
            'Cross Cut Plating',
            'Cross Cut Painting',
            'Incoming Part',
            'Incoming Material',
            'Incoming Sub-Part',
            'Incoming Export',
            'Incoming Chemical',
        ];

        $plants = \App\Models\Plant::all();

        foreach ($plants as $plant) {
            foreach ($categoryNames as $name) {
                \App\Models\Category::firstOrCreate([
                    'plant_id' => $plant->id,
                    'name' => $name,
                ]);
            }
        }
    }
}
