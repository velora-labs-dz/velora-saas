<?php

namespace App\Actions\Memberships;

use App\Enums\MembershipStatus;
use App\Models\Membership;
use Illuminate\Validation\ValidationException;

/**
 * Manually triggered by staff — there is no scheduled job walking past-due
 * memberships to Expired automatically. That's docs/ROADMAP.md §2.3
 * ("expiry automation"), explicitly later scope. For Phase 1, a membership
 * whose ends_at has passed just sits Active until someone marks it.
 */
class ExpireMembershipAction
{
    public function handle(Membership $membership): Membership
    {
        if (! $membership->canTransitionTo(MembershipStatus::Expired)) {
            throw ValidationException::withMessages([
                'status' => "A {$membership->status->value} membership cannot be expired.",
            ]);
        }

        $membership->status = MembershipStatus::Expired;
        $membership->save();

        return $membership;
    }
}
