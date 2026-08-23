<?php

namespace App\Actions\Memberships;

use App\Enums\MembershipStatus;
use App\Models\Membership;
use Illuminate\Validation\ValidationException;

/**
 * Manual-only, by design — no auto-resume date. A frozen membership stays
 * frozen until a staff member explicitly unfreezes it. Automatic
 * expiry/resume scheduling is docs/ROADMAP.md §2.3 ("expiry automation")
 * territory, not Phase 1.
 */
class UnfreezeMembershipAction
{
    public function handle(Membership $membership): Membership
    {
        if (! $membership->canTransitionTo(MembershipStatus::Active)) {
            throw ValidationException::withMessages([
                'status' => "A {$membership->status->value} membership cannot be unfrozen.",
            ]);
        }

        $membership->status = MembershipStatus::Active;
        $membership->frozen_at = null;
        $membership->save();

        return $membership;
    }
}
