<?php

namespace App\Policies;

use App\Models\Membership;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;

/**
 * Maps docs/TESTING.md §6's RBAC expectations onto Memberships:
 *
 *  - Owner/Admin: full access, including cancel.
 *  - Staff: can view, assign (create), edit a draft, activate, freeze,
 *    and unfreeze — but not cancel. Cancelling ends paid access and is
 *    terminal (see MembershipStatus), so it's held to the same
 *    Owner/Admin-only bar as ClientPolicy::archive.
 *  - Viewer: read-only.
 *
 * This policy only answers "is this actor allowed to attempt this kind of
 * action at all". Whether the *specific* transition is legal for this
 * membership's *current* status (e.g. you can't freeze a draft) is a
 * business rule enforced by MembershipStatus::canTransitionTo() inside the
 * relevant Action, not here — those are two different questions.
 */
class MembershipPolicy
{
    public function viewAny(User $user, Organization $organization): bool
    {
        return $this->activeMembership($user, $organization) !== null;
    }

    public function view(User $user, Membership $membership, Organization $organization): bool
    {
        return $this->viewAny($user, $organization);
    }

    public function create(User $user, Organization $organization): bool
    {
        $membership = $this->activeMembership($user, $organization);

        return $membership !== null && $membership->role->canManageMemberships();
    }

    public function update(User $user, Membership $membership, Organization $organization): bool
    {
        return $this->create($user, $organization);
    }

    public function activate(User $user, Membership $membership, Organization $organization): bool
    {
        return $this->create($user, $organization);
    }

    public function freeze(User $user, Membership $membership, Organization $organization): bool
    {
        return $this->create($user, $organization);
    }

    public function unfreeze(User $user, Membership $membership, Organization $organization): bool
    {
        return $this->create($user, $organization);
    }

    public function expire(User $user, Membership $membership, Organization $organization): bool
    {
        return $this->create($user, $organization);
    }

    public function cancel(User $user, Membership $membership, Organization $organization): bool
    {
        $actorMembership = $this->activeMembership($user, $organization);

        return $actorMembership !== null && $actorMembership->role->canCancelMemberships();
    }

    private function activeMembership(User $user, Organization $organization): ?OrganizationMember
    {
        return $organization->members()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();
    }
}
