<?php

namespace App\Actions\Organizations;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\OrganizationMember;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateOrganizationMemberRoleAction
{
    /**
     * Change a member's role.
     *
     * Authorization (who is allowed to do this, and whether Owner may be
     * granted/revoked) is the caller's responsibility via
     * OrganizationPolicy::updateMemberRole — this class only enforces the
     * "an organization must always have at least one active owner" data
     * invariant, which requires a locking read against current data to be
     * race-safe against two concurrent demotions.
     */
    public function handle(Organization $organization, OrganizationMember $target, OrganizationRole $newRole): OrganizationMember
    {
        return DB::transaction(function () use ($organization, $target, $newRole) {
            if ($target->role === OrganizationRole::Owner && $newRole !== OrganizationRole::Owner) {
                $remainingOwners = OrganizationMember::query()
                    ->activeOwners($organization->id, excludingMemberId: $target->id)
                    ->lockForUpdate()
                    ->count();

                if ($remainingOwners < 1) {
                    throw ValidationException::withMessages([
                        'role' => 'An organization must always have at least one owner. Promote another member to owner first.',
                    ]);
                }
            }

            $target->role = $newRole;
            $target->save();

            return $target;
        });
    }
}
