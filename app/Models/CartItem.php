<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = ['cart_id', 'product_id', 'quantity', 'unit_price'];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'integer',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getSubtotalAttribute(): int
    {
        return $this->unit_price * $this->quantity;
    }

    public function getSubtotalLabelAttribute(): string
    {
        return $this->subtotal === 0 ? 'Free' : 'Rp'.number_format($this->subtotal, 0, ',', '.');
    }

    /** A cart can outlive a product's publish state — re-check at read time, never trust the row was valid when added. */
    public function isPurchasable(): bool
    {
        return $this->product !== null && $this->product->isPurchasable();
    }
}
