<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Data Science',
                'icon' => 'analytics',
                'description' => 'Statistik terapan, pipeline data, dan tooling prediktif untuk mengubah data mentah menjadi keputusan.',
            ],
            [
                'name' => 'Artificial Intelligence',
                'icon' => 'psychology',
                'description' => 'Machine learning, deep learning, dan model generatif — dari konsep dasar hingga penerapan produksi.',
            ],
            [
                'name' => 'Web Development',
                'icon' => 'code',
                'description' => 'Pengembangan aplikasi web modern, dari front-end interaktif hingga API back-end yang skalabel.',
            ],
            [
                'name' => 'Programming',
                'icon' => 'terminal',
                'description' => 'Fondasi pemrograman, struktur data, dan praktik rekayasa perangkat lunak lintas bahasa.',
            ],
            [
                'name' => 'Data Analytics',
                'icon' => 'bar_chart',
                'description' => 'Analisis dan visualisasi data untuk mendukung pengambilan keputusan berbasis bukti.',
            ],
            [
                'name' => 'Digital Literacy',
                'icon' => 'public',
                'description' => 'Kecakapan digital dasar untuk pendidik, pelajar, dan profesional di era teknologi.',
            ],
            [
                'name' => 'Research Methodology',
                'icon' => 'science',
                'description' => 'Metodologi penelitian kuantitatif dan kualitatif untuk akademisi dan peneliti.',
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'icon' => $category['icon'],
                    'description' => $category['description'],
                    'status' => 'active',
                ]
            );
        }
    }
}
