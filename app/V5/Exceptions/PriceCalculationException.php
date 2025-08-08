<?php

namespace App\V5\Exceptions;

use App\V5\Exceptions\V5Exception;

/**
 * Price Calculation Exception
 * 
 * Thrown when booking price calculation fails
 */
class PriceCalculationException extends V5Exception
{
    protected function getDefaultErrorCode(): string
    {
        return 'PRICE_CALCULATION_ERROR';
    }

    public function __construct(
        string $message = 'Price calculation failed',
        string $errorCode = '',
        array $context = [],
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $errorCode, 500, $context, $previous);
    }
}