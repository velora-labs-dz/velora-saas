<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\Service;
use App\Models\User;

/**
 * Owner/Admin/Staff can create, edit, activate, and deactivate services;
 * Viewer is read-only. Unlike ClientPolicy::archive, activating/deactivating
 * a service isn't elevated to Owner/Admin-only — it's a lower-stakes,
 * reversible toggle (no data is removed, nothing cascades), so it sits at
 * the same tier as create/edit. See OrganizationRole::canManageServices().
 */
class ServicePolicy
{
    public function viewAny(User $user, Organization $organization): bool
    {
        return $this->activeMembership($user, $organization) !== null;
    }

    public function view(User $user, Service $service, Organization $organization): bool
    {
        return $this->viewAny($user, $organization);
    }

    public function create(User $user, Organization $organization): bool
    {
        $membership = $this->activeMembership($user, $organization);

        return $membership !== null && $membership->role->canManageServices();
    }

    public function update(User $user, Service $service, Organization $organization): bool
    {
        return $this->create($user, $organization);
    }

    public function toggleStatus(User $user, Service $service, Organization $organization): bool
    {
        return $this->create($user, $organization);
    }

    private function activeMembership(User $user, Organization $organization): ?OrganizationMember
    {
        return $organization->members()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();
    }
}
