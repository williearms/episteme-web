<?php

namespace Tests\Feature\Learning;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\DigitalProduct;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Product;
use App\Models\ProductAccess;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LearningAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: Product, 1: array<int, Lesson>} */
    private function courseWithLessons(int $lessonCount = 2): array
    {
        $product = Product::factory()->create(['product_type' => ProductType::Course]);
        $course = Course::factory()->create(['product_id' => $product->id]);
        $module = CourseModule::create(['course_id' => $course->id, 'title' => 'Modul Satu', 'position' => 1]);

        $lessons = [];
        foreach (range(1, $lessonCount) as $position) {
            $lessons[] = $module->lessons()->create(['title' => "Lesson {$position}", 'content' => "Isi lesson {$position}", 'position' => $position]);
        }

        return [$product, $lessons];
    }

    private function enroll(User $user, Product $product): Enrollment
    {
        return Enrollment::create(['user_id' => $user->id, 'product_id' => $product->id, 'enrolled_at' => now()]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        [$product] = $this->courseWithLessons();

        $this->get(route('account.learning.index'))->assertRedirect(route('login'));
        $this->get(route('learning.show', $product->slug))->assertRedirect(route('login'));
    }

    public function test_my_learning_lists_only_the_users_own_enrollments(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $mine = Product::factory()->create(['title' => 'Kelas Milik Saya']);
        $theirs = Product::factory()->create(['title' => 'Kelas Orang Lain']);
        $this->enroll($user, $mine);
        $this->enroll($other, $theirs);

        $this->actingAs($user)->get(route('account.learning.index'))
            ->assertOk()
            ->assertSee('Kelas Milik Saya')
            ->assertDontSee('Kelas Orang Lain');
    }

    public function test_my_learning_shows_an_empty_state(): void
    {
        $this->actingAs(User::factory()->create())->get(route('account.learning.index'))
            ->assertOk()
            ->assertSee('enrolled in anything yet');
    }

    public function test_a_user_cannot_open_a_product_they_are_not_enrolled_in(): void
    {
        [$product] = $this->courseWithLessons();

        $this->actingAs(User::factory()->create())->get(route('learning.show', $product->slug))->assertNotFound();
    }

    public function test_one_user_cannot_open_another_users_enrollment(): void
    {
        [$product, $lessons] = $this->courseWithLessons();
        $this->enroll(User::factory()->create(), $product);
        $intruder = User::factory()->create();

        $this->actingAs($intruder)->get(route('learning.show', $product->slug))->assertNotFound();
        $this->actingAs($intruder)->get(route('learning.lessons.show', [$product->slug, $lessons[0]]))->assertNotFound();
        $this->actingAs($intruder)->post(route('learning.lessons.complete', [$product->slug, $lessons[0]]))->assertNotFound();
    }

    public function test_an_enrolled_user_can_open_the_course_and_a_lesson(): void
    {
        [$product, $lessons] = $this->courseWithLessons();
        $user = User::factory()->create();
        $this->enroll($user, $product);

        $this->actingAs($user)->get(route('learning.show', $product->slug))
            ->assertOk()
            ->assertSee('Modul Satu')
            ->assertSee('Lesson 1');

        $this->actingAs($user)->get(route('learning.lessons.show', [$product->slug, $lessons[0]]))
            ->assertOk()
            ->assertSee('Isi lesson 1');
    }

    public function test_a_lesson_from_another_course_is_not_readable_through_this_enrollment(): void
    {
        [$product] = $this->courseWithLessons();
        [, $foreignLessons] = $this->courseWithLessons();
        $user = User::factory()->create();
        $this->enroll($user, $product);

        $this->actingAs($user)->get(route('learning.lessons.show', [$product->slug, $foreignLessons[0]]))->assertNotFound();
        $this->actingAs($user)->post(route('learning.lessons.complete', [$product->slug, $foreignLessons[0]]))->assertNotFound();
    }

    public function test_completing_lessons_updates_progress_and_completes_the_enrollment(): void
    {
        [$product, $lessons] = $this->courseWithLessons(2);
        $user = User::factory()->create();
        $enrollment = $this->enroll($user, $product);

        $this->actingAs($user)->post(route('learning.lessons.complete', [$product->slug, $lessons[0]]));
        $this->assertSame(50, $enrollment->fresh()->progressPercent());
        $this->assertNull($enrollment->fresh()->completed_at);

        $this->actingAs($user)->post(route('learning.lessons.complete', [$product->slug, $lessons[1]]));
        $this->assertSame(100, $enrollment->fresh()->progressPercent());
        $this->assertNotNull($enrollment->fresh()->completed_at);
    }

    public function test_completing_the_same_lesson_twice_counts_once(): void
    {
        [$product, $lessons] = $this->courseWithLessons(2);
        $user = User::factory()->create();
        $enrollment = $this->enroll($user, $product);

        $this->actingAs($user)->post(route('learning.lessons.complete', [$product->slug, $lessons[0]]));
        $this->actingAs($user)->post(route('learning.lessons.complete', [$product->slug, $lessons[0]]));

        $this->assertDatabaseCount('lesson_progress', 1);
        $this->assertSame(50, $enrollment->fresh()->progressPercent());
    }

    public function test_undoing_a_lesson_lowers_progress_and_clears_course_completion(): void
    {
        [$product, $lessons] = $this->courseWithLessons(1);
        $user = User::factory()->create();
        $enrollment = $this->enroll($user, $product);

        $this->actingAs($user)->post(route('learning.lessons.complete', [$product->slug, $lessons[0]]));
        $this->assertNotNull($enrollment->fresh()->completed_at);

        $this->actingAs($user)->delete(route('learning.lessons.uncomplete', [$product->slug, $lessons[0]]));

        $this->assertSame(0, $enrollment->fresh()->progressPercent());
        $this->assertNull($enrollment->fresh()->completed_at);
    }

    public function test_progress_is_tracked_per_user(): void
    {
        [$product, $lessons] = $this->courseWithLessons(2);
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        $aliceEnrollment = $this->enroll($alice, $product);
        $bobEnrollment = $this->enroll($bob, $product);

        $this->actingAs($alice)->post(route('learning.lessons.complete', [$product->slug, $lessons[0]]));

        $this->assertSame(50, $aliceEnrollment->fresh()->progressPercent());
        $this->assertSame(0, $bobEnrollment->fresh()->progressPercent());
    }

    public function test_an_archived_product_stays_accessible_to_people_who_already_bought_it(): void
    {
        [$product] = $this->courseWithLessons();
        $user = User::factory()->create();
        $this->enroll($user, $product);
        $product->update(['status' => ProductStatus::Archived]);

        $this->actingAs($user)->get(route('learning.show', $product->slug))->assertOk();
    }

    public function test_join_details_are_shown_to_enrolled_users_but_never_on_the_public_page(): void
    {
        $product = Product::factory()->create(['product_type' => ProductType::Webinar]);
        ProductAccess::create(['product_id' => $product->id, 'join_url' => 'https://meet.example.com/secret-room', 'instructions' => 'Masuk 10 menit lebih awal.']);
        $user = User::factory()->create();
        $this->enroll($user, $product);

        $this->actingAs($user)->get(route('learning.show', $product->slug))
            ->assertOk()
            ->assertSee('https://meet.example.com/secret-room', false)
            ->assertSee('Masuk 10 menit lebih awal.');

        $this->get(route('products.show', $product))->assertOk()->assertDontSee('secret-room');
        $this->actingAs($user)->get(route('products.show', $product))->assertDontSee('secret-room');
    }

    public function test_a_digital_product_can_be_downloaded_only_by_an_enrolled_user(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('digital-products/panduan.txt', 'isi berkas');
        $product = Product::factory()->create(['product_type' => ProductType::DigitalProduct]);
        DigitalProduct::factory()->create(['product_id' => $product->id, 'file_path' => 'digital-products/panduan.txt']);
        $buyer = User::factory()->create();
        $this->enroll($buyer, $product);

        $this->actingAs($buyer)->get(route('learning.download', $product->slug))->assertOk()->assertDownload();
        $this->actingAs(User::factory()->create())->get(route('learning.download', $product->slug))->assertNotFound();
    }

    public function test_the_download_url_never_exposes_the_private_file_path(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('digital-products/panduan.txt', 'isi berkas');
        $product = Product::factory()->create(['product_type' => ProductType::DigitalProduct]);
        DigitalProduct::factory()->create(['product_id' => $product->id, 'file_path' => 'digital-products/panduan.txt']);
        $buyer = User::factory()->create();
        $this->enroll($buyer, $product);

        $this->actingAs($buyer)->get(route('learning.show', $product->slug))
            ->assertOk()
            ->assertDontSee('digital-products/panduan.txt');
    }

    public function test_a_missing_file_returns_not_found_instead_of_an_error(): void
    {
        Storage::fake('local');
        $product = Product::factory()->create(['product_type' => ProductType::DigitalProduct]);
        DigitalProduct::factory()->create(['product_id' => $product->id, 'file_path' => 'digital-products/hilang.txt']);
        $buyer = User::factory()->create();
        $this->enroll($buyer, $product);

        $this->actingAs($buyer)->get(route('learning.download', $product->slug))->assertNotFound();
    }
}
