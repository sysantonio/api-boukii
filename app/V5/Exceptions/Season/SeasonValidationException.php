<?php

namespace App\V5\Exceptions\Season;

use App\V5\Exceptions\V5Exception;

class SeasonValidationException extends V5Exception
{
    protected function getDefaultErrorCode(): string
    {
        return 'SEASON_VALIDATION_ERROR';
    }

    public static function invalidDateRange(): self
    {
        return new self(
            message: 'exceptions.season.invalid_date_range',
            httpStatusCode: 422
        );
    }

    public static function overlappingSeasons(array $conflictingSeasons = []): self
    {
        return new self(
            message: 'exceptions.season.overlapping_seasons',
            httpStatusCode: 422,
            context: ['conflicting_seasons' => $conflictingSeasons]
        );
    }

    public static function cannotCloseActiveSeason(): self
    {
        return new self(
            message: 'exceptions.season.cannot_close_active',
            httpStatusCode: 422
        );
    }

    public static function seasonAlreadyClosed(): self
    {
        return new self(
            message: 'exceptions.season.already_closed',
            httpStatusCode: 422
        );
    }
}
