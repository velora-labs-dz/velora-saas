<?php

namespace App\Policies;

use App\Enums\OrganizationRole;
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

    /**
     * Any active member may see who else is in the organization — this is
     * informational, not administrative (matches Viewer being "read only",
     * not "sees nothing").
     */
    public function viewMembers(User $user, Organization $organization): bool
    {
        return $this->view($user, $organization);
    }

    /**
     * Owner/Admin may add members. Only an Owner may add someone directly
     * as Owner (ownership transfer is Owner-only, per TESTING.md's RBAC
     * requirement that Admin has "administrative access but not ownership
     * transfer").
     */
    public function addMember(User $user, Organization $organization, OrganizationRole $role): bool
    {
        $actor = $this->activeMembership($user, $organization);

        if (! $actor || ! $actor->role->canManageOrganization()) {
            return false;
        }

        if ($role === OrganizationRole::Owner && ! $actor->role->isOwner()) {
            return false;
        }

        return true;
    }

    /**
     * Owner/Admin may change a member's role, with one restriction:
     *  - only an Owner may grant Owner or change an existing Owner's role
     *    (ownership transfer is Owner-only)
     *
     * This single rule already covers self-privilege-escalation: a non-Owner
     * can never grant themselves Owner, because granting Owner requires the
     * actor to already be an Owner. It deliberately does NOT block a self
     * *demotion* — an Owner stepping down (e.g. to Admin) is a legitimate
     * self-service action, not an escalation. The "an organization must
     * always have at least one Owner" invariant is what stops the sole
     * remaining Owner from demoting themselves (or anyone else); that check
     * lives in UpdateOrganizationMemberRoleAction since it needs a locking
     * read against current data, and correctly applies whether the target
     * is the actor or someone else.
     */
    public function updateMemberRole(
        User $user,
        Organization $organization,
        OrganizationMember $target,
        OrganizationRole $newRole,
    ): bool {
        $actor = $this->activeMembership($user, $organization);

        if (! $actor || ! $actor->role->canManageOrganization()) {
            return false;
        }

        if ($target->organization_id !== $organization->id) {
            return false;
        }

        if (($target->role === OrganizationRole::Owner || $newRole === OrganizationRole::Owner) && ! $actor->role->isOwner()) {
            return false;
        }

        return true;
    }

    /**
     * Owner/Admin may remove a member, except an Admin may not remove an
     * Owner. Nobody removes themselves through this action — see
     * leaveOrganization(). The "must always have one owner" invariant is
     * a live-count check enforced in RemoveOrganizationMemberAction, not
     * here, since it needs a locking read against current data.
     */
    public function removeMember(User $user, Organization $organization, OrganizationMember $target): bool
    {
        $actor = $this->activeMembership($user, $organization);

        if (! $actor || ! $actor->role->canManageOrganization()) {
            return false;
        }

        if ($target->organization_id !== $organization->id) {
            return false;
        }

        if ($target->user_id === $user->id) {
            return false;
        }

        if ($target->role === OrganizationRole::Owner && ! $actor->role->isOwner()) {
            return false;
        }

        return true;
    }

    /**
     * Any active member may leave voluntarily, regardless of role. The
     * "can't leave if you're the last owner" invariant is enforced in
     * LeaveOrganizationAction for the same locking-read reason as above.
     */
    public function leaveOrganization(User $user, Organization $organization, OrganizationMember $target): bool
    {
        return $target->user_id === $user->id
            && $target->organization_id === $organization->id
            && $target->is_active;
    }

    private function activeMembership(User $user, Organization $organization): ?OrganizationMember
    {
        return $organization->members()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();
    }
}

