<?php

namespace App\V5\Exceptions\Auth;

use App\V5\Exceptions\V5Exception;

class AuthenticationException extends V5Exception
{
    protected function getDefaultErrorCode(): string
    {
        return 'AUTHENTICATION_FAILED';
    }

    public static function invalidCredentials(): self
    {
        return new self(
            message: 'exceptions.auth.invalid_credentials',
            httpStatusCode: 401
        );
    }

    public static function userNotFound(): self
    {
        return new self(
            message: 'exceptions.auth.user_not_found',
            httpStatusCode: 401
        );
    }

    public static function userInactive(): self
    {
        return new self(
            message: 'exceptions.auth.user_inactive',
            httpStatusCode: 401
        );
    }

    public static function noSeasonRole(int $seasonId): self
    {
        return new self(
            message: 'exceptions.auth.no_season_role',
            httpStatusCode: 403,
            context: ['season_id' => $seasonId]
        );
    }

    public static function missingCredentials(): self
    {
        return new self(
            message: 'exceptions.auth.missing_credentials',
            httpStatusCode: 400
        );
    }
}
