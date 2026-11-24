<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAccess extends Model
{
    protected $table = 'product_accesses';

    protected $fillable = ['product_id', 'join_url', 'instructions'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
