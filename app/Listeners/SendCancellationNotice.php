<?php

namespace App\Listeners;

use App\Events\AppointmentCancelled;
use App\Notifications\AppointmentCancelledNotification;
use Illuminate\Support\Facades\Notification;

final class SendCancellationNotice
{
    public function handle(AppointmentCancelled $event): void
    {
        Notification::route('mail', $event->appointment->customer_email)
            ->notify(new AppointmentCancelledNotification($event->appointment));
    }
}
