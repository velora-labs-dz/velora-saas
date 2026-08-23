<?php

namespace App\Http\Requests\Appointments;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Unlike CancelMembershipRequest, a reason is optional here — cancelling a
 * booking is a routine, frequent action (reschedules, no-shows caught
 * early, client changed their mind), not the higher-stakes step
 * cancelling a paid membership is. Forcing a reason on every cancel would
 * just train staff to type filler text.
 */
class CancelAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Real authorization happens via AppointmentPolicy::cancel in the
        // controller, once the current organization is resolved.
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'cancellation_reason' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
