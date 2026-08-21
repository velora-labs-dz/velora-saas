<?php

namespace App\Models;

use App\Enums\OrganizationRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;

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
}
