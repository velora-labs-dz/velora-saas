<?php

namespace App\Actions\Attendance;

use App\Models\Attendance;
use Illuminate\Validation\ValidationException;

class CheckOutAction
{
    public function handle(Attendance $attendance): Attendance
    {
        if (! $attendance->isOpen()) {
            throw ValidationException::withMessages([
                'check_out_at' => 'This client is already checked out.',
            ]);
        }

        $attendance->check_out_at = now();
        $attendance->save();

        return $attendance;
    }
}
