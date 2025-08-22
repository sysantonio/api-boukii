<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class V5MigrationLog extends Model
{
    protected $fillable = [
        'school_id',
        'migration_type',
        'status',
        'metadata',
        'error_message',
        'started_at',
        'completed_at',
        'progress_percentage'
    ];

    protected $casts = [
        'metadata' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'progress_percentage' => 'integer'
    ];

    protected $dates = [
        'started_at',
        'completed_at'
    ];

    // Status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    // Migration type constants
    public const TYPE_DATA = 'data';
    public const TYPE_FEATURE_FLAG = 'feature_flag';
    public const TYPE_ROLLBACK = 'rollback';

    /**
     * Relationship with School
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Scope para filtrar por estado
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope para filtrar por tipo de migración
     */
    public function scopeMigrationType($query, string $type)
    {
        return $query->where('migration_type', $type);
    }

    /**
     * Scope para migraciones recientes
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', Carbon::now()->subHours($hours));
    }

    /**
     * Scope para migraciones fallidas
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope para migraciones completadas
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope para migraciones en progreso
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_RUNNING);
    }

    /**
     * Obtiene la duración de la migración en segundos
     */
    public function getDurationAttribute(): ?int
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        return $this->started_at->diffInSeconds($this->completed_at);
    }

    /**
     * Obtiene la duración formateada
     */
    public function getFormattedDurationAttribute(): ?string
    {
        $duration = $this->duration;
        
        if (!$duration) {
            return null;
        }

        if ($duration < 60) {
            return $duration . 's';
        } elseif ($duration < 3600) {
            return round($duration / 60, 1) . 'm';
        } else {
            return round($duration / 3600, 1) . 'h';
        }
    }

    /**
     * Verifica si la migración está en progreso
     */
    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_RUNNING;
    }

    /**
     * Verifica si la migración ha fallado
     */
    public function hasFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Verifica si la migración se ha completado
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Marca la migración como iniciada
     */
    public function markAsStarted(): void
    {
        $this->update([
            'status' => self::STATUS_RUNNING,
            'started_at' => Carbon::now(),
            'progress_percentage' => 0
        ]);
    }

    /**
     * Marca la migración como completada
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => Carbon::now(),
            'progress_percentage' => 100
        ]);
    }

    /**
     * Marca la migración como fallida
     */
    public function markAsFailed(string $errorMessage, array $context = []): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'completed_at' => Carbon::now(),
            'error_message' => $errorMessage,
            'metadata' => array_merge($this->metadata ?? [], [
                'error_context' => $context,
                'failed_at' => Carbon::now()->toISOString()
            ])
        ]);
    }

    /**
     * Actualiza el progreso de la migración
     */
    public function updateProgress(int $percentage, array $metadata = []): void
    {
        $this->update([
            'progress_percentage' => max(0, min(100, $percentage)),
            'metadata' => array_merge($this->metadata ?? [], $metadata)
        ]);
    }

    /**
     * Obtiene estadísticas de migraciones para un período
     */
    public static function getStatistics(Carbon $from, Carbon $to): array
    {
        $logs = static::whereBetween('created_at', [$from, $to])->get();

        return [
            'total' => $logs->count(),
            'completed' => $logs->where('status', self::STATUS_COMPLETED)->count(),
            'failed' => $logs->where('status', self::STATUS_FAILED)->count(),
            'in_progress' => $logs->where('status', self::STATUS_RUNNING)->count(),
            'pending' => $logs->where('status', self::STATUS_PENDING)->count(),
            'by_type' => $logs->groupBy('migration_type')->map->count(),
            'by_school' => $logs->groupBy('school_id')->map->count(),
            'avg_duration' => $logs->where('status', self::STATUS_COMPLETED)
                ->map(fn($log) => $log->duration)
                ->filter()
                ->avg(),
        ];
    }

    /**
     * Obtiene migraciones problemáticas (fallidas múltiples veces)
     */
    public static function getProblematicMigrations(int $failureThreshold = 3): array
    {
        return static::selectRaw('
                school_id, 
                migration_type, 
                COUNT(*) as failure_count,
                MAX(created_at) as last_failure
            ')
            ->where('status', self::STATUS_FAILED)
            ->groupBy(['school_id', 'migration_type'])
            ->havingRaw('COUNT(*) >= ?', [$failureThreshold])
            ->orderBy('failure_count', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Limpia logs antiguos
     */
    public static function cleanup(int $daysToKeep = 90): int
    {
        $cutoffDate = Carbon::now()->subDays($daysToKeep);
        
        // Mantener logs fallidos por más tiempo (6 meses)
        $failureCutoffDate = Carbon::now()->subMonths(6);
        
        $deletedCount = 0;
        
        // Eliminar logs completados antiguos
        $deletedCount += static::where('status', self::STATUS_COMPLETED)
            ->where('created_at', '<', $cutoffDate)
            ->delete();
            
        // Eliminar logs fallidos muy antiguos
        $deletedCount += static::where('status', self::STATUS_FAILED)
            ->where('created_at', '<', $failureCutoffDate)
            ->delete();
            
        return $deletedCount;
    }

    /**
     * Auto-set started_at cuando se cambia a running
     */
    protected static function boot()
    {
        parent::boot();
        
        static::updating(function ($log) {
            if ($log->isDirty('status') && $log->status === self::STATUS_RUNNING && !$log->started_at) {
                $log->started_at = Carbon::now();
            }
            
            if ($log->isDirty('status') && in_array($log->status, [self::STATUS_COMPLETED, self::STATUS_FAILED]) && !$log->completed_at) {
                $log->completed_at = Carbon::now();
            }
        });
    }
}