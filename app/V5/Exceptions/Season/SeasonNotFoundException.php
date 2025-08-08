<?php

namespace App\V5\Exceptions\Season;

use App\V5\Exceptions\V5Exception;

class SeasonNotFoundException extends V5Exception
{
    protected function getDefaultErrorCode(): string
    {
        return 'SEASON_NOT_FOUND';
    }

    public static function withId(int $seasonId): self
    {
        return new self(
            message: 'exceptions.season.not_found',
            httpStatusCode: 404,
            context: ['season_id' => $seasonId]
        );
    }

    public static function noActiveSeasonForSchool(int $schoolId): self
    {
        return new self(
            message: 'exceptions.season.no_active_season',
            httpStatusCode: 404,
            context: ['school_id' => $schoolId]
        );
    }
}
