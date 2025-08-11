<?php

namespace App\Http\Middleware\V5;

use App\Models\School;
use App\Models\User;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Middleware que requiere solo contexto de escuela (sin temporada)
 * Usado para endpoints que manejan temporadas donde no tiene sentido requerir temporada
 */
class SchoolContextMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Verificar autenticación
        $user = Auth::guard('api_v5')->user();
        if (! $user) {
            return $this->unauthorizedResponse('Authentication required');
        }

        // 2. Obtener school_id
        $schoolId = $this->getSchoolId($request, $user);
        if (! $schoolId) {
            return $this->forbiddenResponse('School context is required');
        }

        // 3. Verificar que la escuela existe y está activa
        $school = School::where('id', $schoolId)->where('active', true)->first();
        if (! $school) {
            return $this->forbiddenResponse('School not found or inactive');
        }

        // 4. Verificar que el usuario tiene acceso a la escuela
        if (! $this->userHasAccessToSchool($user, $school)) {
            return $this->forbiddenResponse('Access denied to this school');
        }

        // 5. Inyectar contexto de escuela en el request
        $request->merge([
            'context_school_id' => $school->id,
            'context_school'    => $school,
        ]);

        // 6. Procesar request
        $response = $next($request);

        // 7. Añadir headers de contexto a la respuesta
        if ($response instanceof JsonResponse) {
            $response->header('X-School-Context', $school->id);
            $response->header('X-School-Name', $school->name);
        }

        return $response;
    }

    private function getSchoolId(Request $request, User $user): ?int
    {
        // Primero, intentar obtener de token context_data
        $token = $user->currentAccessToken();
        if ($token) {
            $contextData = $token->context_data;
            if (is_string($contextData)) {
                $contextData = json_decode($contextData, true);
            }
            
            if (isset($contextData['school_id'])) {
                return (int) $contextData['school_id'];
            }
            
            if (isset($contextData['school_slug'])) {
                $school = School::where('slug', $contextData['school_slug'])->first();
                if ($school) {
                    return (int) $school->id;
                }
            }
        }

        // Fallback: obtener de headers o parámetros
        $schoolId = $request->header('X-School-ID')
            ?? $request->query('school_id')
            ?? $request->input('school_id');

        return $schoolId ? (int) $schoolId : null;
    }

    private function userHasAccessToSchool(User $user, School $school): bool
    {
        // Superadmin tiene acceso a todo
        if ($user->hasRole('superadmin')) {
            return true;
        }

        // Usuario asociado a la escuela
        if ($user->schools()->where('schools.id', $school->id)->exists()) {
            return true;
        }

        // Usuario propietario de la escuela
        if ($school->owner_id === $user->id) {
            return true;
        }

        return false;
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