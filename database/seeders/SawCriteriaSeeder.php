<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\SawCriteria;
use App\Models\SawMenuScore;
use Illuminate\Database\Seeder;

class SawCriteriaSeeder extends Seeder
{
    public function run(): void
    {
        $criteria = [
            [
                'key'         => 'price',
                'name'        => 'Harga',
                'description' => 'Harga menu (semakin murah semakin baik)',
                'type'        => 'cost',
                'weight'      => 0.25,
                'order'       => 1,
            ],
            [
                'key'         => 'taste',
                'name'        => 'Rasa',
                'description' => 'Penilaian cita rasa menu (skala 1–10)',
                'type'        => 'benefit',
                'weight'      => 0.25,
                'order'       => 2,
            ],
            [
                'key'         => 'popularity',
                'name'        => 'Popularitas',
                'description' => 'Jumlah pesanan menu (dihitung otomatis dari data penjualan)',
                'type'        => 'benefit',
                'weight'      => 0.20,
                'order'       => 3,
            ],
            [
                'key'         => 'portion',
                'name'        => 'Porsi',
                'description' => 'Ukuran porsi / kesesuaian dengan harga (skala 1–10)',
                'type'        => 'benefit',
                'weight'      => 0.10,
                'order'       => 4,
            ],
            [
                'key'         => 'preparation_time',
                'name'        => 'Waktu Penyajian',
                'description' => 'Lama waktu persiapan – semakin cepat semakin baik (skala 1–10, 1=cepat)',
                'type'        => 'cost',
                'weight'      => 0.10,
                'order'       => 5,
            ],
            [
                'key'         => 'presentation',
                'name'        => 'Tampilan Menu',
                'description' => 'Estetika penyajian makanan (skala 1–10)',
                'type'        => 'benefit',
                'weight'      => 0.10,
                'order'       => 6,
            ],
        ];

        foreach ($criteria as $item) {
            SawCriteria::updateOrCreate(['key' => $item['key']], $item);
        }

        // Default scores per menu for manual criteria (taste, portion, preparation_time, presentation)
        $defaultScores = [
            'Bakmi Ayam Original' => [
                'taste'            => 8,
                'portion'          => 7,
                'preparation_time' => 3,
                'presentation'     => 7,
            ],
            'Bakmi Ayam Spesial' => [
                'taste'            => 9,
                'portion'          => 8,
                'preparation_time' => 4,
                'presentation'     => 9,
            ],
            'Bakmi Goreng' => [
                'taste'            => 8,
                'portion'          => 8,
                'preparation_time' => 4,
                'presentation'     => 8,
            ],
            'Pangsit Goreng' => [
                'taste'            => 7,
                'portion'          => 6,
                'preparation_time' => 3,
                'presentation'     => 7,
            ],
            'Pangsit Rebus' => [
                'taste'            => 7,
                'portion'          => 6,
                'preparation_time' => 2,
                'presentation'     => 6,
            ],
            'Es Teh Manis' => [
                'taste'            => 8,
                'portion'          => 8,
                'preparation_time' => 1,
                'presentation'     => 7,
            ],
            'Es Jeruk' => [
                'taste'            => 8,
                'portion'          => 8,
                'preparation_time' => 2,
                'presentation'     => 8,
            ],
            'Bakmi Kuah Sapi' => [
                'taste'            => 9,
                'portion'          => 9,
                'preparation_time' => 5,
                'presentation'     => 9,
            ],
        ];

        $menus = Menu::all()->keyBy('name');

        foreach ($defaultScores as $menuName => $scores) {
            $menu = $menus->get($menuName);
            if (! $menu) {
                continue;
            }

            foreach ($scores as $key => $score) {
                SawMenuScore::updateOrCreate(
                    ['menu_id' => $menu->id, 'criteria_key' => $key],
                    ['score' => $score]
                );
            }
        }
    }
}
