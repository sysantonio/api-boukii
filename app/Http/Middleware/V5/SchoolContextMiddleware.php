<?php

namespace App\Http\Middleware\V5;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\School;
use App\Models\User;

class SchoolContextMiddleware
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

        // Obtener school_id del token o de la request
        $schoolId = $this->getSchoolIdFromContext($request, $user);

        if (!$schoolId) {
            return $this->forbiddenResponse('School context is required');
        }

        // Verificar que la escuela existe
        $school = School::where('id', $schoolId)->first();

        if (!$school) {
            return $this->forbiddenResponse('School not found or inactive');
        }

        // Verificar que el usuario tiene acceso a esta escuela
        if (!$this->userHasAccessToSchool($user, $school)) {
            return $this->forbiddenResponse('Access denied to this school');
        }

        // Agregar contexto de escuela a la request
        $request->merge([
            'context_school_id' => $school->id,
            'context_school' => $school
        ]);

        // Agregar información al header de respuesta para debugging
        $response = $next($request);
        
        if ($response instanceof JsonResponse) {
            $response->header('X-School-Context', $school->id);
            $response->header('X-School-Name', $school->name);
        }

        return $response;
    }

    /**
     * Obtener school_id del contexto (token o request)
     */
    private function getSchoolIdFromContext(Request $request, User $user): ?int
    {
        // 1. Intentar obtener del token actual (context_data)
        $token = $user->currentAccessToken();
        
        // Decode context data if it's a string
        $contextData = $token ? $token->context_data : null;
        if (is_string($contextData)) {
            $contextData = json_decode($contextData, true);
        }
        
        // Debug logging
        \Log::info('SchoolContextMiddleware Debug', [
            'token_id' => $token->id ?? null,
            'token_name' => $token->name ?? null,
            'context_data_raw' => $token ? $token->context_data : null,
            'context_data_decoded' => $contextData,
            'context_data_type' => gettype($contextData),
            'has_context_data' => isset($contextData),
            'has_school_id' => isset($contextData['school_id']) ?? false,
            'has_school_slug' => isset($contextData['school_slug']) ?? false
        ]);
        
        // Check for school_id first
        if ($token && isset($contextData['school_id'])) {
            \Log::info('School ID found in token context', ['school_id' => $contextData['school_id']]);
            return (int) $contextData['school_id'];
        }
        
        // If no school_id, try to get school by slug
        if ($token && isset($contextData['school_slug'])) {
            $school = \App\Models\School::where('slug', $contextData['school_slug'])->first();
            if ($school) {
                \Log::info('School ID found by slug in token context', [
                    'school_slug' => $contextData['school_slug'],
                    'school_id' => $school->id
                ]);
                return (int) $school->id;
            }
        }

        // 2. Intentar obtener de la request (header o query param)
        $schoolId = $request->header('X-School-ID') 
                    ?? $request->query('school_id') 
                    ?? $request->input('school_id');

        if ($schoolId) {
            \Log::info('School ID found in request', ['school_id' => $schoolId]);
            return (int) $schoolId;
        }

        // 3. Si es superadmin, puede que no tenga contexto específico
        if ($user->hasRole('superadmin')) {
            \Log::info('Superadmin user, allowing null school context');
            return null; // Se manejará en el controlador
        }

        \Log::warning('No school context found for user', ['user_id' => $user->id]);
        return null;
    }

    /**
     * Verificar si el usuario tiene acceso a la escuela
     */
    private function userHasAccessToSchool(User $user, School $school): bool
    {
        // Superadmin tiene acceso a todas las escuelas
        if ($user->hasRole('superadmin')) {
            return true;
        }

        // Verificar relación directa user-school
        if ($user->schools()->where('schools.id', $school->id)->exists()) {
            return true;
        }

        // Verificar si es propietario de la escuela
        if ($school->owner_id === $user->id) {
            return true;
        }

        return false;
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