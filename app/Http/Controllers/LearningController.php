<?php

namespace App\Http\Controllers;

use App\Enums\ProductType;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Product;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LearningController extends Controller
{
    public function index(Request $request): View
    {
        $enrollments = $request->user()->enrollments()
            ->with('product.category')
            ->latest('enrolled_at')
            ->get();

        return view('account.learning', ['enrollments' => $enrollments]);
    }

    public function show(Request $request, string $slug): View
    {
        $enrollment = $this->enrollment($request, $slug);
        $product = $enrollment->product->load(['access', 'digitalProduct', 'webinar', 'workshop', 'bootcamp', 'mentoring']);

        $lessons = collect();
        $completedIds = collect();
        $passedQuizIds = collect();
        $nextQuiz = null;

        if ($product->product_type === ProductType::Course) {
            $this->loadCourseOutline($product);
            $lessons = $product->course?->modules->flatMap->lessons ?? collect();
            $completedIds = $enrollment->progress()->pluck('lesson_id');
            $passedQuizIds = $this->passedQuizIds($enrollment);
            $nextQuiz = ($product->course?->modules ?? collect())
                ->map->quiz
                ->first(fn (?Quiz $quiz) => $quiz && $quiz->questions_count > 0 && ! $passedQuizIds->contains($quiz->id));
        }

        return view('learning.show', [
            'enrollment' => $enrollment,
            'product' => $product,
            'lessons' => $lessons,
            'completedIds' => $completedIds,
            'passedQuizIds' => $passedQuizIds,
            'nextLesson' => $lessons->first(fn (Lesson $lesson) => ! $completedIds->contains($lesson->id)),
            'nextQuiz' => $nextQuiz,
        ]);
    }

    public function lesson(Request $request, string $slug, Lesson $lesson): View
    {
        $enrollment = $this->enrollment($request, $slug);
        $this->assertLessonBelongsTo($enrollment, $lesson);

        $product = $enrollment->product;
        $this->loadCourseOutline($product);
        $lessons = $product->course->modules->flatMap->lessons->values();
        $index = $lessons->search(fn (Lesson $item) => $item->id === $lesson->id);

        return view('learning.lesson', [
            'enrollment' => $enrollment,
            'product' => $product,
            'lesson' => $lesson,
            'modules' => $product->course->modules,
            'completedIds' => $enrollment->progress()->pluck('lesson_id'),
            'passedQuizIds' => $this->passedQuizIds($enrollment),
            'previous' => $index > 0 ? $lessons[$index - 1] : null,
            'next' => $lessons[$index + 1] ?? null,
        ]);
    }

    public function complete(Request $request, string $slug, Lesson $lesson): RedirectResponse
    {
        $enrollment = $this->enrollment($request, $slug);
        $this->assertLessonBelongsTo($enrollment, $lesson);

        $enrollment->markLesson($lesson, true);

        return back()->with('status', 'Lesson marked as complete.');
    }

    public function uncomplete(Request $request, string $slug, Lesson $lesson): RedirectResponse
    {
        $enrollment = $this->enrollment($request, $slug);
        $this->assertLessonBelongsTo($enrollment, $lesson);

        $enrollment->markLesson($lesson, false);

        return back()->with('status', 'Lesson marked as not complete.');
    }

    public function quiz(Request $request, string $slug, Quiz $quiz): View
    {
        $enrollment = $this->enrollment($request, $slug);
        $this->assertQuizBelongsTo($enrollment, $quiz);

        // Options are rendered without is_correct — the answer key never leaves the server before submit.
        $quiz->load('questions.options');

        return view('learning.quiz', [
            'enrollment' => $enrollment,
            'product' => $enrollment->product,
            'quiz' => $quiz,
            'attempts' => $enrollment->quizAttempts()->where('quiz_id', $quiz->id)->latest()->get(),
        ]);
    }

    public function submitQuiz(Request $request, string $slug, Quiz $quiz): RedirectResponse
    {
        $enrollment = $this->enrollment($request, $slug);
        $this->assertQuizBelongsTo($enrollment, $quiz);

        $validated = $request->validate([
            'answers' => ['nullable', 'array'],
            'answers.*' => ['array'],
            'answers.*.*' => ['integer'],
        ]);

        $attempt = $enrollment->quizAttempts()->create(['quiz_id' => $quiz->id] + $quiz->grade($validated['answers'] ?? []));

        $enrollment->syncCompletion();

        return redirect()->route('learning.quizzes.attempts.show', [$slug, $quiz, $attempt]);
    }

    public function attempt(Request $request, string $slug, Quiz $quiz, QuizAttempt $attempt): View
    {
        $enrollment = $this->enrollment($request, $slug);
        $this->assertQuizBelongsTo($enrollment, $quiz);
        abort_unless($attempt->enrollment_id === $enrollment->id && $attempt->quiz_id === $quiz->id, 404);

        $quiz->load('questions.options');

        return view('learning.quiz-result', [
            'enrollment' => $enrollment,
            'product' => $enrollment->product,
            'quiz' => $quiz,
            'attempt' => $attempt,
        ]);
    }

    /** Streams from the private disk — the file's real path is never exposed in a URL. */
    public function download(Request $request, string $slug): StreamedResponse
    {
        $enrollment = $this->enrollment($request, $slug);
        $product = $enrollment->product->load('digitalProduct');
        $path = $product->digitalProduct?->file_path;

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download(
            $path,
            Str::slug($product->title).'.'.pathinfo($path, PATHINFO_EXTENSION),
        );
    }

    /**
     * Resolved through the signed-in user's own enrollments, never by an
     * enrollment id in the URL — so there is nothing to guess or tamper with.
     */
    private function enrollment(Request $request, string $slug): Enrollment
    {
        return $request->user()->enrollments()
            ->whereHas('product', fn ($query) => $query->where('slug', $slug))
            ->with('product')
            ->firstOrFail();
    }

    private function loadCourseOutline(Product $product): void
    {
        $product->load([
            'course.modules.lessons',
            'course.modules.quiz' => fn ($query) => $query->withCount('questions'),
        ]);
    }

    private function passedQuizIds(Enrollment $enrollment)
    {
        return $enrollment->quizAttempts()->where('passed', true)->pluck('quiz_id')->unique()->values();
    }

    /** Same guard as lessons: a quiz from another course (or one with no questions yet) is a 404. */
    private function assertQuizBelongsTo(Enrollment $enrollment, Quiz $quiz): void
    {
        abort_unless(
            $quiz->module->course->product_id === $enrollment->product_id && $quiz->questions()->exists(),
            404
        );
    }

    /** A lesson id from another course must not be readable through this course's enrollment. */
    private function assertLessonBelongsTo(Enrollment $enrollment, Lesson $lesson): void
    {
        abort_unless($lesson->module->course->product_id === $enrollment->product_id, 404);
    }
}
