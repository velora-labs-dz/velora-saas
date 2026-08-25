<?php

namespace App\Models;

use App\Enums\OrganizationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * `status` and `created_by` are intentionally excluded from Fillable.
 * They are set explicitly by trusted server code (see CreateOrganizationAction),
 * never derived from request input. See docs/SECURITY.md §6 (mass assignment).
 */
#[Fillable([
    'name',
    'slug',
    'legal_name',
    'timezone',
    'locale',
    'currency',
    'contact_email',
    'contact_phone',
    'address_line_1',
    'address_line_2',
    'city',
    'wilaya',
    'postal_code',
    'country_code',
])]
class Organization extends Model
{
    /** @use HasFactory<\Database\Factories\OrganizationFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => OrganizationStatus::class,
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): HasMany
    {
        return $this->hasMany(OrganizationMember::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function membershipPlans(): HasMany
    {
        return $this->hasMany(MembershipPlan::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_members')
            ->using(OrganizationMember::class)
            ->withPivot(['role', 'is_active', 'joined_at'])
            ->withTimestamps();
    }

    /**
     * Use the slug as the route/public identifier instead of the numeric id.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
