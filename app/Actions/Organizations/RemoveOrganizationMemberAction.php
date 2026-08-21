<?php

namespace App\Actions\Organizations;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\OrganizationMember;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RemoveOrganizationMemberAction
{
    /**
     * Remove a member from an organization.
     *
     * Authorization is the caller's responsibility via
     * OrganizationPolicy::removeMember — this class only enforces the "an
     * organization must always have at least one active owner" invariant.
     */
    public function handle(Organization $organization, OrganizationMember $target): void
    {
        DB::transaction(function () use ($organization, $target) {
            if ($target->role === OrganizationRole::Owner) {
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

            $target->delete();
        });
    }
}
