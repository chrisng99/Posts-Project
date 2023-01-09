<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Create 5 fake categories
        for ($i = 0; $i < 5; $i++) {
            Category::create(['name' => fake()->word()]);
        }
    }
}