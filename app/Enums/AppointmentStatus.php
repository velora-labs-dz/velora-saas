<?php

namespace App\Enums;

/**
 * Deliberately two states, not the richer Membership-style lifecycle.
 * Step 6 (docs/ROADMAP.md) scope is create/edit/cancel/conflict-validation
 * only — no "completed"/"no-show" here. Whether a scheduled appointment
 * actually happened is Attendance's job (Step 7), tracked separately via
 * check-in/check-out, not by mutating the appointment itself.
 */
enum AppointmentStatus: string
{
    case Scheduled = 'scheduled';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Scheduled',
            self::Cancelled => 'Cancelled',
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return $this === self::Scheduled && $target === self::Cancelled;
    }
}
