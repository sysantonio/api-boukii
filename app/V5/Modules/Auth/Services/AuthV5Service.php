<?php

namespace App\V5\Modules\Auth\Services;

use App\V5\Services\BaseService;
use App\Models\User;
use App\V5\Models\UserSeasonRole;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AuthV5Service extends BaseService
{
    public function loginWithSeasonContext(array $credentials): array
    {
        $email = $credentials['email'] ?? null;
        $password = $credentials['password'] ?? null;
        $seasonId = $credentials['season_id'] ?? null;

        if (!$email || !$password || !$seasonId) {
            return [];
        }

        /** @var User|null $user */
        $user = User::query()
            ->where('email', $email)
            ->where('active', true)
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return [];
        }

        $seasonRole = UserSeasonRole::query()
            ->where('user_id', $user->id)
            ->where('season_id', $seasonId)
            ->first();

        if (!$seasonRole) {
            return [];
        }

        $token = $user->createToken('BoukiiV5', ['season:' . $seasonRole->role])->plainTextToken;

        return [
            'token' => $token,
            'user' => $user,
            'role' => $seasonRole->role,
            'season_id' => (int) $seasonId,
            'permissions' => $this->checkSeasonPermissions($user->id, (int) $seasonId),
        ];
    }

    public function checkSeasonPermissions(int $userId, int $seasonId): array
    {
        $roleName = UserSeasonRole::query()
            ->where('user_id', $userId)
            ->where('season_id', $seasonId)
            ->value('role');

        if (!$roleName) {
            return [];
        }

        $role = Role::where('name', $roleName)->first();
        if (!$role) {
            return [];
        }

        return $role->permissions->pluck('name')->toArray();
    }

    public function assignSeasonRole(int $userId, int $seasonId, string $role): void
    {
        UserSeasonRole::updateOrCreate(
            [
                'user_id' => $userId,
                'season_id' => $seasonId,
            ],
            [
                'role' => $role,
            ]
        );
    }
}
