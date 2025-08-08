<?php

namespace App\V5\Exceptions\Auth;

use App\V5\Exceptions\V5Exception;

class AuthorizationException extends V5Exception
{
    protected function getDefaultErrorCode(): string
    {
        return 'AUTHORIZATION_FAILED';
    }

    public static function missingPermission(string $permission): self
    {
        return new self(
            message: 'exceptions.auth.missing_permission',
            httpStatusCode: 403,
            context: ['required_permission' => $permission]
        );
    }

    public static function invalidRole(string $role): self
    {
        return new self(
            message: 'exceptions.auth.invalid_role',
            httpStatusCode: 400,
            context: ['role' => $role]
        );
    }

    public static function noSeasonAccess(int $seasonId): self
    {
        return new self(
            message: 'exceptions.auth.no_season_access',
            httpStatusCode: 403,
            context: ['season_id' => $seasonId]
        );
    }

    public static function seasonContextRequired(): self
    {
        return new self(
            message: 'exceptions.auth.season_context_required',
            httpStatusCode: 400
        );
    }
}
