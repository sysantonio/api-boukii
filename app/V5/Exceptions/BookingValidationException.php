<?php

namespace App\V5\Exceptions;

use App\V5\Exceptions\V5Exception;

/**
 * Booking Validation Exception
 * 
 * Thrown when booking data validation fails
 */
class BookingValidationException extends V5Exception
{
    protected function getDefaultErrorCode(): string
    {
        return 'BOOKING_VALIDATION_ERROR';
    }

    public function __construct(
        string $message = 'Booking validation failed',
        string $errorCode = '',
        array $context = [],
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $errorCode, 422, $context, $previous);
    }
}