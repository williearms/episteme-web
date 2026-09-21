<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Webinar-specific product metadata. Deliberately holds only public
 * scheduling info (date/time/platform name) — meeting access, attendance,
 * and recordings belong to a later delivery phase.
 */
class Webinar extends Model
{
    /** @use HasFactory<\Database\Factories\WebinarFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'event_date',
        'start_time',
        'end_time',
        'timezone',
        'platform',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function durationLabel(): ?string
    {
        if (! $this->start_time) {
            return null;
        }

        if (! $this->end_time) {
            return substr($this->start_time, 0, 5).' '.$this->timezone;
        }

        $minutes = \Illuminate\Support\Carbon::parse($this->start_time)->diffInMinutes(\Illuminate\Support\Carbon::parse($this->end_time));
        $hours = intdiv((int) $minutes, 60);
        $remainder = $minutes % 60;

        return trim(($hours ? "{$hours}h " : '').($remainder ? "{$remainder}m" : '')) ?: null;
    }
}
