<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Expired = 'expired';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Paid => 'Paid',
            self::Failed => 'Failed',
            self::Expired => 'Expired',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Pending => 'neutral',
            self::Paid => 'success',
            self::Failed, self::Expired, self::Cancelled => 'error',
        };
    }

    /** Terminal statuses never transition again — used by the webhook handler for idempotency. */
    public function isTerminal(): bool
    {
        return match ($this) {
            self::Paid, self::Expired, self::Cancelled => true,
            self::Pending, self::Failed => false,
        };
    }
}
