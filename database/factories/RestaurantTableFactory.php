<?php

namespace Database\Factories;

use App\Models\RestaurantTable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RestaurantTableFactory extends Factory
{
    protected $model = RestaurantTable::class;

    public function definition(): array
    {
        static $counter = 0;
        $counter++;

        return [
            'name'      => 'Table ' . $counter,
            'qr_token'  => Str::random(40),
            'branch'    => 'main',
            'is_active' => true,
        ];
    }
}
