<?php

namespace App\Models;

use App\Enums\MembershipStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * `status`, `organization_id`, and `created_by` are intentionally excluded
 * from Fillable and are set explicitly by trusted server code, never
 * derived from request input. See docs/SECURITY.md §6 (mass assignment).
 * `activated_at`/`frozen_at`/`cancelled_at`/`cancellation_reason` are also
 * excluded — they're written only by the transition Actions
 * (ActivateMembershipAction etc.), never by a generic update.
 * `paid_amount`/`remaining_amount` are excluded too, as of Step 8 — they're
 * now driven exclusively by Payment actions (RecordPaymentAction etc. via
 * recalculateBalance()), not by anything a membership edit form could
 * submit. No FormRequest has ever actually sent them (UpdateMembershipRequest
 * only validates starts_at/ends_at/price/currency/notes), so this closes a
 * latent gap rather than changing current behavior.
 */
#[Fillable([
    'client_id',
    'membership_plan_id',
    'starts_at',
    'ends_at',
    'price',
    'currency',
    'notes',
])]
class Membership extends Model
{
    /** @use HasFactory<\Database\Factories\MembershipFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => MembershipStatus::class,
            'starts_at' => 'date',
            'ends_at' => 'date',
            'price' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'activated_at' => 'datetime',
            'frozen_at' => 'datetime',
            'cancelled_at' => 'datetime',
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

    public function membershipPlan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function canTransitionTo(MembershipStatus $target): bool
    {
        return $this->status->canTransitionTo($target);
    }

    /**
     * Recomputes remaining_amount from price and the current
     * paid_amount. Called by Payment actions after they adjust
     * paid_amount (up on record, down on void/refund) — kept as one
     * shared method so the subtraction logic has a single home rather
     * than being repeated in RecordPaymentAction, VoidPaymentAction, and
     * RefundPaymentAction. Does not save(); the caller is responsible for
     * persisting alongside its own change.
     */
    public function recalculateBalance(): void
    {
        $this->remaining_amount = bcsub((string) $this->price, (string) $this->paid_amount, 2);
    }
}
