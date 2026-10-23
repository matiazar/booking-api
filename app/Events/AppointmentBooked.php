<?php

namespace App\Events;

use App\Models\Appointment;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched only after the booking transaction commits, so listeners
 * never act on an appointment that was rolled back.
 */
final class AppointmentBooked implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(public Appointment $appointment) {}
}
