<?php

namespace App\Actions\Appointments;

use App\Models\Appointment;
use App\Models\Organization;
use App\Models\User;

class CreateAppointmentAction
{
    /**
     * @param  array<string, mixed>  $attributes  Already-validated attributes
     *                                             from StoreAppointmentRequest.
     *                                             Conflict checking (staff and
     *                                             client double-booking)
     *                                             already happened in the
     *                                             request, not here.
     */
    public function handle(Organization $organization, array $attributes, User $creator): Appointment
    {
        $appointment = new Appointment($attributes);
        $appointment->organization_id = $organization->id;
        $appointment->created_by = $creator->id;
        $appointment->save();

        return $appointment;
    }
}
