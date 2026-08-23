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

    /**
     * Same tier as canManageClients()/canManageServices(): create, edit,
     * assign, activate, freeze, and unfreeze are all reversible/non-
     * destructive operational actions available to Owner/Admin/Staff.
     */
    public function canManageMemberships(): bool
    {
        return $this !== self::Viewer;
    }

    /**
     * Cancelling a membership ends a client's paid access and can't be
     * undone by a further transition (Cancelled is terminal — see
     * MembershipStatus::allowedTransitions()). That's a materially bigger
     * consequence than freeze/unfreeze, so it sits at the same elevated
     * tier as ClientPolicy::archive rather than the general manage tier.
     */
    public function canCancelMemberships(): bool
    {
        return $this->canManageOrganization();
    }

    /**
     * Same tier as canManageClients()/canManageServices() — appointments
     * don't carry the same elevated-cancel treatment Memberships do
     * (cancelling a booking isn't ending a paid, terminal commitment the
     * way cancelling a membership is), so create/edit/cancel all sit at
     * the general manage tier.
     */
    public function canManageAppointments(): bool
    {
        return $this !== self::Viewer;
    }
}
