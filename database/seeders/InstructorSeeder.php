<?php

namespace Database\Seeders;

use App\Models\Instructor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InstructorSeeder extends Seeder
{
    public function run(): void
    {
        $instructors = [
            [
                'name' => 'Dr. Ahmad Pratama',
                'headline' => 'Ex-Principal Data Scientist @ Tokopedia',
                'bio' => 'Berpengalaman lebih dari 10 tahun membangun sistem feature store dan model prediktif skala besar di industri e-commerce.',
            ],
            [
                'name' => 'Rina Wijayanti',
                'headline' => 'Machine Learning Engineer @ Gojek',
                'bio' => 'Fokus pada penerapan model machine learning untuk optimasi logistik dan sistem rekomendasi real-time.',
            ],
            [
                'name' => 'Budi Santoso',
                'headline' => 'Senior Full-Stack Developer & Bootcamp Instructor',
                'bio' => 'Membangun puluhan aplikasi web produksi dengan Laravel dan JavaScript modern, aktif membimbing developer pemula.',
            ],
            [
                'name' => 'Dr. Siti Nurhaliza',
                'headline' => 'Dosen & Peneliti Artificial Intelligence, Universitas Indonesia',
                'bio' => 'Meneliti model bahasa dan etika kecerdasan buatan, aktif mempublikasikan riset di jurnal internasional.',
            ],
            [
                'name' => 'Dedi Kurniawan',
                'headline' => 'Lead Data Analyst @ Bank Digital Nasional',
                'bio' => 'Membangun dashboard dan pipeline analitik untuk mendukung keputusan bisnis berbasis data di sektor keuangan.',
            ],
            [
                'name' => 'Yulia Permatasari',
                'headline' => 'UX Researcher & Digital Literacy Advocate',
                'bio' => 'Mendampingi pendidik dan komunitas dalam meningkatkan kecakapan digital melalui pelatihan yang aplikatif.',
            ],
            [
                'name' => 'Farhan Hidayat',
                'headline' => 'Backend Engineer & Mentor Karier Teknologi',
                'bio' => 'Membimbing puluhan software engineer junior menuju posisi senior melalui mentoring karier terstruktur.',
            ],
        ];

        foreach ($instructors as $instructor) {
            Instructor::updateOrCreate(
                ['name' => $instructor['name']],
                [
                    'slug' => Str::slug($instructor['name']),
                    'headline' => $instructor['headline'],
                    'bio' => $instructor['bio'],
                ]
            );
        }
    }
}
