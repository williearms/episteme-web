<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enrollment extends Model
{
    protected $fillable = ['user_id', 'product_id', 'order_id', 'enrolled_at', 'completed_at'];

    protected function casts(): array
    {
        return [
            'enrolled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function hasCompleted(Lesson $lesson): bool
    {
        return $this->progress()->where('lesson_id', $lesson->id)->exists();
    }

    /** Share of the course's lessons this enrollment has completed, 0–100. */
    public function progressPercent(): int
    {
        $total = $this->courseLessonCount();

        if ($total === 0) {
            return 0;
        }

        return (int) floor($this->progress()->count() / $total * 100);
    }

    public function markLesson(Lesson $lesson, bool $completed): void
    {
        if ($completed) {
            $this->progress()->firstOrCreate(['lesson_id' => $lesson->id], ['completed_at' => now()]);
        } else {
            $this->progress()->where('lesson_id', $lesson->id)->delete();
        }

        $this->syncCompletion();
    }

    private function syncCompletion(): void
    {
        $finished = $this->progressPercent() === 100;

        if ($finished && ! $this->completed_at) {
            $this->update(['completed_at' => now()]);
        } elseif (! $finished && $this->completed_at) {
            $this->update(['completed_at' => null]);
        }
    }

    private function courseLessonCount(): int
    {
        return Lesson::query()
            ->whereHas('module.course', fn ($query) => $query->where('product_id', $this->product_id))
            ->count();
    }
}
