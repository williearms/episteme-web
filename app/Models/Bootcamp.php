<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bootcamp extends Model
{
    /** @use HasFactory<\Database\Factories\BootcampFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'duration',
        'schedule',
        'cohort',
        'delivery_mode',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function durationLabel(): ?string
    {
        return $this->duration;
    }
}
