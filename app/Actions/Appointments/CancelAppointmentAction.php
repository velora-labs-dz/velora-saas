<?php

namespace App\Actions\Appointments;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Illuminate\Validation\ValidationException;

class CancelAppointmentAction
{
    public function handle(Appointment $appointment, ?string $reason): Appointment
    {
        if (! $appointment->canTransitionTo(AppointmentStatus::Cancelled)) {
            throw ValidationException::withMessages([
                'status' => 'This appointment is already cancelled.',
            ]);
        }

        $appointment->status = AppointmentStatus::Cancelled;
        $appointment->cancellation_reason = $reason;
        $appointment->save();

        return $appointment;
    }
}
