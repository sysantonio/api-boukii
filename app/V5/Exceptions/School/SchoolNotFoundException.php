<?php

namespace App\V5\Exceptions\School;

use App\V5\Exceptions\V5Exception;

class SchoolNotFoundException extends V5Exception
{
    protected function getDefaultErrorCode(): string
    {
        return 'SCHOOL_NOT_FOUND';
    }

    public static function withId(int $schoolId): self
    {
        return new self(
            message: 'exceptions.school.not_found',
            httpStatusCode: 404,
            context: ['school_id' => $schoolId]
        );
    }

    public static function noSchoolsForSeason(int $seasonId): self
    {
        return new self(
            message: 'exceptions.school.no_schools_for_season',
            httpStatusCode: 404,
            context: ['season_id' => $seasonId]
        );
    }
}
