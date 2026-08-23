<?php

namespace App\Models;

use App\Enums\MembershipStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * `status`, `organization_id`, and `created_by` are intentionally excluded
 * from Fillable and are set explicitly by trusted server code, never
 * derived from request input. See docs/SECURITY.md §6 (mass assignment).
 * `activated_at`/`frozen_at`/`cancelled_at`/`cancellation_reason` are also
 * excluded — they're written only by the transition Actions
 * (ActivateMembershipAction etc.), never by a generic update.
 */
#[Fillable([
    'client_id',
    'membership_plan_id',
    'starts_at',
    'ends_at',
    'price',
    'currency',
    'paid_amount',
    'remaining_amount',
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

    public function canTransitionTo(MembershipStatus $target): bool
    {
        return $this->status->canTransitionTo($target);
    }
}
