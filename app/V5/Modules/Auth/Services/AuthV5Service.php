<?php

namespace App\V5\Modules\Auth\Services;

use App\V5\Services\BaseService;

class AuthV5Service extends BaseService
{
    public function loginWithSeasonContext(array $credentials): array
    {
        // TODO: implement login logic using season context
        return [];
    }

    public function checkSeasonPermissions(int $userId, int $seasonId): array
    {
        // TODO: return permission list for user in given season
        return [];
    }

    public function assignSeasonRole(int $userId, int $seasonId, string $role): void
    {
        // TODO: persist role assignment
    }
}
