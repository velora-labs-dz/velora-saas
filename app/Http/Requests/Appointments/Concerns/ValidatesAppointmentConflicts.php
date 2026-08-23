<?php

namespace App\Http\Requests\Appointments\Concerns;

use App\Models\Appointment;
use App\Support\CurrentOrganization;
use Illuminate\Contracts\Validation\Validator;

/**
 * Shared by StoreAppointmentRequest and UpdateAppointmentRequest so the
 * overlap ("conflict") logic — the actual business rule docs/TESTING.md
 * §7 calls "conflict rules as they are implemented" — has exactly one
 * implementation, not two copies that could quietly drift apart.
 */
trait ValidatesAppointmentConflicts
{
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->rejectConflicts($validator);
        });
    }

    /**
     * Blocks double-booking on both sides of the appointment: the same
     * staff member and the same client can't have two overlapping
     * scheduled appointments. Cancelled appointments never count as a
     * conflict. Two ranges overlap when one starts before the other ends
     * on both sides — the standard interval-overlap test.
     */
    protected function rejectConflicts(Validator $validator): void
    {
        if ($validator->errors()->hasAny(['employee_id', 'client_id', 'starts_at', 'ends_at'])) {
            // Don't run overlap queries against inputs that already
            // failed basic validation (missing dates, non-existent ids).
            return;
        }

        $organizationId = app(CurrentOrganization::class)->id();
        $startsAt = $this->input('starts_at');
        $endsAt = $this->input('ends_at');
        $excludingId = $this->route('appointment');

        $overlapping = fn ($column, $value) => Appointment::query()
            ->where('organization_id', $organizationId)
            ->where($column, $value)
            ->where('status', 'scheduled')
            ->when($excludingId, fn ($query) => $query->where('id', '!=', $excludingId))
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->exists();

        if ($overlapping('employee_id', $this->input('employee_id'))) {
            $validator->errors()->add('employee_id', 'This staff member already has an appointment during that time.');
        }

        if ($overlapping('client_id', $this->input('client_id'))) {
            $validator->errors()->add('client_id', 'This client already has an appointment during that time.');
        }
    }
}
