<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class AppointmentCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Appointment $appointment) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appointment = $this->appointment->loadMissing('professional');
        $localStart = $appointment->starts_at->setTimezone($appointment->professional->timezone);

        return (new MailMessage)
            ->subject('Your appointment was cancelled')
            ->greeting("Hi {$appointment->customer_name},")
            ->line('Your appointment on '.$localStart->isoFormat('dddd D MMMM YYYY, HH:mm').' has been cancelled.')
            ->line("Booking reference: {$appointment->id}");
    }
}
