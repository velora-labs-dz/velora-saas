<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * `status`, `organization_id`, `recorded_by`, `refunded_amount`,
 * `refund_reason`, `voided_at`, and `void_reason` are intentionally
 * excluded from Fillable — set only by trusted server code
 * (RecordPaymentAction / VoidPaymentAction / RefundPaymentAction), never
 * derived from request input directly. See docs/SECURITY.md §6 (mass
 * assignment). There is no generic update — a payment is append-oriented
 * per docs/VELORA_SOURCE_OF_TRUTH.md §2.4; it's corrected through
 * void/refund, never edited in place.
 */
#[Fillable([
    'client_id',
    'membership_id',
    'amount',
    'currency',
    'method',
    'reference',
    'paid_at',
    'notes',
])]
class Payment extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'method' => PaymentMethod::class,
            'status' => PaymentStatus::class,
            'amount' => 'decimal:2',
            'refunded_amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'voided_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * The amount actually retained after refunds — what still "counts"
     * toward a linked membership's paid_amount. Zero once voided (the
     * payment never happened) or fully refunded.
     */
    public function netAmount(): string
    {
        if ($this->status === PaymentStatus::Voided) {
            return '0.00';
        }

        return bcsub((string) $this->amount, (string) $this->refunded_amount, 2);
    }
}
