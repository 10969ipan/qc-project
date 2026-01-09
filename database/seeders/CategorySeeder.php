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
        $categories = [
            ['name' => 'Sub Assy'],
            ['name' => 'Inprosess'],
            ['name' => 'Cross Cut Plating'],
            ['name' => 'Cross Cut Painting'],
        ];

        foreach ($categories as $category) {
            \App\Models\Category::create($category);
        }
    }
}
