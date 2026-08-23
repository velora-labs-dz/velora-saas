<?php

namespace App\Actions\Memberships;

use App\Enums\MembershipStatus;
use App\Models\Membership;
use Illuminate\Validation\ValidationException;

class CancelMembershipAction
{
    public function handle(Membership $membership, string $reason): Membership
    {
        if (! $membership->canTransitionTo(MembershipStatus::Cancelled)) {
            throw ValidationException::withMessages([
                'status' => "A {$membership->status->value} membership cannot be cancelled.",
            ]);
        }

        $membership->status = MembershipStatus::Cancelled;
        $membership->cancelled_at = now();
        $membership->cancellation_reason = $reason;
        $membership->save();

        return $membership;
    }
}
