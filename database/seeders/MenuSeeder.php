<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            [
                'name' => 'Bakmi Ayam Original',
                'description' => 'Mie kuning dengan topping ayam suwir berbumbu, pangsit rebus, dan tauge segar.',
                'price' => 25000,
                'category' => 'bakmi',
                'is_available' => true,
            ],
            [
                'name' => 'Bakmi Ayam Spesial',
                'description' => 'Bakmi ayam dengan tambahan ceker, telur asin, dan jamur kuping.',
                'price' => 32000,
                'category' => 'bakmi',
                'is_available' => true,
            ],
            [
                'name' => 'Bakmi Goreng',
                'description' => 'Mie goreng wok dengan kecap manis, sayuran, dan ayam cincang.',
                'price' => 28000,
                'category' => 'bakmi',
                'is_available' => true,
            ],
            [
                'name' => 'Pangsit Goreng',
                'description' => 'Pangsit renyah dengan isian udang dan daging ayam, disajikan dengan sambal.',
                'price' => 18000,
                'category' => 'snack',
                'is_available' => true,
            ],
            [
                'name' => 'Pangsit Rebus',
                'description' => 'Pangsit lembut berbumbu kaldu ayam yang gurih dan segar.',
                'price' => 15000,
                'category' => 'snack',
                'is_available' => true,
            ],
            [
                'name' => 'Es Teh Manis',
                'description' => 'Teh hitam segar dengan es batu dan gula pilihan.',
                'price' => 8000,
                'category' => 'minuman',
                'is_available' => true,
            ],
            [
                'name' => 'Es Jeruk',
                'description' => 'Jeruk peras segar dingin, menyegarkan di setiap tegukan.',
                'price' => 10000,
                'category' => 'minuman',
                'is_available' => true,
            ],
            [
                'name' => 'Bakmi Kuah Sapi',
                'description' => 'Mie dengan kaldu sapi kaya rempah, daging sapi empuk, dan daun bawang.',
                'price' => 38000,
                'category' => 'bakmi',
                'is_available' => false,
            ],
        ];

        foreach ($menus as $menu) {
            Menu::create($menu);
        }
    }
}
