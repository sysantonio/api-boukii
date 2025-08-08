<?php

namespace App\V5\Modules\Auth\Services;

use App\Models\User;
use App\V5\Models\UserSeasonRole;
use App\V5\Services\BaseService;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AuthV5Service extends BaseService
{
    public function loginWithSeasonContext(array $credentials): array
    {
        $email = $credentials['email'] ?? null;
        $password = $credentials['password'] ?? null;
        $seasonId = $credentials['season_id'] ?? null;

        if (! $email || ! $password || ! $seasonId) {
            throw new \InvalidArgumentException('Email, password, and season_id are required');
        }

        /** @var User|null $user */
        $user = User::query()
            ->where('email', $email)
            ->where('active', true)
            ->first();

        if (! $user) {
            throw new \Illuminate\Auth\AuthenticationException('User not found or inactive');
        }

        if (! Hash::check($password, $user->password)) {
            throw new \Illuminate\Auth\AuthenticationException('Invalid credentials');
        }

        $seasonRole = UserSeasonRole::query()
            ->where('user_id', $user->id)
            ->where('season_id', $seasonId)
            ->first();

        if (! $seasonRole) {
            throw new \Illuminate\Auth\AuthenticationException('User has no role for this season');
        }

        // Revoke previous tokens for this user
        $user->tokens()->delete();

        $token = $user->createToken('BoukiiV5', ['season:'.$seasonRole->role])->plainTextToken;

        return [
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
            ],
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

        if (! $roleName) {
            return [];
        }

        $role = Role::where('name', $roleName)->first();
        if (! $role) {
            return [];
        }

        return $role->permissions->pluck('name')->toArray();
    }

    public function assignSeasonRole(int $userId, int $seasonId, string $role): void
    {
        // Validate role exists
        $validRole = Role::where('name', $role)->exists();
        if (! $validRole) {
            throw new \InvalidArgumentException("Invalid role: {$role}");
        }

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

    /**
     * Get all seasons where user has roles
     */
    public function getUserSeasons(int $userId): array
    {
        return UserSeasonRole::query()
            ->where('user_id', $userId)
            ->with(['season'])
            ->get()
            ->map(function ($userRole) {
                return [
                    'season_id' => $userRole->season_id,
                    'season_name' => $userRole->season->name ?? null,
                    'role' => $userRole->role,
                ];
            })
            ->toArray();
    }

    /**
     * Check if user has permission for specific season
     */
    public function hasSeasonPermission(int $userId, int $seasonId, string $permission): bool
    {
        $permissions = $this->checkSeasonPermissions($userId, $seasonId);

        return in_array($permission, $permissions);
    }

    /**
     * Logout user by revoking all tokens
     */
    public function logout(int $userId): void
    {
        $user = User::find($userId);
        if ($user) {
            $user->tokens()->delete();
        }
    }
}
