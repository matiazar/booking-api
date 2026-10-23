<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

final class SlotUnavailableException extends BookingException
{
    public function __construct()
    {
        parent::__construct('The selected time is not available.');
    }

    public function status(): int
    {
        return Response::HTTP_CONFLICT;
    }

    public function errorCode(): string
    {
        return 'slot_unavailable';
    }
}
