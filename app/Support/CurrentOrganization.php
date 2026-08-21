<?php

namespace App\Support;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\OrganizationMember;
use RuntimeException;

/**
 * Request-scoped holder for the authenticated user's active organization.
 *
 * This class does not resolve membership itself and must never be trusted
 * blindly — it is only ever populated by ResolveCurrentOrganization
 * middleware, and only after that middleware has verified an active
 * OrganizationMember row exists for the authenticated user. Nothing else
 * should call set().
 */
class CurrentOrganization
{
    private ?Organization $organization = null;

    private ?OrganizationMember $membership = null;

    public function set(Organization $organization, OrganizationMember $membership): void
    {
        $this->organization = $organization;
        $this->membership = $membership;
    }

    public function clear(): void
    {
        $this->organization = null;
        $this->membership = null;
    }

    public function exists(): bool
    {
        return $this->organization !== null;
    }

    public function organization(): ?Organization
    {
        return $this->organization;
    }

    public function membership(): ?OrganizationMember
    {
        return $this->membership;
    }

    public function id(): ?int
    {
        return $this->organization?->id;
    }

    public function role(): ?OrganizationRole
    {
        return $this->membership?->role;
    }

    public function organizationOrFail(): Organization
    {
        if (! $this->organization) {
            throw new RuntimeException('No current organization has been resolved for this request.');
        }

        return $this->organization;
    }
}
