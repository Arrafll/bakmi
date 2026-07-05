<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Category::create([
            'name' => 'Menu Biasa',
        ]);

        Category::create([
            'name' => 'Menu Spesial',
        ]);

        Category::create([
            'name' => 'Minuman',
        ]);
    }
}
