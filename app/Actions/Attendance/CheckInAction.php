<?php

namespace App\Actions\Attendance;

use App\Models\Attendance;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CheckInAction
{
    /**
     * @param  array<string, mixed>  $attributes  Already-validated attributes
     *                                             from CheckInRequest —
     *                                             client_id and notes.
     *
     * A client can only have one open (checked-in, not checked-out)
     * attendance row at a time — confirmed decision, and the exact rule
     * docs/TESTING.md §8's "duplicate/open session behavior" test exists
     * to prove. Enforced here, not by a DB constraint, since "open" is a
     * computed condition (check_out_at IS NULL for this client in this
     * organization), not a simple column uniqueness rule.
     */
    public function handle(Organization $organization, array $attributes, ?User $recordedBy): Attendance
    {
        $hasOpenSession = Attendance::query()
            ->where('organization_id', $organization->id)
            ->where('client_id', $attributes['client_id'])
            ->whereNull('check_out_at')
            ->exists();

        if ($hasOpenSession) {
            throw ValidationException::withMessages([
                'client_id' => 'This client already has an open check-in.',
            ]);
        }

        $attendance = new Attendance($attributes);
        $attendance->organization_id = $organization->id;
        $attendance->check_in_at = now();
        $attendance->source = 'manual';
        $attendance->recorded_by = $recordedBy?->id;
        $attendance->save();

        return $attendance;
    }
}
