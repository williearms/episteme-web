<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    protected $fillable = ['course_module_id', 'title', 'description', 'pass_percentage'];

    protected function casts(): array
    {
        return ['pass_percentage' => 'integer'];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'course_module_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('position');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    /**
     * Grade a submission. Always done here on the server from the stored answer key —
     * the browser never receives which options are correct before submitting.
     *
     * A question is correct only when the chosen options are exactly the correct ones
     * (all-or-nothing for multiple-answer questions). Option ids that don't belong to
     * the question are ignored, and an unanswered question is wrong.
     *
     * @param  array<int|string, mixed>  $submitted  question id => option id(s)
     * @return array{correct_count: int, total_questions: int, percentage: int, passed: bool, answers: array<int, array<int, int>>}
     */
    public function grade(array $submitted): array
    {
        $questions = $this->questions()->with('options')->get();
        $answers = [];
        $correctCount = 0;

        foreach ($questions as $question) {
            $validIds = $question->options->pluck('id')->all();

            $chosen = collect((array) ($submitted[$question->id] ?? []))
                ->map(fn ($id) => (int) $id)
                ->filter(fn (int $id) => in_array($id, $validIds, true))
                ->unique()->sort()->values()->all();

            $answerKey = $question->options->where('is_correct', true)->pluck('id')->sort()->values()->all();

            $answers[$question->id] = $chosen;

            // An empty key would otherwise make a blank answer "correct".
            if ($answerKey !== [] && $chosen === $answerKey) {
                $correctCount++;
            }
        }

        $total = $questions->count();
        $percentage = $total > 0 ? (int) floor($correctCount / $total * 100) : 0;

        return [
            'correct_count' => $correctCount,
            'total_questions' => $total,
            'percentage' => $percentage,
            'passed' => $total > 0 && $percentage >= $this->pass_percentage,
            'answers' => $answers,
        ];
    }
}
