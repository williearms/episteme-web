<?php

namespace App\Models;

use Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Course-specific product metadata (Phase 3) plus the course's modules and
 * lessons (Phase 6). total_modules/total_lessons stay as the public,
 * marketing-facing counts shown on the product page.
 */
class Course extends Model
{
    /** @use HasFactory<CourseFactory> */
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

    public function modules(): HasMany
    {
        return $this->hasMany(CourseModule::class)->orderBy('position');
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
