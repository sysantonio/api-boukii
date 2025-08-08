<?php

namespace App\V5\Guards;

use App\V5\Modules\Auth\Services\AuthV5Service;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeasonPermissionGuard
{
    protected AuthV5Service $auth;

    public function __construct(AuthV5Service $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle season permission middleware
     *
     * @param  string|null  $permission - Specific permission to check
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ?string $permission = null)
    {
        try {
            // Check if user is authenticated
            $user = $request->user();
            if (! $user) {
                return $this->unauthorizedResponse('Authentication required');
            }

            // Get season context validated by ContextMiddleware
            $seasonId = (int) $request->get('context_season_id', 0);
            if ($seasonId <= 0) {
                return $this->forbiddenResponse('Season context required');
            }

            // Check if user has any permissions for this season
            $permissions = $this->auth->checkSeasonPermissions($user->id, $seasonId);
            if (empty($permissions)) {
                return $this->forbiddenResponse('User has no permissions for this season');
            }

            // Check specific permission if provided
            if ($permission && ! in_array($permission, $permissions)) {
                return $this->forbiddenResponse("Missing required permission: {$permission}");
            }

            // Add permissions to request for later use
            $request->merge(['user_permissions' => $permissions]);

            return $next($request);
        } catch (\Exception $e) {
            return $this->errorResponse('Permission check failed', $e->getMessage());
        }
    }

    /**
     * Check if user has specific permission in season
     */
    public function hasPermission(Request $request, string $permission): bool
    {
        $user = $request->user();
        if (! $user) {
            return false;
        }

        $seasonId = (int) $request->get('context_season_id', 0);
        if ($seasonId <= 0) {
            return false;
        }

        return $this->auth->hasSeasonPermission($user->id, $seasonId, $permission);
    }

    /**
     * Get all permissions for current user in season
     */
    public function getUserPermissions(Request $request): array
    {
        return $request->get('user_permissions', []);
    }

    private function unauthorizedResponse(string $message): JsonResponse
    {
        return response()->json([
            'error' => $message,
            'code' => 'UNAUTHORIZED',
        ], 401);
    }

    private function forbiddenResponse(string $message): JsonResponse
    {
        return response()->json([
            'error' => $message,
            'code' => 'FORBIDDEN',
        ], 403);
    }

    private function errorResponse(string $message, string $details = ''): JsonResponse
    {
        $response = [
            'error' => $message,
            'code' => 'PERMISSION_ERROR',
        ];

        if ($details) {
            $response['details'] = $details;
        }

        return response()->json($response, 500);
    }
}
