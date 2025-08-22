<?php

namespace App\Http\Controllers\V5;

use App\Http\Controllers\Controller;
use App\Services\FeatureFlagService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FeatureFlagController extends Controller
{
    public function __construct(
        protected FeatureFlagService $featureFlagService
    ) {}

    /**
     * Obtiene feature flags para una escuela específica
     */
    public function getFlags(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'school_id' => 'required|integer|exists:schools,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid parameters',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $schoolId = $request->integer('school_id');
            
            // Verificar acceso a la escuela
            if (!$this->canAccessSchool($schoolId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied to school'
                ], 403);
            }

            $flags = $this->featureFlagService->getFlagsForSchool($schoolId);
            
            // Log para auditoría
            Log::info('Feature flags requested', [
                'school_id' => $schoolId,
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'data' => $flags,
                'metadata' => [
                    'school_id' => $schoolId,
                    'updated_at' => now()->toISOString(),
                    'version' => config('app.version', '5.0.0'),
                    'cache_ttl' => 300 // 5 minutos
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching feature flags', [
                'school_id' => $request->get('school_id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching feature flags',
                'error' => app()->isProduction() ? 'Internal server error' : $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualiza feature flags para una escuela (solo admins)
     */
    public function updateFlags(Request $request): JsonResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isSchoolAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'school_id' => 'required|integer|exists:schools,id',
            'flags' => 'required|array',
            'flags.useV5Dashboard' => 'boolean',
            'flags.useV5Planificador' => 'boolean',
            'flags.useV5Reservas' => 'boolean',
            'flags.useV5Cursos' => 'boolean',
            'flags.useV5Monitores' => 'boolean',
            'flags.useV5Clientes' => 'boolean',
            'flags.useV5Analytics' => 'boolean',
            'flags.useV5Settings' => 'boolean',
            'flags.enableBetaFeatures' => 'boolean',
            'flags.maintenanceMode' => 'boolean',
            'reason' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid parameters',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $schoolId = $request->integer('school_id');
            $flags = $request->input('flags');
            $reason = $request->input('reason');

            // Verificar acceso a la escuela
            if (!$this->canAccessSchool($schoolId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied to school'
                ], 403);
            }

            $updatedFlags = $this->featureFlagService->updateFlagsForSchool(
                $schoolId, 
                $flags, 
                auth()->id(),
                $reason
            );

            // Log para auditoría
            Log::info('Feature flags updated', [
                'school_id' => $schoolId,
                'user_id' => auth()->id(),
                'flags' => $flags,
                'reason' => $reason,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'data' => $updatedFlags,
                'message' => 'Feature flags updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating feature flags', [
                'school_id' => $request->get('school_id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error updating feature flags',
                'error' => app()->isProduction() ? 'Internal server error' : $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene historial de cambios de feature flags
     */
    public function getHistory(Request $request): JsonResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isSchoolAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'school_id' => 'required|integer|exists:schools,id',
            'limit' => 'integer|min:1|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid parameters',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $schoolId = $request->integer('school_id');
            $limit = $request->integer('limit', 50);

            $history = $this->featureFlagService->getFlagHistory($schoolId, $limit);

            return response()->json([
                'success' => true,
                'data' => $history
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching feature flags history', [
                'school_id' => $request->get('school_id'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching history'
            ], 500);
        }
    }

    /**
     * Limpia cache de feature flags
     */
    public function clearCache(Request $request): JsonResponse
    {
        if (!auth()->user()->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions'
            ], 403);
        }

        try {
            $schoolId = $request->integer('school_id');
            
            if ($schoolId) {
                $this->featureFlagService->clearCacheForSchool($schoolId);
                $message = "Cache cleared for school {$schoolId}";
            } else {
                $this->featureFlagService->clearAllCache();
                $message = "All feature flag cache cleared";
            }

            Log::info('Feature flags cache cleared', [
                'school_id' => $schoolId,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            Log::error('Error clearing feature flags cache', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error clearing cache'
            ], 500);
        }
    }

    /**
     * Verifica si el usuario tiene acceso a la escuela
     */
    private function canAccessSchool(int $schoolId): bool
    {
        $user = auth()->user();
        
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->schools()->where('schools.id', $schoolId)->exists();
    }
}