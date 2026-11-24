<?php

namespace Tests\Feature\Admin;

use App\Enums\ProductType;
use App\Filament\Resources\CourseModuleResource\Pages\EditCourseModule;
use App\Filament\Resources\CourseModuleResource\RelationManagers\LessonsRelationManager;
use App\Filament\Resources\QuizResource\Pages\CreateQuiz;
use App\Filament\Resources\QuizResource\Pages\ListQuizzes;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Enrollment;
use App\Models\Product;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Tables\Actions\CreateAction as TableCreateAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminQuizTest extends TestCase
{
    use RefreshDatabase;

    private CourseModule $module;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->admin()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $product = Product::factory()->create(['product_type' => ProductType::Course]);
        $this->module = CourseModule::create([
            'course_id' => Course::factory()->create(['product_id' => $product->id])->id,
            'title' => 'Modul Satu',
            'position' => 1,
        ]);
    }

    private function quizData(array $overrides = []): array
    {
        return array_merge([
            'course_module_id' => $this->module->id,
            'title' => 'Kuis Admin',
            'pass_percentage' => 60,
            'questions' => [[
                'body' => 'Apa warna langit?',
                'explanation' => 'Biru saat cerah.',
                'options' => [
                    ['body' => 'Biru', 'is_correct' => true],
                    ['body' => 'Hijau', 'is_correct' => false],
                ],
            ]],
        ], $overrides);
    }

    public function test_an_admin_can_create_a_quiz_with_questions_and_options(): void
    {
        Livewire::test(CreateQuiz::class)->fillForm($this->quizData())->call('create')->assertHasNoFormErrors();

        $quiz = Quiz::where('title', 'Kuis Admin')->firstOrFail();
        $this->assertSame($this->module->id, $quiz->course_module_id);
        $this->assertDatabaseHas('quiz_questions', ['quiz_id' => $quiz->id, 'body' => 'Apa warna langit?', 'position' => 1]);
        $this->assertDatabaseHas('quiz_options', ['body' => 'Biru', 'is_correct' => true]);
        $this->assertDatabaseHas('quiz_options', ['body' => 'Hijau', 'is_correct' => false]);
    }

    public function test_a_question_needs_at_least_one_correct_option(): void
    {
        $data = $this->quizData();
        $data['questions'][0]['options'] = [['body' => 'A', 'is_correct' => false], ['body' => 'B', 'is_correct' => false]];

        Livewire::test(CreateQuiz::class)->fillForm($data)->call('create')->assertHasFormErrors();

        $this->assertDatabaseCount('quizzes', 0);
    }

    public function test_a_question_needs_at_least_two_options(): void
    {
        $data = $this->quizData();
        $data['questions'][0]['options'] = [['body' => 'Satu-satunya', 'is_correct' => true]];

        Livewire::test(CreateQuiz::class)->fillForm($data)->call('create')->assertHasFormErrors();

        $this->assertDatabaseCount('quizzes', 0);
    }

    public function test_a_quiz_needs_at_least_one_question(): void
    {
        Livewire::test(CreateQuiz::class)->fillForm($this->quizData(['questions' => []]))->call('create')->assertHasFormErrors();
    }

    public function test_a_module_can_only_have_one_quiz(): void
    {
        Quiz::create(['course_module_id' => $this->module->id, 'title' => 'Sudah ada']);

        Livewire::test(CreateQuiz::class)->fillForm($this->quizData())->call('create')->assertHasFormErrors(['course_module_id' => 'unique']);
    }

    public function test_the_pass_mark_must_be_between_1_and_100(): void
    {
        Livewire::test(CreateQuiz::class)->fillForm($this->quizData(['pass_percentage' => 150]))->call('create')->assertHasFormErrors(['pass_percentage']);
    }

    public function test_a_quiz_with_student_attempts_cannot_be_deleted(): void
    {
        $used = Quiz::create(['course_module_id' => $this->module->id, 'title' => 'Dipakai']);
        $enrollment = Enrollment::create(['user_id' => User::factory()->create()->id, 'product_id' => $this->module->course->product_id, 'enrolled_at' => now()]);
        QuizAttempt::create(['enrollment_id' => $enrollment->id, 'quiz_id' => $used->id, 'correct_count' => 1, 'total_questions' => 1, 'percentage' => 100, 'passed' => true, 'answers' => []]);
        $unused = Quiz::create([
            'course_module_id' => CourseModule::create(['course_id' => $this->module->course_id, 'title' => 'Dua', 'position' => 2])->id,
            'title' => 'Belum dipakai',
        ]);

        Livewire::test(ListQuizzes::class)
            ->assertTableActionHidden('delete', $used)
            ->assertTableActionVisible('delete', $unused);
    }

    public function test_lesson_html_from_the_rich_editor_is_saved(): void
    {
        Livewire::test(LessonsRelationManager::class, ['ownerRecord' => $this->module, 'pageClass' => EditCourseModule::class])
            ->callTableAction(TableCreateAction::class, data: [
                'title' => 'Lesson Rich',
                'position' => 1,
                'content' => '<h2>Judul</h2><p>Isi <strong>tebal</strong></p>',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('lessons', ['title' => 'Lesson Rich', 'content' => '<h2>Judul</h2><p>Isi <strong>tebal</strong></p>']);
    }
}
