<?php

namespace App\Actions\Payments;

use App\Enums\PaymentStatus;
use App\Models\Membership;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RefundPaymentAction
{
    /**
     * Refunding is real money genuinely being given back — partial or
     * full, and can be called more than once on the same payment (each
     * refund adds to refunded_amount) as long as the cumulative total
     * never exceeds what was actually paid. Only legal from Recorded or
     * already-Refunded (see PaymentStatus::canRefund()); a Voided payment
     * never happened, so there's nothing to refund.
     */
    public function handle(Payment $payment, string $amount, ?string $reason): Payment
    {
        if (! $payment->status->canRefund()) {
            throw ValidationException::withMessages([
                'status' => "A {$payment->status->value} payment cannot be refunded.",
            ]);
        }

        $newRefundedTotal = bcadd((string) $payment->refunded_amount, $amount, 2);

        if (bccomp($newRefundedTotal, (string) $payment->amount, 2) === 1) {
            throw ValidationException::withMessages([
                'amount' => 'The refund total cannot exceed the original payment amount.',
            ]);
        }

        return DB::transaction(function () use ($payment, $amount, $reason, $newRefundedTotal) {
            if ($payment->membership_id !== null) {
                $membership = Membership::query()->lockForUpdate()->findOrFail($payment->membership_id);
                $membership->paid_amount = bcsub((string) $membership->paid_amount, $amount, 2);
                $membership->recalculateBalance();
                $membership->save();
            }

            $payment->refunded_amount = $newRefundedTotal;
            $payment->refund_reason = $reason;
            $payment->status = PaymentStatus::Refunded;
            $payment->save();

            return $payment;
        });
    }
}
