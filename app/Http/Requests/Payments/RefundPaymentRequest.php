<?php

namespace App\Http\Requests\Payments;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Whether this specific refund amount fits within what's actually left to
 * refund on this specific payment is a business-state rule tied to the
 * payment's current refunded_amount — enforced in RefundPaymentAction,
 * not here. This request only validates the shape of the input (a
 * positive number, a reason).
 */
class RefundPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Real authorization happens via PaymentPolicy::refund in the
        // controller, once the current organization is resolved.
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999.99'],
            'refund_reason' => ['required', 'string', 'max:1000'],
        ];
    }
}
