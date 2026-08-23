<?php

namespace App\Actions\Memberships;

use App\Enums\MembershipStatus;
use App\Models\Membership;
use Illuminate\Validation\ValidationException;

class UpdateMembershipAction
{
    /**
     * @param  array<string, mixed>  $attributes  Already-validated attributes
     *                                             from UpdateMembershipRequest.
     *
     * Editable only while Draft. Once a membership is Active, its dates
     * and price are the terms the client actually agreed to and started
     * using — changing them retroactively is a billing/refund concern
     * (docs/LATER.md territory: Invoice/CreditNote), not a plain edit.
     */
    public function handle(Membership $membership, array $attributes): Membership
    {
        if ($membership->status !== MembershipStatus::Draft) {
            throw ValidationException::withMessages([
                'status' => 'Only a draft membership can be edited. Cancel and re-assign instead.',
            ]);
        }

        $membership->fill($attributes);
        $membership->save();

        return $membership;
    }
}
