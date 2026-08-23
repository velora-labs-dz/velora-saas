<?php

namespace App\Models;

use App\Enums\OrganizationRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrganizationMember extends Model
{
    use AsPivot;

    protected $table = 'organization_members';

    /**
     * Nothing here is mass-assignable. organization_id, user_id and role are
     * exactly the fields a request must never be able to set directly — every
     * row is built with explicit property assignment inside a trusted Action.
     */
    protected $fillable = [];

    protected function casts(): array
    {
        return [
            'role' => OrganizationRole::class,
            'is_active' => 'boolean',
            'joined_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Appointments where this member is the servicing staff — only
     * meaningful for rows with role=staff, but the relation itself isn't
     * role-restricted; that's enforced at assignment time (see
     * StoreAppointmentRequest), not by the relation.
     */
    public function appointmentsAsEmployee(): HasMany
    {
        return $this->hasMany(Appointment::class, 'employee_id');
    }

    /**
     * Active Owner rows for an organization, optionally excluding one row
     * (the member currently being demoted/removed) and optionally locked
     * for update — used by the "must always have at least one owner"
     * invariant, which needs a consistent read inside a transaction to be
     * race-safe against two concurrent demotions.
     */
    public function scopeActiveOwners(
        \Illuminate\Database\Eloquent\Builder $query,
        int $organizationId,
        ?int $excludingMemberId = null,
    ): \Illuminate\Database\Eloquent\Builder {
        $query = $query
            ->where('organization_id', $organizationId)
            ->where('role', OrganizationRole::Owner)
            ->where('is_active', true);

        if ($excludingMemberId !== null) {
            $query->where('id', '!=', $excludingMemberId);
        }

        return $query;
    }
}
