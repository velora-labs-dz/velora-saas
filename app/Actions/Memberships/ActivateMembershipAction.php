<?php

namespace App\Actions\Memberships;

use App\Enums\MembershipStatus;
use App\Models\Membership;
use Illuminate\Validation\ValidationException;

class ActivateMembershipAction
{
    public function handle(Membership $membership): Membership
    {
        if (! $membership->canTransitionTo(MembershipStatus::Active)) {
            throw ValidationException::withMessages([
                'status' => "A {$membership->status->value} membership cannot be activated.",
            ]);
        }

        $membership->status = MembershipStatus::Active;
        $membership->activated_at = now();
        $membership->save();

        return $membership;
    }
}
