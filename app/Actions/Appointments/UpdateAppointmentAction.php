<?php

namespace App\Actions\Appointments;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Illuminate\Validation\ValidationException;

class UpdateAppointmentAction
{
    /**
     * @param  array<string, mixed>  $attributes  Already-validated attributes
     *                                             from UpdateAppointmentRequest,
     *                                             including conflict checking.
     */
    public function handle(Appointment $appointment, array $attributes): Appointment
    {
        if ($appointment->status !== AppointmentStatus::Scheduled) {
            throw ValidationException::withMessages([
                'status' => 'A cancelled appointment cannot be edited.',
            ]);
        }

        $appointment->fill($attributes);
        $appointment->save();

        return $appointment;
    }
}
