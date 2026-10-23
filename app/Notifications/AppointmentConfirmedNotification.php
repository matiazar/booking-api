<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class AppointmentConfirmedNotification extends Notification implements ShouldQueue
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
        $appointment = $this->appointment->loadMissing(['professional', 'appointmentType']);
        $localStart = $appointment->starts_at->setTimezone($appointment->professional->timezone);

        return (new MailMessage)
            ->subject('Your appointment is confirmed')
            ->greeting("Hi {$appointment->customer_name},")
            ->line("Your {$appointment->appointmentType->name} with {$appointment->professional->name} is confirmed.")
            ->line('When: '.$localStart->isoFormat('dddd D MMMM YYYY, HH:mm').' ('.$appointment->professional->timezone.')')
            ->line("Booking reference: {$appointment->id}");
    }
}
