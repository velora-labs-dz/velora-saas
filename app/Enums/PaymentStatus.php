<?php

namespace App\Enums;

/**
 * Financial records are append-oriented (docs/VELORA_SOURCE_OF_TRUTH.md
 * §2.4) — a payment is never edited in place. Two distinct correction
 * operations exist, not one generic "cancel":
 *
 *   recorded ──void──> voided        (this payment should never have
 *                                      existed — a data-entry mistake)
 *   recorded ──refund─> refunded      (real money, genuinely paid, is
 *                                      being given back — partial or
 *                                      full, tracked via refunded_amount)
 *
 * Voided and Refunded are both terminal. You can't void a payment that's
 * already had a refund applied to it — that money genuinely changed
 * hands, so "this never happened" is no longer true; use additional
 * refunds instead. See ADR-010.
 */
enum PaymentStatus: string
{
    case Recorded = 'recorded';
    case Voided = 'voided';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Recorded => 'Recorded',
            self::Voided => 'Voided',
            self::Refunded => 'Refunded',
        };
    }

    public function canVoid(): bool
    {
        return $this === self::Recorded;
    }

    public function canRefund(): bool
    {
        return $this === self::Recorded || $this === self::Refunded;
    }
}
