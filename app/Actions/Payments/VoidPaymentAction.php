<?php

namespace App\Actions\Payments;

use App\Enums\PaymentStatus;
use App\Models\Membership;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VoidPaymentAction
{
    /**
     * Voiding means "this payment should never have been recorded" — the
     * full amount is reversed out of a linked membership's paid_amount,
     * not just some of it. Only legal from Recorded (see
     * PaymentStatus::canVoid()) — once any refund has been applied, real
     * money has genuinely changed hands and void no longer makes sense;
     * use RefundPaymentAction instead.
     */
    public function handle(Payment $payment, string $reason): Payment
    {
        if (! $payment->status->canVoid()) {
            throw ValidationException::withMessages([
                'status' => "A {$payment->status->value} payment cannot be voided.",
            ]);
        }

        return DB::transaction(function () use ($payment, $reason) {
            if ($payment->membership_id !== null) {
                $membership = Membership::query()->lockForUpdate()->findOrFail($payment->membership_id);
                $membership->paid_amount = bcsub((string) $membership->paid_amount, (string) $payment->amount, 2);
                $membership->recalculateBalance();
                $membership->save();
            }

            $payment->status = PaymentStatus::Voided;
            $payment->voided_at = now();
            $payment->void_reason = $reason;
            $payment->save();

            return $payment;
        });
    }
}
