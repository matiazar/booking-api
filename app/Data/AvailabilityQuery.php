<?php

namespace App\Data;

use App\Models\AppointmentType;
use App\Models\Professional;
use Carbon\CarbonImmutable;

final readonly class AvailabilityQuery
{
    public function __construct(
        public Professional $professional,
        public AppointmentType $appointmentType,
        public CarbonImmutable $date,
    ) {}
}
