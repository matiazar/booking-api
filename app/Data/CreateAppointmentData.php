<?php

namespace App\Data;

use Carbon\CarbonImmutable;

/**
 * Validated input for booking an appointment. Built by the Form Request,
 * consumed by the service - so the service never touches the HTTP layer
 * and can be reused from a controller, a job or a console command.
 */
final readonly class CreateAppointmentData
{
    public function __construct(
        public int $appointmentTypeId,
        public CarbonImmutable $startsAt,
        public string $customerName,
        public string $customerEmail,
    ) {}
}
