<?php

namespace App\Listeners;

use App\Events\AppointmentBooked;
use App\Notifications\AppointmentConfirmedNotification;
use Illuminate\Support\Facades\Notification;

final class SendAppointmentConfirmation
{
    public function handle(AppointmentBooked $event): void
    {
        Notification::route('mail', $event->appointment->customer_email)
            ->notify(new AppointmentConfirmedNotification($event->appointment));
    }
}
