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
<<<<<<< Updated upstream
            'name' => 'Bakmi',
        ]);

        Category::create([
            'name' => 'Snack',
=======
            'name' => 'Menu Biasa',
        ]);

        Category::create([
            'name' => 'Menu Spesial',
>>>>>>> Stashed changes
        ]);

        Category::create([
            'name' => 'Minuman',
        ]);
    }
}
