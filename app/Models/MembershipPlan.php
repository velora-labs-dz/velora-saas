<?php

namespace App\Models;

use App\Enums\DurationUnit;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * `active`, and `organization_id` are intentionally excluded from
 * Fillable and are set explicitly by trusted server code, never derived
 * from request input. See docs/SECURITY.md §6 (mass assignment). Unlike
 * Client/Service, there is no `created_by` column here — not in
 * docs/DATABASE_SCHEMA.md §6's target schema for membership_plans.
 */
#[Fillable([
    'name',
    'description',
    'duration_value',
    'duration_unit',
    'price',
    'currency',
    'sessions_limit',
    'visits_per_period',
    'freeze_allowed',
    'freeze_limit',
])]
class MembershipPlan extends Model
{
    /** @use HasFactory<\Database\Factories\MembershipPlanFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'duration_unit' => DurationUnit::class,
            'duration_value' => 'integer',
            'price' => 'decimal:2',
            'sessions_limit' => 'integer',
            'visits_per_period' => 'integer',
            'freeze_allowed' => 'boolean',
            'freeze_limit' => 'integer',
            'active' => 'boolean',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    /**
     * The date a Membership created today against this plan would end,
     * given duration_value/duration_unit. See CreateMembershipAction.
     */
    public function endDateFrom(\DateTimeInterface $startsAt): \Carbon\Carbon
    {
        $date = \Carbon\Carbon::parse($startsAt);

        return match ($this->duration_unit) {
            DurationUnit::Days => $date->addDays($this->duration_value),
            DurationUnit::Weeks => $date->addWeeks($this->duration_value),
            DurationUnit::Months => $date->addMonths($this->duration_value),
        };
    }
}
