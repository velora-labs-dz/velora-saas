<?php

namespace App\Actions\Payments;

use App\Models\Membership;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RecordPaymentAction
{
    /**
     * @param  array<string, mixed>  $attributes  Already-validated attributes
     *                                             from RecordPaymentRequest —
     *                                             client_id, membership_id
     *                                             (nullable), amount,
     *                                             currency, method,
     *                                             reference, paid_at, notes.
     *
     * When a membership is linked, this is the write side of the balance
     * tracking docs/ROADMAP.md's Step 8 feature list calls for:
     * Membership.paid_amount goes up by the payment amount and
     * remaining_amount is recalculated. Wrapped in a transaction so the
     * payment and the balance update can't land only one succeeds.
     */
    public function handle(Organization $organization, array $attributes, ?User $recordedBy): Payment
    {
        return DB::transaction(function () use ($organization, $attributes, $recordedBy) {
            $payment = new Payment($attributes);
            $payment->organization_id = $organization->id;
            $payment->recorded_by = $recordedBy?->id;
            $payment->save();

            if ($payment->membership_id !== null) {
                $membership = Membership::query()->lockForUpdate()->findOrFail($payment->membership_id);
                $membership->paid_amount = bcadd((string) $membership->paid_amount, (string) $payment->amount, 2);
                $membership->recalculateBalance();
                $membership->save();
            }

            return $payment;
        });
    }
}
