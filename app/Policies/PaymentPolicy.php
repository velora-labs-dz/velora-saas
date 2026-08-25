<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\Payment;
use App\Models\User;

/**
 * Recording a payment sits at the general canManagePayments() tier
 * (Owner/Admin/Staff); void and refund require canCorrectPayments()
 * (Owner/Admin only) — see OrganizationRole. Viewer is read-only
 * everywhere.
 */
class PaymentPolicy
{
    public function viewAny(User $user, Organization $organization): bool
    {
        return $this->activeMembership($user, $organization) !== null;
    }

    public function view(User $user, Payment $payment, Organization $organization): bool
    {
        return $this->viewAny($user, $organization);
    }

    public function create(User $user, Organization $organization): bool
    {
        $membership = $this->activeMembership($user, $organization);

        return $membership !== null && $membership->role->canManagePayments();
    }

    public function void(User $user, Payment $payment, Organization $organization): bool
    {
        $membership = $this->activeMembership($user, $organization);

        return $membership !== null && $membership->role->canCorrectPayments();
    }

    public function refund(User $user, Payment $payment, Organization $organization): bool
    {
        return $this->void($user, $payment, $organization);
    }

    private function activeMembership(User $user, Organization $organization): ?OrganizationMember
    {
        return $organization->members()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();
    }
}
