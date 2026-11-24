<?php

namespace Tests\Feature\Learning;

use App\Enums\ProductType;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Enrollment;
use App\Models\Product;
use App\Models\User;
use App\Support\RichText;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LessonContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_formatting_is_kept(): void
    {
        $html = RichText::sanitize('<h2>Judul</h2><p>Teks <strong>tebal</strong> dan <em>miring</em></p><ul><li>satu</li></ul><blockquote>kutipan</blockquote><pre>kode</pre>');

        foreach (['<h2>', '<strong>tebal</strong>', '<em>miring</em>', '<ul>', '<li>satu</li>', '<blockquote>', '<pre>'] as $expected) {
            $this->assertStringContainsString($expected, $html);
        }
    }

    public function test_scripts_and_event_handlers_are_removed(): void
    {
        $html = RichText::sanitize('<p onclick="steal()">Halo</p><script>alert(1)</script><img src="x" onerror="alert(2)"><iframe src="https://evil.example"></iframe>');

        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('alert(', $html);
        $this->assertStringNotContainsString('onclick', $html);
        $this->assertStringNotContainsString('onerror', $html);
        $this->assertStringNotContainsString('<iframe', $html);
        $this->assertStringContainsString('Halo', $html);
    }

    public function test_dangerous_link_schemes_are_removed_and_safe_links_get_rel(): void
    {
        $html = RichText::sanitize('<a href="javascript:alert(1)">jahat</a> <a href="https://example.com">aman</a>');

        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringContainsString('href="https://example.com"', $html);
        $this->assertStringContainsString('rel="noopener noreferrer"', $html);
    }

    public function test_inline_styles_are_removed(): void
    {
        $this->assertStringNotContainsString('style=', RichText::sanitize('<p style="position:fixed;inset:0">x</p>'));
    }

    public function test_null_content_is_an_empty_string(): void
    {
        $this->assertSame('', RichText::sanitize(null));
    }

    public function test_the_lesson_page_renders_the_html_but_not_the_script(): void
    {
        $product = Product::factory()->create(['product_type' => ProductType::Course]);
        $course = Course::factory()->create(['product_id' => $product->id]);
        $module = CourseModule::create(['course_id' => $course->id, 'title' => 'Modul', 'position' => 1]);
        $lesson = $module->lessons()->create([
            'title' => 'Lesson HTML',
            'content' => '<h2>Bagian Satu</h2><p>Isi <strong>penting</strong></p><script>alert("xss")</script>',
            'position' => 1,
        ]);
        $user = User::factory()->create();
        Enrollment::create(['user_id' => $user->id, 'product_id' => $product->id, 'enrolled_at' => now()]);

        $this->actingAs($user)->get(route('learning.lessons.show', [$product->slug, $lesson]))
            ->assertOk()
            ->assertSee('<h2>Bagian Satu</h2>', false)
            ->assertSee('<strong>penting</strong>', false)
            ->assertDontSee('<script>alert("xss")</script>', false)
            ->assertDontSee('alert("xss")', false);
    }
}
