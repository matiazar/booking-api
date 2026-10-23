<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';

    /**
     * Statuses that block the professional's agenda.
     *
     * @return list<self>
     */
    public static function blocking(): array
    {
        return [self::Confirmed];
    }
}
