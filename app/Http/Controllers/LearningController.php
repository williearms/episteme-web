<?php

namespace App\Http\Controllers;

use App\Enums\ProductType;
use App\Models\Enrollment;
use App\Models\Lesson;
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

        if ($product->product_type === ProductType::Course) {
            $product->load('course.modules.lessons');
            $lessons = $product->course?->modules->flatMap->lessons ?? collect();
            $completedIds = $enrollment->progress()->pluck('lesson_id');
        }

        return view('learning.show', [
            'enrollment' => $enrollment,
            'product' => $product,
            'lessons' => $lessons,
            'completedIds' => $completedIds,
            'nextLesson' => $lessons->first(fn (Lesson $lesson) => ! $completedIds->contains($lesson->id)),
        ]);
    }

    public function lesson(Request $request, string $slug, Lesson $lesson): View
    {
        $enrollment = $this->enrollment($request, $slug);
        $this->assertLessonBelongsTo($enrollment, $lesson);

        $product = $enrollment->product->load('course.modules.lessons');
        $lessons = $product->course->modules->flatMap->lessons->values();
        $index = $lessons->search(fn (Lesson $item) => $item->id === $lesson->id);

        return view('learning.lesson', [
            'enrollment' => $enrollment,
            'product' => $product,
            'lesson' => $lesson,
            'modules' => $product->course->modules,
            'completedIds' => $enrollment->progress()->pluck('lesson_id'),
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

    /** A lesson id from another course must not be readable through this course's enrollment. */
    private function assertLessonBelongsTo(Enrollment $enrollment, Lesson $lesson): void
    {
        abort_unless($lesson->module->course->product_id === $enrollment->product_id, 404);
    }
}
