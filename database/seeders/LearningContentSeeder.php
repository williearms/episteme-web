<?php

namespace Database\Seeders;

use App\Enums\ProductType;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Product;
use App\Models\ProductAccess;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Demo learning content. Everything here is placeholder: lesson text is generic,
 * join links point at example.com, and "downloads" are small text files.
 */
class LearningContentSeeder extends Seeder
{
    private const MODULE_TITLES = [
        'Pengantar dan Konsep Dasar',
        'Persiapan Lingkungan Kerja',
        'Teknik Inti',
        'Studi Kasus Industri',
        'Praktik Terapan',
        'Evaluasi dan Refleksi',
        'Proyek Akhir',
        'Langkah Selanjutnya',
    ];

    public function run(): void
    {
        $this->seedCourses();
        $this->seedEventAccess();
        $this->seedDigitalFiles();
    }

    private function seedCourses(): void
    {
        Course::with('product')->get()->each(function (Course $course) {
            if ($course->modules()->exists()) {
                return;
            }

            $moduleCount = max(1, $course->total_modules ?? 3);
            $lessonsPerModule = max(1, intdiv($course->total_lessons ?? $moduleCount * 3, $moduleCount));

            foreach (range(1, $moduleCount) as $m) {
                $module = CourseModule::create([
                    'course_id' => $course->id,
                    'title' => self::MODULE_TITLES[($m - 1) % count(self::MODULE_TITLES)],
                    'position' => $m,
                ]);

                foreach (range(1, $lessonsPerModule) as $l) {
                    $module->lessons()->create([
                        'title' => "Materi {$m}.{$l}",
                        'position' => $l,
                        'content' => "Materi {$m}.{$l} pada kelas {$course->product->title} membahas salah satu bagian dari modul \"{$module->title}\".\n\nBacalah ringkasan ini, coba terapkan pada contoh kasus Anda sendiri, lalu tandai selesai sebelum melanjutkan ke materi berikutnya.",
                    ]);
                }
            }
        });
    }

    private function seedEventAccess(): void
    {
        $liveTypes = [ProductType::Webinar, ProductType::Workshop, ProductType::Bootcamp, ProductType::Mentoring];

        Product::whereIn('product_type', $liveTypes)->get()->each(function (Product $product) {
            ProductAccess::firstOrCreate(['product_id' => $product->id], [
                'join_url' => 'https://meet.example.com/'.$product->slug,
                'instructions' => "Gunakan tautan di atas untuk bergabung 10 menit sebelum acara dimulai.\n\nPastikan koneksi internet stabil dan gunakan nama asli Anda saat masuk.",
            ]);
        });
    }

    private function seedDigitalFiles(): void
    {
        Product::where('product_type', ProductType::DigitalProduct)->with('digitalProduct')->get()->each(function (Product $product) {
            if (! $product->digitalProduct || $product->digitalProduct->file_path) {
                return;
            }

            $path = 'digital-products/'.Str::slug($product->title).'.txt';
            Storage::disk('local')->put($path, "{$product->title}\n\nBerkas contoh untuk produk digital ini (placeholder data seeding).\n");

            $product->digitalProduct->update(['file_path' => $path]);
        });
    }
}
