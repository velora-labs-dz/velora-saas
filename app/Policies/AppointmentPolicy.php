<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;

/**
 * Same tier structure as ClientPolicy/ServicePolicy: Owner/Admin/Staff can
 * create, edit, and cancel; Viewer is read-only. See
 * OrganizationRole::canManageAppointments().
 */
class AppointmentPolicy
{
    public function viewAny(User $user, Organization $organization): bool
    {
        return $this->activeMembership($user, $organization) !== null;
    }

    public function view(User $user, Appointment $appointment, Organization $organization): bool
    {
        return $this->viewAny($user, $organization);
    }

    public function create(User $user, Organization $organization): bool
    {
        $membership = $this->activeMembership($user, $organization);

        return $membership !== null && $membership->role->canManageAppointments();
    }

    public function update(User $user, Appointment $appointment, Organization $organization): bool
    {
        return $this->create($user, $organization);
    }

    public function cancel(User $user, Appointment $appointment, Organization $organization): bool
    {
        return $this->create($user, $organization);
    }

    private function activeMembership(User $user, Organization $organization): ?OrganizationMember
    {
        return $organization->members()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();
    }
}
