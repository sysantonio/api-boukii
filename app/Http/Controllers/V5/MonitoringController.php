<?php

namespace App\Http\Controllers\V5;

use App\Http\Controllers\Controller;
use App\Services\V5MonitoringService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class MonitoringController extends Controller
{
    private V5MonitoringService $monitoringService;

    public function __construct(V5MonitoringService $monitoringService)
    {
        $this->monitoringService = $monitoringService;
        
        // Middleware para proteger endpoints de monitoreo
        $this->middleware('auth:sanctum');
        $this->middleware('can:view-monitoring')->except(['healthCheck']);
    }

    /**
     * Obtiene estadísticas generales del sistema
     */
    public function getSystemStats(): JsonResponse
    {
        try {
            $stats = Cache::remember('v5_system_stats', 60, function () {
                return $this->monitoringService->getSystemStats();
            });

            return response()->json($stats);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load system stats',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene comparativa de performance entre V4 y V5
     */
    public function getPerformanceComparison(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'school_id' => 'sometimes|integer|exists:schools,id',
            'module' => 'sometimes|string|in:dashboard,planificador,reservas,cursos,clientes,analytics',
            'hours' => 'sometimes|integer|min:1|max:168' // Máximo 1 semana
        ]);

        try {
            $schoolId = $validated['school_id'] ?? null;
            $module = $validated['module'] ?? 'clientes'; // Default a clientes (ya implementado)
            $hours = $validated['hours'] ?? 24;

            if ($schoolId) {
                $comparison = $this->monitoringService->getPerformanceComparison($schoolId, $module, $hours);
            } else {
                // Agregado para todos los colegios
                $comparison = $this->getAggregatedPerformanceComparison($module, $hours);
            }

            return response()->json($comparison);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load performance comparison',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registra métrica de performance
     */
    public function recordPerformance(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'school_id' => 'nullable|integer|exists:schools,id',
            'version' => 'required|string|in:v4,v5',
            'module' => 'required|string|max:50',
            'action' => 'required|string|max:50',
            'response_time_ms' => 'required|integer|min:0',
            'metadata' => 'sometimes|array'
        ]);

        try {
            $this->monitoringService->recordPerformance(
                $validated['school_id'],
                $validated['version'],
                $validated['module'],
                $validated['action'],
                $validated['response_time_ms'],
                $validated['metadata'] ?? []
            );

            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to record performance metric',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registra error de migración
     */
    public function recordMigrationError(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'school_id' => 'required|integer|exists:schools,id',
            'migration_type' => 'required|string|max:50',
            'error_message' => 'required|string',
            'context' => 'sometimes|array'
        ]);

        try {
            $this->monitoringService->recordMigrationError(
                $validated['school_id'],
                $validated['migration_type'],
                $validated['error_message'],
                $validated['context'] ?? []
            );

            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to record migration error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Health check público para monitoring externo
     */
    public function healthCheck(): JsonResponse
    {
        try {
            $stats = $this->monitoringService->getSystemStats();
            
            return response()->json([
                'status' => $stats['system_health']['status'],
                'timestamp' => now()->toISOString(),
                'checks' => $stats['system_health']['checks']
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'unhealthy',
                'timestamp' => now()->toISOString(),
                'error' => $e->getMessage()
            ], 503);
        }
    }

    /**
     * Obtiene alertas recientes con filtros
     */
    public function getRecentAlerts(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'severity' => 'sometimes|string|in:critical,warning,info',
            'limit' => 'sometimes|integer|min:1|max:100',
            'school_id' => 'sometimes|integer|exists:schools,id'
        ]);

        try {
            $alerts = Cache::get('v5_monitoring:alerts', []);
            
            // Aplicar filtros
            if (isset($validated['severity'])) {
                $alerts = array_filter($alerts, fn($alert) => $alert['severity'] === $validated['severity']);
            }
            
            if (isset($validated['school_id'])) {
                $alerts = array_filter($alerts, fn($alert) => ($alert['school_id'] ?? null) === $validated['school_id']);
            }
            
            // Limitar resultados
            $limit = $validated['limit'] ?? 50;
            $alerts = array_slice($alerts, 0, $limit);
            
            return response()->json($alerts);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load alerts',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene métricas de un colegio específico
     */
    public function getSchoolMetrics(Request $request, int $schoolId): JsonResponse
    {
        $validated = $request->validate([
            'module' => 'sometimes|string',
            'hours' => 'sometimes|integer|min:1|max:168'
        ]);

        try {
            $module = $validated['module'] ?? 'clientes';
            $hours = $validated['hours'] ?? 24;
            
            $comparison = $this->monitoringService->getPerformanceComparison($schoolId, $module, $hours);
            
            return response()->json([
                'school_id' => $schoolId,
                'performance' => $comparison,
                'period' => [
                    'hours' => $hours,
                    'module' => $module
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load school metrics',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Limpia cache de métricas (para debugging)
     */
    public function clearMetricsCache(): JsonResponse
    {
        if (!app()->isProduction()) {
            try {
                Cache::forget('v5_system_stats');
                Cache::forget('v5_monitoring:alerts');
                
                // Limpiar cache de stats por versión y módulo
                $patterns = ['v5_monitoring:stats:*'];
                foreach ($patterns as $pattern) {
                    $keys = Cache::getRedis()->keys($pattern);
                    if (!empty($keys)) {
                        Cache::getRedis()->del($keys);
                    }
                }
                
                return response()->json(['success' => true, 'message' => 'Cache cleared']);
                
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Failed to clear cache',
                    'message' => $e->getMessage()
                ], 500);
            }
        }
        
        return response()->json(['error' => 'Not available in production'], 403);
    }

    /**
     * Obtiene estadísticas agregadas de performance
     */
    private function getAggregatedPerformanceComparison(string $module, int $hours): array
    {
        $since = now()->subHours($hours);
        
        // Obtener métricas V4
        $v4Metrics = \App\Models\PerformanceMetric::where('version', 'v4')
            ->where('module', $module)
            ->where('measured_at', '>=', $since)
            ->get();
            
        // Obtener métricas V5
        $v5Metrics = \App\Models\PerformanceMetric::where('version', 'v5')
            ->where('module', $module)
            ->where('measured_at', '>=', $since)
            ->get();

        $v4Stats = $this->calculateAggregatedStats($v4Metrics);
        $v5Stats = $this->calculateAggregatedStats($v5Metrics);

        return [
            'v4' => $v4Stats,
            'v5' => $v5Stats,
            'improvement' => [
                'response_time' => $this->calculateImprovement(
                    $v4Stats['avg_response_time'],
                    $v5Stats['avg_response_time']
                ),
                'error_rate' => $this->calculateImprovement(
                    $v4Stats['error_rate'],
                    $v5Stats['error_rate']
                )
            ],
            'period' => [
                'hours' => $hours,
                'from' => $since->toISOString(),
                'to' => now()->toISOString(),
                'module' => $module
            ]
        ];
    }

    /**
     * Calcula estadísticas agregadas de métricas
     */
    private function calculateAggregatedStats($metrics): array
    {
        if ($metrics->isEmpty()) {
            return [
                'request_count' => 0,
                'avg_response_time' => 0,
                'min_response_time' => 0,
                'max_response_time' => 0,
                'p95_response_time' => 0,
                'error_rate' => 0,
                'errors_count' => 0
            ];
        }

        $responseTimes = $metrics->pluck('response_time_ms')->sort()->values();
        $errorCount = $metrics->where('response_time_ms', '>', 5000)->count(); // >5s considerado error

        return [
            'request_count' => $metrics->count(),
            'avg_response_time' => $responseTimes->average(),
            'min_response_time' => $responseTimes->min(),
            'max_response_time' => $responseTimes->max(),
            'p95_response_time' => $this->calculatePercentile($responseTimes, 95),
            'error_rate' => $metrics->count() > 0 ? ($errorCount / $metrics->count()) * 100 : 0,
            'errors_count' => $errorCount
        ];
    }

    /**
     * Calcula percentil de una colección
     */
    private function calculatePercentile($values, int $percentile): float
    {
        if ($values->isEmpty()) return 0;

        $index = ($percentile / 100) * ($values->count() - 1);
        $lower = floor($index);
        $upper = ceil($index);

        if ($lower == $upper) {
            return $values[$lower];
        }

        return $values[$lower] * ($upper - $index) + $values[$upper] * ($index - $lower);
    }

    /**
     * Calcula mejora porcentual
     */
    private function calculateImprovement(float $oldValue, float $newValue): array
    {
        if ($oldValue == 0) {
            return ['percentage' => 0, 'direction' => 'neutral'];
        }

        $percentage = (($oldValue - $newValue) / $oldValue) * 100;
        $direction = $percentage > 0 ? 'improved' : ($percentage < 0 ? 'degraded' : 'neutral');

        return [
            'percentage' => abs($percentage),
            'direction' => $direction
        ];
    }
}