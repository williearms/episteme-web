<?php

namespace Tests\Feature\Learning;

use App\Enums\ProductType;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Enrollment;
use App\Models\Product;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    private CourseModule $module;

    private Quiz $quiz;

    /** @var array<string, QuizQuestion> */
    private array $questions = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = Product::factory()->create(['product_type' => ProductType::Course]);
        $course = Course::factory()->create(['product_id' => $this->product->id]);
        $this->module = CourseModule::create(['course_id' => $course->id, 'title' => 'Modul Satu', 'position' => 1]);
        $this->quiz = Quiz::create(['course_module_id' => $this->module->id, 'title' => 'Kuis Modul Satu', 'pass_percentage' => 50]);

        // single: one correct option; multi: two correct options (all-or-nothing)
        $this->questions['single'] = $this->question('Ibu kota Indonesia?', 1, [['Jakarta', true], ['Bandung', false], ['Surabaya', false]], 'Jakarta adalah ibu kota saat ini.');
        $this->questions['multi'] = $this->question('Mana yang bilangan prima?', 2, [['2', true], ['3', true], ['4', false]]);
    }

    private function question(string $body, int $position, array $options, ?string $explanation = null): QuizQuestion
    {
        $question = $this->quiz->questions()->create(['body' => $body, 'explanation' => $explanation, 'position' => $position]);

        foreach ($options as $i => [$text, $correct]) {
            $question->options()->create(['body' => $text, 'is_correct' => $correct, 'position' => $i + 1]);
        }

        return $question->load('options');
    }

    private function optionId(string $question, string $text): int
    {
        return $this->questions[$question]->options->firstWhere('body', $text)->id;
    }

    private function enrolled(): User
    {
        $user = User::factory()->create();
        Enrollment::create(['user_id' => $user->id, 'product_id' => $this->product->id, 'enrolled_at' => now()]);

        return $user;
    }

    private function submit(User $user, array $answers)
    {
        return $this->actingAs($user)->post(route('learning.quizzes.attempts.store', [$this->product->slug, $this->quiz]), ['answers' => $answers]);
    }

    private function allCorrect(): array
    {
        return [
            $this->questions['single']->id => [$this->optionId('single', 'Jakarta')],
            $this->questions['multi']->id => [$this->optionId('multi', '2'), $this->optionId('multi', '3')],
        ];
    }

    public function test_guests_cannot_open_or_submit_a_quiz(): void
    {
        $this->get(route('learning.quizzes.show', [$this->product->slug, $this->quiz]))->assertRedirect(route('login'));
        $this->post(route('learning.quizzes.attempts.store', [$this->product->slug, $this->quiz]))->assertRedirect(route('login'));
    }

    public function test_someone_not_enrolled_gets_a_404(): void
    {
        $stranger = User::factory()->create();

        $this->actingAs($stranger)->get(route('learning.quizzes.show', [$this->product->slug, $this->quiz]))->assertNotFound();
        $this->submit($stranger, $this->allCorrect())->assertNotFound();
        $this->assertDatabaseCount('quiz_attempts', 0);
    }

    public function test_the_quiz_page_shows_questions_but_never_the_answers(): void
    {
        $this->actingAs($this->enrolled())->get(route('learning.quizzes.show', [$this->product->slug, $this->quiz]))
            ->assertOk()
            ->assertSee('Ibu kota Indonesia?')
            ->assertSee('Jakarta')
            ->assertSee('Select all that apply')
            ->assertDontSee('is_correct')
            ->assertDontSee('Jakarta adalah ibu kota saat ini.');
    }

    public function test_all_correct_answers_pass_with_100_percent(): void
    {
        $user = $this->enrolled();

        $response = $this->submit($user, $this->allCorrect());

        $attempt = QuizAttempt::firstOrFail();
        $response->assertRedirect(route('learning.quizzes.attempts.show', [$this->product->slug, $this->quiz, $attempt]));
        $this->assertSame(100, $attempt->percentage);
        $this->assertTrue($attempt->passed);
        $this->assertSame(2, $attempt->correct_count);
    }

    public function test_wrong_answers_do_not_pass(): void
    {
        $this->submit($this->enrolled(), [
            $this->questions['single']->id => [$this->optionId('single', 'Bandung')],
            $this->questions['multi']->id => [$this->optionId('multi', '4')],
        ]);

        $attempt = QuizAttempt::firstOrFail();
        $this->assertSame(0, $attempt->percentage);
        $this->assertFalse($attempt->passed);
    }

    public function test_a_multiple_answer_question_is_all_or_nothing(): void
    {
        $this->submit($this->enrolled(), [
            $this->questions['single']->id => [$this->optionId('single', 'Jakarta')],
            $this->questions['multi']->id => [$this->optionId('multi', '2')], // missing "3"
        ]);

        $attempt = QuizAttempt::firstOrFail();
        $this->assertSame(1, $attempt->correct_count);
        $this->assertSame(50, $attempt->percentage);
        $this->assertTrue($attempt->passed); // pass mark is 50
    }

    public function test_selecting_an_extra_wrong_option_makes_a_multiple_answer_question_wrong(): void
    {
        $this->submit($this->enrolled(), [
            $this->questions['single']->id => [$this->optionId('single', 'Jakarta')],
            $this->questions['multi']->id => [$this->optionId('multi', '2'), $this->optionId('multi', '3'), $this->optionId('multi', '4')],
        ]);

        $this->assertSame(1, QuizAttempt::firstOrFail()->correct_count);
    }

    public function test_submitting_nothing_scores_zero(): void
    {
        $this->submit($this->enrolled(), []);

        $attempt = QuizAttempt::firstOrFail();
        $this->assertSame(0, $attempt->percentage);
        $this->assertFalse($attempt->passed);
    }

    public function test_option_ids_from_another_question_earn_no_credit(): void
    {
        // Answering the single-choice question with the (correct) option of the *other* question.
        $this->submit($this->enrolled(), [
            $this->questions['single']->id => [$this->optionId('multi', '2')],
        ]);

        $this->assertSame(0, QuizAttempt::firstOrFail()->correct_count);
    }

    public function test_a_question_with_no_correct_option_configured_never_gives_free_credit(): void
    {
        $broken = $this->question('Salah konfigurasi', 3, [['A', false], ['B', false]]);

        $result = $this->quiz->grade([]);

        $this->assertSame(3, $result['total_questions']);
        $this->assertSame(0, $result['correct_count']);
        $this->assertSame([], $result['answers'][$broken->id]);
    }

    public function test_the_result_page_reveals_correct_answers_and_explanations_after_submitting(): void
    {
        $user = $this->enrolled();
        $this->submit($user, $this->allCorrect());
        $attempt = QuizAttempt::firstOrFail();

        $this->actingAs($user)->get(route('learning.quizzes.attempts.show', [$this->product->slug, $this->quiz, $attempt]))
            ->assertOk()
            ->assertSee('100%')
            ->assertSee('Passed')
            ->assertSee('Jakarta adalah ibu kota saat ini.');
    }

    public function test_an_invalid_payload_is_rejected(): void
    {
        $this->actingAs($this->enrolled())->post(
            route('learning.quizzes.attempts.store', [$this->product->slug, $this->quiz]),
            ['answers' => [$this->questions['single']->id => ['not-a-number']]],
        )->assertSessionHasErrors('answers.*.*');

        $this->assertDatabaseCount('quiz_attempts', 0);
    }

    public function test_a_quiz_without_questions_is_not_available(): void
    {
        $empty = Quiz::create(['course_module_id' => CourseModule::create(['course_id' => $this->module->course_id, 'title' => 'Modul Dua', 'position' => 2])->id, 'title' => 'Kosong']);

        $this->actingAs($this->enrolled())->get(route('learning.quizzes.show', [$this->product->slug, $empty]))->assertNotFound();
    }

    public function test_a_quiz_from_another_course_cannot_be_opened_through_this_enrollment(): void
    {
        $otherProduct = Product::factory()->create(['product_type' => ProductType::Course]);
        $otherCourse = Course::factory()->create(['product_id' => $otherProduct->id]);
        $otherModule = CourseModule::create(['course_id' => $otherCourse->id, 'title' => 'Lain', 'position' => 1]);
        $otherQuiz = Quiz::create(['course_module_id' => $otherModule->id, 'title' => 'Kuis Lain']);
        $otherQuiz->questions()->create(['body' => 'Q', 'position' => 1])->options()->create(['body' => 'A', 'is_correct' => true, 'position' => 1]);
        $user = $this->enrolled();

        $this->actingAs($user)->get(route('learning.quizzes.show', [$this->product->slug, $otherQuiz]))->assertNotFound();
        $this->actingAs($user)->post(route('learning.quizzes.attempts.store', [$this->product->slug, $otherQuiz]))->assertNotFound();
    }

    public function test_one_user_cannot_read_another_users_attempt(): void
    {
        $owner = $this->enrolled();
        $this->submit($owner, $this->allCorrect());
        $attempt = QuizAttempt::firstOrFail();

        $this->actingAs($this->enrolled())->get(route('learning.quizzes.attempts.show', [$this->product->slug, $this->quiz, $attempt]))->assertNotFound();
    }

    public function test_passing_the_quiz_is_required_to_complete_the_course(): void
    {
        $lesson = $this->module->lessons()->create(['title' => 'L1', 'content' => '<p>x</p>', 'position' => 1]);
        $user = $this->enrolled();
        $enrollment = $user->enrollments()->firstOrFail();

        $this->actingAs($user)->post(route('learning.lessons.complete', [$this->product->slug, $lesson]));
        $this->assertSame(50, $enrollment->fresh()->progressPercent());
        $this->assertNull($enrollment->fresh()->completed_at);

        // Failing doesn't move it.
        $this->submit($user, []);
        $this->assertSame(50, $enrollment->fresh()->progressPercent());
        $this->assertNull($enrollment->fresh()->completed_at);

        $this->submit($user, $this->allCorrect());
        $this->assertSame(100, $enrollment->fresh()->progressPercent());
        $this->assertNotNull($enrollment->fresh()->completed_at);
    }

    public function test_passing_twice_counts_the_quiz_once(): void
    {
        $this->module->lessons()->create(['title' => 'L1', 'content' => '<p>x</p>', 'position' => 1]);
        $user = $this->enrolled();

        $this->submit($user, $this->allCorrect());
        $this->submit($user, $this->allCorrect());

        // 1 lesson (not done) + 1 quiz (passed) = 50%, not 100%.
        $this->assertSame(50, $user->enrollments()->firstOrFail()->progressPercent());
    }

    public function test_the_course_page_lists_the_quiz_and_offers_it_when_lessons_are_done(): void
    {
        $user = $this->enrolled();

        $this->actingAs($user)->get(route('learning.show', $this->product->slug))
            ->assertOk()
            ->assertSee('Quiz: Kuis Modul Satu')
            ->assertSee('Take the next quiz');

        $this->submit($user, $this->allCorrect());

        $this->actingAs($user)->get(route('learning.show', $this->product->slug))
            ->assertDontSee('Take the next quiz');
    }
}
