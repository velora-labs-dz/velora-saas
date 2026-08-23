<?php

namespace App\Enums;

enum DurationUnit: string
{
    case Days = 'days';
    case Weeks = 'weeks';
    case Months = 'months';

    public function label(): string
    {
        return match ($this) {
            self::Days => 'Days',
            self::Weeks => 'Weeks',
            self::Months => 'Months',
        };
    }
}
