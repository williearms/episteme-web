<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mentoring extends Model
{
    /** @use HasFactory<\Database\Factories\MentoringFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'session_duration_minutes',
        'number_of_sessions',
        'session_type',
        'delivery_mode',
    ];

    protected function casts(): array
    {
        return [
            'session_duration_minutes' => 'integer',
            'number_of_sessions' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function durationLabel(): ?string
    {
        if (! $this->session_duration_minutes) {
            return null;
        }

        $sessions = $this->number_of_sessions > 1 ? "{$this->number_of_sessions}x " : '';

        return "{$sessions}{$this->session_duration_minutes} min/session";
    }
}
