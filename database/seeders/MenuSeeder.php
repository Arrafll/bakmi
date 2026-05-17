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
                'image' => 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=600&auto=format',
                'category' => 'bakmi',
                'is_available' => true,
            ],
            [
                'name' => 'Bakmi Ayam Spesial',
                'description' => 'Bakmi ayam dengan tambahan ceker, telur asin, dan jamur kuping.',
                'price' => 32000,
                'image' => 'https://images.unsplash.com/photo-1555126634-323283e090fa?w=600&auto=format',
                'category' => 'bakmi',
                'is_available' => true,
            ],
            [
                'name' => 'Bakmi Goreng',
                'description' => 'Mie goreng wok dengan kecap manis, sayuran, dan ayam cincang.',
                'price' => 28000,
                'image' => 'https://images.unsplash.com/photo-1585032226651-759b368d7246?w=600&auto=format',
                'category' => 'bakmi',
                'is_available' => true,
            ],
            [
                'name' => 'Pangsit Goreng',
                'description' => 'Pangsit renyah dengan isian udang dan daging ayam, disajikan dengan sambal.',
                'price' => 18000,
                'image' => 'https://images.unsplash.com/photo-1612929633738-8fe44f7ec841?w=600&auto=format',
                'category' => 'snack',
                'is_available' => true,
            ],
            [
                'name' => 'Pangsit Rebus',
                'description' => 'Pangsit lembut berbumbu kaldu ayam yang gurih dan segar.',
                'price' => 15000,
                'image' => 'https://images.unsplash.com/photo-1569050467447-ce54b3bbc37d?w=600&auto=format',
                'category' => 'snack',
                'is_available' => true,
            ],
            [
                'name' => 'Es Teh Manis',
                'description' => 'Teh hitam segar dengan es batu dan gula pilihan.',
                'price' => 8000,
                'image' => 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=600&auto=format',
                'category' => 'minuman',
                'is_available' => true,
            ],
            [
                'name' => 'Es Jeruk',
                'description' => 'Jeruk peras segar dingin, menyegarkan di setiap tegukan.',
                'price' => 10000,
                'image' => 'https://images.unsplash.com/photo-1621506289937-a8e4df240d0b?w=600&auto=format',
                'category' => 'minuman',
                'is_available' => true,
            ],
            [
                'name' => 'Bakmi Kuah Sapi',
                'description' => 'Mie dengan kaldu sapi kaya rempah, daging sapi empuk, dan daun bawang.',
                'price' => 38000,
                'image' => 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=600&auto=format',
                'category' => 'bakmi',
                'is_available' => false,
            ],
        ];

        foreach ($menus as $menu) {
            Menu::create($menu);
        }
    }
}
