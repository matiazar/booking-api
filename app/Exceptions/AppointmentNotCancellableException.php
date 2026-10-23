<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

final class AppointmentNotCancellableException extends BookingException
{
    private function __construct(string $message, private readonly string $reason)
    {
        parent::__construct($message);
    }

    public static function alreadyCancelled(): self
    {
        return new self('The appointment is already cancelled.', 'already_cancelled');
    }

    public static function tooLate(int $noticeHours): self
    {
        return new self(
            "Appointments can only be cancelled at least {$noticeHours} hours in advance.",
            'cancellation_window_closed',
        );
    }

    public function status(): int
    {
        return Response::HTTP_UNPROCESSABLE_ENTITY;
    }

    public function errorCode(): string
    {
        return $this->reason;
    }
}
