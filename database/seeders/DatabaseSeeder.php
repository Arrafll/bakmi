<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(CategorySeeder::class);
        $this->call(MenuSeeder::class);
	$this->call(CategorySeeder::class);
        $this->call(UserSeeder::class);
<<<<<<< Updated upstream
        $this->call(SawCriteriaSeeder::class);
=======
        $this->call(AssessmentCriteriaSeeder::class);
>>>>>>> Stashed changes
    }
}
