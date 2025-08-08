<?php

namespace App\V5\Exceptions;

use App\V5\Exceptions\V5Exception;

/**
 * Booking Not Found Exception
 * 
 * Thrown when a requested booking cannot be found
 */
class BookingNotFoundException extends V5Exception
{
    protected function getDefaultErrorCode(): string
    {
        return 'BOOKING_NOT_FOUND';
    }

    public function __construct(
        string $message = 'Booking not found',
        string $errorCode = '',
        array $context = [],
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $errorCode, 404, $context, $previous);
    }
}