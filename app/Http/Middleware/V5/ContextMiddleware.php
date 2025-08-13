<?php

namespace App\Http\Middleware\V5;

use App\Models\School;
use App\Models\Season;
use App\Models\User;
use App\V5\Models\UserSeasonRole;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContextMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('sanctum')->user();
        if (! $user) {
            return $this->unauthorizedResponse('Authentication required');
        }

        $schoolId = $this->getSchoolId($request, $user);
        if (! $schoolId) {
            return $this->forbiddenResponse('School context is required');
        }

        $school = School::where('id', $schoolId)->first();
        if (! $school) {
            return $this->forbiddenResponse('School not found or inactive');
        }

        if (! $this->userHasAccessToSchool($user, $school)) {
            return $this->forbiddenResponse('Access denied to this school');
        }

        $seasonId = $this->getSeasonId($request, $user);
        if (! $seasonId) {
            return $this->forbiddenResponse('Season context is required');
        }

        $season = Season::where('id', $seasonId)
            ->where('school_id', $school->id)
            ->where('is_active', true)
            ->first();
        if (! $season) {
            return $this->forbiddenResponse('Season not found, inactive, or not associated with school');
        }

        if (! $this->userHasAccessToSeason($user, $season)) {
            return $this->forbiddenResponse('Access denied to this season');
        }

        $request->merge([
            'context_school_id'   => $school->id,
            'context_school'      => $school,
            'context_season_id'   => $season->id,
            'context_season'      => $season,
        ]);

        $response = $next($request);

        if ($response instanceof JsonResponse) {
            $response->header('X-School-Context', $school->id);
            $response->header('X-School-Name', $school->name);
            $response->header('X-Season-Context', $season->id);
            $response->header('X-Season-Name', $season->name);
        }

        return $response;
    }

    private function getSchoolId(Request $request, User $user): ?int
    {
        $token = $user->currentAccessToken();
        $contextData = $token ? $token->context_data : null;
        if (is_string($contextData)) {
            $contextData = json_decode($contextData, true);
        }

        if ($token && isset($contextData['school_id'])) {
            return (int) $contextData['school_id'];
        }

        if ($token && isset($contextData['school_slug'])) {
            $school = School::where('slug', $contextData['school_slug'])->first();
            if ($school) {
                return (int) $school->id;
            }
        }

        $schoolId = $request->header('X-School-ID')
            ?? $request->query('school_id')
            ?? $request->input('school_id');

        return $schoolId ? (int) $schoolId : null;
    }

    private function getSeasonId(Request $request, User $user): ?int
    {
        $token = $user->currentAccessToken();
        if ($token) {
            if ($token->season_id) {
                return (int) $token->season_id;
            }
            $context = $token->context_data;
            if (is_string($context)) {
                $context = json_decode($context, true);
            }
            if (isset($context['season_id'])) {
                return (int) $context['season_id'];
            }
        }

        $seasonId = $request->header('X-Season-ID')
            ?? $request->query('season_id')
            ?? $request->input('season_id');

        return $seasonId ? (int) $seasonId : null;
    }

    private function userHasAccessToSchool(User $user, School $school): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        if ($user->schools()->where('schools.id', $school->id)->exists()) {
            return true;
        }

        if ($school->owner_id === $user->id) {
            return true;
        }

        return false;
    }

    private function userHasAccessToSeason(User $user, Season $season): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return UserSeasonRole::where('user_id', $user->id)
            ->where('season_id', $season->id)
            ->exists();
    }

    private function unauthorizedResponse(string $message): JsonResponse
    {
        return response()->json([
            'success'    => false,
            'message'    => $message,
            'error_code' => 'UNAUTHORIZED',
        ], 401);
    }

    private function forbiddenResponse(string $message): JsonResponse
    {
        return response()->json([
            'success'    => false,
            'message'    => $message,
            'error_code' => 'FORBIDDEN',
        ], 403);
    }
}
