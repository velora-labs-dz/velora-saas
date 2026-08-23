<?php

namespace App\Actions\Memberships;

use App\Enums\MembershipStatus;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\User;

class CreateMembershipAction
{
    /**
     * @param  array<string, mixed>  $attributes  Already-validated attributes
     *                                             from StoreMembershipRequest —
     *                                             includes client_id,
     *                                             membership_plan_id,
     *                                             starts_at, ends_at, price,
     *                                             currency, notes.
     *
     * Every assignment starts life as Draft, never Active — activation is
     * its own explicit step (ActivateMembershipAction) so "this membership
     * exists" and "this membership's access period has actually begun"
     * stay two separate, auditable moments. See MembershipStatus.
     */
    public function handle(Organization $organization, array $attributes, User $creator): Membership
    {
        $membership = new Membership($attributes);
        $membership->organization_id = $organization->id;
        $membership->created_by = $creator->id;
        $membership->status = MembershipStatus::Draft;
        $membership->save();

        return $membership;
    }
}
