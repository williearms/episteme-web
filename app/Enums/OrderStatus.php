<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending Payment',
            self::Paid => 'Paid',
            self::Cancelled => 'Cancelled',
            self::Expired => 'Expired',
        };
    }

    /** Badge variant — reuses the same success/neutral/error tokens as PaymentStatus. */
    public function badgeVariant(): string
    {
        return match ($this) {
            self::Pending => 'neutral',
            self::Paid => 'success',
            self::Cancelled, self::Expired => 'error',
        };
    }
}
