<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;

/**
 * Same tier structure as AppointmentPolicy: Owner/Admin/Staff can check
 * clients in and out; Viewer is read-only. See
 * OrganizationRole::canManageAttendance().
 */
class AttendancePolicy
{
    public function viewAny(User $user, Organization $organization): bool
    {
        return $this->activeMembership($user, $organization) !== null;
    }

    public function view(User $user, Attendance $attendance, Organization $organization): bool
    {
        return $this->viewAny($user, $organization);
    }

    public function checkIn(User $user, Organization $organization): bool
    {
        $membership = $this->activeMembership($user, $organization);

        return $membership !== null && $membership->role->canManageAttendance();
    }

    public function checkOut(User $user, Attendance $attendance, Organization $organization): bool
    {
        return $this->checkIn($user, $organization);
    }

    private function activeMembership(User $user, Organization $organization): ?OrganizationMember
    {
        return $organization->members()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();
    }
}
