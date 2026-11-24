<?php

namespace App\Models;

use Database\Factories\DigitalProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalProduct extends Model
{
    /** @use HasFactory<DigitalProductFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'file_type',
        'file_size',
        'access_type',
        'file_path',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** Digital products are static files — there's no duration concept. */
    public function durationLabel(): ?string
    {
        return null;
    }
}
