<?php

namespace App\Http\Middleware\V5;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\Season;
use App\Models\User;

class SeasonContextMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('api_v5')->user();

        if (!$user) {
            return $this->unauthorizedResponse('Authentication required');
        }

        // Obtener season_id del token o de la request
        $seasonId = $this->getSeasonIdFromContext($request, $user);
        $schoolId = $request->get('context_school_id');

        if (!$seasonId) {
            return $this->forbiddenResponse('Season context is required');
        }

        if (!$schoolId) {
            return $this->forbiddenResponse('School context is required');
        }

        // Verificar que la temporada existe, está activa y pertenece a la escuela
        $season = Season::where('id', $seasonId)
            ->where('school_id', $schoolId)
            ->where('is_active', true)
            ->first();

        if (!$season) {
            return $this->forbiddenResponse('Season not found, inactive, or not associated with school');
        }

        // Verificar permisos específicos de temporada si es necesario
        if (!$this->userHasAccessToSeason($user, $season)) {
            return $this->forbiddenResponse('Access denied to this season');
        }

        // Agregar contexto de temporada a la request
        $request->merge([
            'context_season_id' => $season->id,
            'context_season' => $season
        ]);

        // Agregar información al header de respuesta para debugging
        $response = $next($request);
        
        if ($response instanceof JsonResponse) {
            $response->header('X-Season-Context', $season->id);
            $response->header('X-Season-Name', $season->name);
        }

        return $response;
    }

    /**
     * Obtener season_id del contexto (token o request)
     */
    private function getSeasonIdFromContext(Request $request, User $user): ?int
    {
        // 1. Intentar obtener del token actual
        $token = $user->currentAccessToken();
        if ($token && $token->season_id) {
            return $token->season_id;
        }

        // 2. Intentar obtener de la request (header o query param)
        $seasonId = $request->header('X-Season-ID') 
                    ?? $request->query('season_id') 
                    ?? $request->input('season_id');

        if ($seasonId) {
            return (int) $seasonId;
        }

        return null;
    }

    /**
     * Verificar si el usuario tiene acceso a la temporada
     */
    private function userHasAccessToSeason(User $user, Season $season): bool
    {
        // Superadmin tiene acceso a todas las temporadas
        if ($user->hasRole('superadmin')) {
            return true;
        }

        // School admin tiene acceso a todas las temporadas de su escuela
        if ($user->hasRole('school_admin')) {
            return $user->schools()->where('schools.id', $season->school_id)->exists();
        }

        // Otros roles también tienen acceso si tienen acceso a la escuela
        return $user->schools()->where('schools.id', $season->school_id)->exists();
    }

    /**
     * Respuesta no autorizada
     */
    private function unauthorizedResponse(string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error_code' => 'UNAUTHORIZED'
        ], 401);
    }

    /**
     * Respuesta prohibida
     */
    private function forbiddenResponse(string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error_code' => 'FORBIDDEN'
        ], 403);
    }
}