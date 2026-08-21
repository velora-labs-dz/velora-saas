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
}
