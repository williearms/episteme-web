<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Course-specific product metadata (Phase 3: product description only —
 * modules/lessons/quizzes/progress belong to the Learning phase).
 */
class Course extends Model
{
    /** @use HasFactory<\Database\Factories\CourseFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'duration_minutes',
        'total_modules',
        'total_lessons',
        'delivery_type',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'total_modules' => 'integer',
            'total_lessons' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function durationLabel(): ?string
    {
        if (! $this->duration_minutes) {
            return null;
        }

        $hours = intdiv($this->duration_minutes, 60);
        $minutes = $this->duration_minutes % 60;

        return trim(($hours ? "{$hours}h " : '').($minutes ? "{$minutes}m" : ''));
    }
}
