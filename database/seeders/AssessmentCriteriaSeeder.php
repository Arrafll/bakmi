<?php

namespace Database\Seeders;

use App\Models\AssessmentCriterion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AssessmentCriteriaSeeder extends Seeder
{
    public function run(): void
    {
        $criteria = [
            [
                'name' => 'Harga',
                'direction' => 'cost',
                'weight' => 20,
                'scale_labels' => ['Sangat Murah', 'Murah', 'Sedang', 'Mahal', 'Sangat Mahal'],
                'sort_order' => 1,
            ],
            [
                'name' => 'Rasa',
                'direction' => 'benefit',
                'weight' => 30,
                'scale_labels' => ['Tidak Enak', 'Kurang Enak', 'Cukup Enak', 'Enak', 'Sangat Enak'],
                'sort_order' => 2,
            ],
            [
                'name' => 'Popularitas',
                'direction' => 'benefit',
                'weight' => 20,
                'scale_labels' => ['Sangat Tidak Populer', 'Tidak Populer', 'Cukup Populer', 'Populer', 'Sangat Populer'],
                'sort_order' => 3,
            ],
            [
                'name' => 'Porsi',
                'direction' => 'benefit',
                'weight' => 10,
                'scale_labels' => ['Sangat Tidak Kenyang', 'Tidak Kenyang', 'Cukup Kenyang', 'Kenyang', 'Sangat Kenyang'],
                'sort_order' => 4,
            ],
            [
                'name' => 'Waktu Penyajian',
                'direction' => 'cost',
                'weight' => 10,
                'scale_labels' => ['Sangat Cepat', 'Cepat', 'Sedang', 'Lama', 'Sangat Lama'],
                'sort_order' => 5,
            ],
            [
                'name' => 'Tampilan Menu',
                'direction' => 'benefit',
                'weight' => 10,
                'scale_labels' => ['Tidak Menarik', 'Kurang Menarik', 'Cukup Menarik', 'Menarik', 'Sangat Menarik'],
                'sort_order' => 6,
            ],
        ];

        foreach ($criteria as $criterion) {
            AssessmentCriterion::updateOrCreate(
                ['slug' => Str::slug($criterion['name'])],
                [
                    'name' => $criterion['name'],
                    'direction' => $criterion['direction'],
                    'weight' => $criterion['weight'],
                    'scale_labels' => $criterion['scale_labels'],
                    'sort_order' => $criterion['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
