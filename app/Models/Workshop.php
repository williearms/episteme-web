<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Workshop extends Model
{
    /** @use HasFactory<\Database\Factories\WorkshopFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'event_date',
        'duration_minutes',
        'mode',
        'location',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'duration_minutes' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function durationLabel(): ?string
    {
        return $this->duration_minutes ? "{$this->duration_minutes} minutes" : null;
    }
}
