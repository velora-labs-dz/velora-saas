<?php

namespace App\Actions\Memberships;

use App\Enums\MembershipStatus;
use App\Models\Membership;
use Illuminate\Validation\ValidationException;

class FreezeMembershipAction
{
    public function handle(Membership $membership): Membership
    {
        if (! $membership->canTransitionTo(MembershipStatus::Frozen)) {
            throw ValidationException::withMessages([
                'status' => "A {$membership->status->value} membership cannot be frozen.",
            ]);
        }

        if (! $membership->membershipPlan->freeze_allowed) {
            throw ValidationException::withMessages([
                'status' => 'This membership\'s plan does not allow freezing.',
            ]);
        }

        $membership->status = MembershipStatus::Frozen;
        $membership->frozen_at = now();
        $membership->save();

        return $membership;
    }
}
