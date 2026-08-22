<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;

/**
 * Maps docs/TESTING.md §3's RBAC matrix onto the Clients entity:
 *
 *  - Owner:  full access (view, create, edit, archive, restore)
 *  - Admin:  same as Owner for Clients — the Owner-only carve-out in
 *            OrganizationPolicy is specifically about ownership transfer,
 *            which has no equivalent concept here
 *  - Staff:  operational mutations (view, create, edit) but no destructive
 *            action (archive/restore) unless explicitly permitted — it
 *            isn't, for Clients
 *  - Viewer: read-only (view only)
 *
 * Every method re-derives the actor's membership from the *current*
 * organization context rather than trusting $client->organization — the
 * caller is responsible for having already resolved $client through
 * CurrentOrganization (see ClientController), so by the time a policy
 * method runs, "does this client belong to this org" has already been
 * settled by the query itself, not by this class. See docs/SECURITY.md §5.
 */
class ClientPolicy
{
    public function viewAny(User $user, Organization $organization): bool
    {
        return $this->activeMembership($user, $organization) !== null;
    }

    public function view(User $user, Client $client, Organization $organization): bool
    {
        return $this->viewAny($user, $organization);
    }

    public function create(User $user, Organization $organization): bool
    {
        $membership = $this->activeMembership($user, $organization);

        return $membership !== null && $membership->role->canManageClients();
    }

    public function update(User $user, Client $client, Organization $organization): bool
    {
        return $this->create($user, $organization);
    }

    public function archive(User $user, Client $client, Organization $organization): bool
    {
        $membership = $this->activeMembership($user, $organization);

        return $membership !== null && $membership->role->canManageOrganization();
    }

    public function restore(User $user, Client $client, Organization $organization): bool
    {
        return $this->archive($user, $client, $organization);
    }

    private function activeMembership(User $user, Organization $organization): ?OrganizationMember
    {
        return $organization->members()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();
    }
}
