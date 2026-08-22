<?php

namespace App\Enums;

enum OrganizationRole: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Staff = 'staff';
    case Viewer = 'viewer';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::Admin => 'Admin',
            self::Staff => 'Staff',
            self::Viewer => 'Viewer',
        };
    }

    /**
     * Roles allowed to manage the organization itself (settings, membership).
     * This is deliberately narrow — full RBAC/permission matrix is a later step.
     */
    public function canManageOrganization(): bool
    {
        return in_array($this, [self::Owner, self::Admin], true);
    }

    public function isOwner(): bool
    {
        return $this === self::Owner;
    }

    /**
     * Roles allowed to perform operational mutations on organization-owned
     * business entities (e.g. Clients) — create/edit, but not necessarily
     * destructive actions. Per docs/TESTING.md §3: Owner/Admin/Staff can
     * mutate operationally; Viewer cannot.
     */
    public function canManageClients(): bool
    {
        return $this !== self::Viewer;
    }

    /**
     * Same tier as canManageClients() today (everyone but Viewer can
     * create/edit/activate/deactivate a service) — kept as its own method
     * rather than reused because Services and Clients are different
     * entities that may reasonably diverge later (e.g. if service pricing
     * changes need Owner/Admin-only approval down the line).
     */
    public function canManageServices(): bool
    {
        return $this !== self::Viewer;
    }
}
