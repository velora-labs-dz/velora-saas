<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;

class OrganizationPolicy
{
    /**
     * A member (any active role) may view the organization.
     */
    public function view(User $user, Organization $organization): bool
    {
        return $this->activeMembership($user, $organization) !== null;
    }

    /**
     * Switching the active organization requires the same membership as viewing it.
     */
    public function switchTo(User $user, Organization $organization): bool
    {
        return $this->view($user, $organization);
    }

    /**
     * Only owner/admin may change organization settings.
     */
    public function update(User $user, Organization $organization): bool
    {
        $membership = $this->activeMembership($user, $organization);

        return $membership !== null && $membership->role->canManageOrganization();
    }

    private function activeMembership(User $user, Organization $organization): ?OrganizationMember
    {
        return $organization->members()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();
    }
}
