<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'subtotal',
        'discount',
        'total',
        'currency',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'subtotal' => 'integer',
            'discount' => 'integer',
            'total' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'order_number';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Give the buyer access to everything on this order. Idempotent — the
     * unique [user_id, product_id] key means a redelivered webhook or a
     * repeat purchase can never create a second enrollment.
     */
    public function grantEnrollments(): void
    {
        foreach ($this->items()->whereNotNull('product_id')->get() as $item) {
            Enrollment::firstOrCreate(
                ['user_id' => $this->user_id, 'product_id' => $item->product_id],
                ['order_id' => $this->id, 'enrolled_at' => now()],
            );
        }
    }

    public function latestPayment(): ?Payment
    {
        return $this->payments()->latest()->first();
    }

    public function getTotalLabelAttribute(): string
    {
        return $this->total === 0 ? 'Free' : 'Rp'.number_format($this->total, 0, ',', '.');
    }

    public function getSubtotalLabelAttribute(): string
    {
        return $this->subtotal === 0 ? 'Free' : 'Rp'.number_format($this->subtotal, 0, ',', '.');
    }

    /** "EP-20260928-000123" — human-readable, not guessable enough to matter, retried on the (astronomically unlikely) collision. */
    public static function generateOrderNumber(): string
    {
        do {
            $candidate = 'EP-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('order_number', $candidate)->exists());

        return $candidate;
    }
}
