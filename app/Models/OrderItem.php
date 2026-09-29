<?php

namespace App\Models;

use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_title',
        'product_type',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'integer',
            'subtotal' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** May be null — the product can be deleted after the order is placed; the snapshot fields above remain authoritative. */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getProductTypeLabelAttribute(): string
    {
        return ProductType::from($this->product_type)->label();
    }

    public function getUnitPriceLabelAttribute(): string
    {
        return $this->unit_price === 0 ? 'Free' : 'Rp'.number_format($this->unit_price, 0, ',', '.');
    }

    public function getSubtotalLabelAttribute(): string
    {
        return $this->subtotal === 0 ? 'Free' : 'Rp'.number_format($this->subtotal, 0, ',', '.');
    }
}
