<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PerformanceMetric extends Model
{
    protected $fillable = [
        'school_id',
        'version',
        'module',
        'action',
        'response_time_ms',
        'metadata',
        'measured_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'measured_at' => 'datetime',
        'response_time_ms' => 'integer'
    ];

    protected $dates = [
        'measured_at'
    ];

    /**
     * Relationship with School
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Scope para filtrar por versión
     */
    public function scopeVersion($query, string $version)
    {
        return $query->where('version', $version);
    }

    /**
     * Scope para filtrar por módulo
     */
    public function scopeModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Scope para filtrar por rango de fechas
     */
    public function scopeDateRange($query, Carbon $from, Carbon $to)
    {
        return $query->whereBetween('measured_at', [$from, $to]);
    }

    /**
     * Scope para métricas lentas (por encima de un threshold)
     */
    public function scopeSlow($query, int $thresholdMs = 5000)
    {
        return $query->where('response_time_ms', '>', $thresholdMs);
    }

    /**
     * Obtiene el promedio de tiempo de respuesta para un período
     */
    public static function getAverageResponseTime(
        string $version, 
        string $module, 
        Carbon $from, 
        Carbon $to, 
        ?int $schoolId = null
    ): float {
        $query = static::where('version', $version)
            ->where('module', $module)
            ->whereBetween('measured_at', [$from, $to]);
            
        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }
        
        return $query->avg('response_time_ms') ?? 0.0;
    }

    /**
     * Obtiene métricas de percentiles
     */
    public static function getPercentileMetrics(
        string $version, 
        string $module, 
        Carbon $from, 
        Carbon $to, 
        ?int $schoolId = null
    ): array {
        $query = static::where('version', $version)
            ->where('module', $module)
            ->whereBetween('measured_at', [$from, $to]);
            
        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }
        
        $metrics = $query->orderBy('response_time_ms')->pluck('response_time_ms');
        
        if ($metrics->isEmpty()) {
            return [
                'p50' => 0,
                'p95' => 0,
                'p99' => 0,
                'min' => 0,
                'max' => 0
            ];
        }
        
        $count = $metrics->count();
        
        return [
            'p50' => $metrics[(int) ($count * 0.5)] ?? 0,
            'p95' => $metrics[(int) ($count * 0.95)] ?? 0,
            'p99' => $metrics[(int) ($count * 0.99)] ?? 0,
            'min' => $metrics->min(),
            'max' => $metrics->max()
        ];
    }

    /**
     * Obtiene conteo de errores (métricas lentas)
     */
    public static function getErrorCount(
        string $version, 
        string $module, 
        Carbon $from, 
        Carbon $to, 
        int $thresholdMs = 5000,
        ?int $schoolId = null
    ): int {
        $query = static::where('version', $version)
            ->where('module', $module)
            ->whereBetween('measured_at', [$from, $to])
            ->where('response_time_ms', '>', $thresholdMs);
            
        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }
        
        return $query->count();
    }

    /**
     * Limpia métricas antiguas
     */
    public static function cleanup(int $daysToKeep = 30): int
    {
        $cutoffDate = Carbon::now()->subDays($daysToKeep);
        
        return static::where('measured_at', '<', $cutoffDate)->delete();
    }

    /**
     * Obtiene estadísticas de uso por escuela
     */
    public static function getSchoolUsageStats(Carbon $from, Carbon $to): array
    {
        return static::whereBetween('measured_at', [$from, $to])
            ->selectRaw('
                school_id,
                version,
                module,
                COUNT(*) as request_count,
                AVG(response_time_ms) as avg_response_time,
                MIN(response_time_ms) as min_response_time,
                MAX(response_time_ms) as max_response_time
            ')
            ->groupBy(['school_id', 'version', 'module'])
            ->orderBy('school_id')
            ->get()
            ->groupBy('school_id')
            ->toArray();
    }

    /**
     * Auto-set measured_at if not provided
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($metric) {
            if (!$metric->measured_at) {
                $metric->measured_at = Carbon::now();
            }
        });
    }
}