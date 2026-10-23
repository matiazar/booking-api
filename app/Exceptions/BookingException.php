<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use RuntimeException;

/**
 * Base class for expected business-rule violations.
 *
 * These are not bugs: they are never reported to the admin and they render
 * themselves as a consistent JSON error with a stable machine-readable code.
 */
abstract class BookingException extends RuntimeException
{
    abstract public function status(): int;

    abstract public function errorCode(): string;

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'error' => $this->errorCode(),
        ], $this->status());
    }
}
