<?php

namespace App\Actions\Organizations;

use App\Enums\OrganizationRole;
use App\Enums\OrganizationStatus;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateOrganizationAction
{
    /**
     * Create a new organization and make $user its owner.
     *
     * @param  array<string, mixed>  $data  Validated attributes (name, legal_name, timezone, locale,
     *                                       currency, contact/address fields). Never contains slug,
     *                                       status, created_by, or role — those are set here only.
     */
    public function handle(User $user, array $data): Organization
    {
        return DB::transaction(function () use ($user, $data) {
            $organization = new Organization($data);
            $organization->slug = $this->uniqueSlug($data['name']);
            $organization->status = OrganizationStatus::Active;
            $organization->created_by = $user->id;
            $organization->save();

            $member = new OrganizationMember();
            $member->organization_id = $organization->id;
            $member->user_id = $user->id;
            $member->role = OrganizationRole::Owner;
            $member->is_active = true;
            $member->joined_at = now();
            $member->save();

            return $organization;
        });
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'organization';
        $slug = $base;
        $suffix = 1;

        while (Organization::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
