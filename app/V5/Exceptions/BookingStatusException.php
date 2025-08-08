<?php

namespace App\V5\Exceptions;

use App\V5\Exceptions\V5Exception;

/**
 * Booking Status Exception
 * 
 * Thrown when booking status operations are invalid
 */
class BookingStatusException extends V5Exception
{
    protected function getDefaultErrorCode(): string
    {
        return 'BOOKING_STATUS_ERROR';
    }

    public function __construct(
        string $message = 'Invalid booking status operation',
        string $errorCode = '',
        array $context = [],
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $errorCode, 400, $context, $previous);
    }
}