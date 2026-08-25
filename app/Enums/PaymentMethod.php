<?php

namespace App\Enums;

/**
 * Cash/transfer only — no payment gateway in Phase 1. See
 * docs/FOUNDATION.md §4 ("Payment recording (cash/transfer only, no
 * provider integration)").
 */
enum PaymentMethod: string
{
    case Cash = 'cash';
    case Transfer = 'transfer';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::Transfer => 'Transfer',
        };
    }
}
