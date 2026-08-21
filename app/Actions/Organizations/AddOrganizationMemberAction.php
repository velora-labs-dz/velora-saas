<?php

namespace App\Actions\Organizations;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AddOrganizationMemberAction
{
    /**
     * Add an existing user to an organization with the given role.
     *
     * Authorization (who is allowed to do this, and whether $role may be
     * Owner) is the caller's responsibility via OrganizationPolicy::addMember
     * — this class only enforces data invariants.
     */
    public function handle(Organization $organization, User $targetUser, OrganizationRole $role): OrganizationMember
    {
        return DB::transaction(function () use ($organization, $targetUser, $role) {
            $alreadyMember = OrganizationMember::query()
                ->where('organization_id', $organization->id)
                ->where('user_id', $targetUser->id)
                ->lockForUpdate()
                ->exists();

            if ($alreadyMember) {
                throw ValidationException::withMessages([
                    'email' => 'This user is already a member of the organization.',
                ]);
            }

            $member = new OrganizationMember();
            $member->organization_id = $organization->id;
            $member->user_id = $targetUser->id;
            $member->role = $role;
            $member->is_active = true;
            $member->joined_at = now();
            $member->save();

            return $member;
        });
    }
}
