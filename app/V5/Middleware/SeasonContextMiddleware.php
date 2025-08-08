<?php

namespace App\V5\Middleware;

use App\V5\Modules\Season\Services\SeasonService;
use Closure;
use Illuminate\Http\Request;

class SeasonContextMiddleware
{
    protected SeasonService $seasons;

    public function __construct(SeasonService $seasons)
    {
        $this->seasons = $seasons;
    }

    public function handle(Request $request, Closure $next)
    {
        try {
            // Get season_id from query parameter, header, or token context
            $seasonId = $request->get('season_id') ?? $request->header('X-Season-Id');
            
            // If not found in request, try to get from authenticated token
            if (!$seasonId && $request->user()) {
                $token = $request->user()->currentAccessToken();
                if ($token && $token->season_id) {
                    $seasonId = $token->season_id;
                }
            }
            
            // If season_id is provided, validate it exists
            if ($seasonId) {
                $seasonId = (int) $seasonId;
                if ($seasonId > 0) {
                    $season = $this->seasons->find($seasonId);
                    if (! $season) {
                        return response()->json([
                            'error' => 'Invalid season_id provided',
                            'code' => 'INVALID_SEASON',
                        ], 400);
                    }
                    // Ensure the season_id is available in the request
                    $request->merge(['season_id' => $seasonId]);
                }
            }
            // Auto-detect season from school_id if not provided
            else {
                $schoolId = $request->get('school_id') ?? $request->header('X-School-Id');
                
                // If not found in request, try to get from authenticated token
                if (!$schoolId && $request->user()) {
                    $token = $request->user()->currentAccessToken();
                    if ($token && $token->school_id) {
                        $schoolId = $token->school_id;
                    }
                }
                if ($schoolId) {
                    $schoolId = (int) $schoolId;
                    if ($schoolId > 0) {
                        $season = $this->seasons->getCurrentSeason($schoolId);
                        if ($season) {
                            $request->merge(['season_id' => $season->id]);
                        } else {
                            return response()->json([
                                'error' => 'No active season found for school',
                                'code' => 'NO_ACTIVE_SEASON',
                            ], 400);
                        }
                    }
                }
            }
            
            // For routes that require season context, ensure it's present
            if ($this->requiresSeasonContext($request)) {
                return response()->json([
                    'error' => 'Season context required (season_id or school_id)',
                    'code' => 'SEASON_CONTEXT_REQUIRED',
                ], 400);
            }

            return $next($request);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Season context validation failed',
                'code' => 'SEASON_CONTEXT_ERROR',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if the current route requires season context
     */
    private function requiresSeasonContext(Request $request): bool
    {
        $route = $request->route();
        if (! $route) {
            return false;
        }

        $routeName = $route->getName();
        $uri = $request->getRequestUri();

        // Routes that always require season context
        $requiresContext = [
            '/api/v5/schools',
            '/api/v5/auth/permissions',
            '/api/v5/auth/season/switch',
        ];

        foreach ($requiresContext as $pattern) {
            if (str_starts_with($uri, $pattern)) {
                return true;
            }
        }

        return false;
    }
}
