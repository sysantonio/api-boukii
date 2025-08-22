<?php

namespace App\Services;

use App\Models\School;
use App\Models\FeatureFlagHistory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class FeatureFlagService
{
    private const CACHE_PREFIX = 'feature_flags';
    private const CACHE_TTL = 300; // 5 minutos
    
    // Feature flags por defecto (conservador)
    private const DEFAULT_FLAGS = [
        'useV5Dashboard' => false,
        'useV5Planificador' => false,
        'useV5Reservas' => false,
        'useV5Cursos' => false,
        'useV5Monitores' => false,
        'useV5Clientes' => true, // Ya implementado completamente
        'useV5Analytics' => false,
        'useV5Settings' => false,
        'useV5Calendar' => false,
        'useV5Payments' => false,
        'useV5Communications' => false,
        'useV5Renting' => false,
        'useV5Chat' => false,
        'enableBetaFeatures' => false,
        'enableDebugMode' => false,
        'maintenanceMode' => false,
        'enableMicrogate' => false,
        'enableWhatsApp' => false,
        'enableDeepL' => false,
        'enableAccuWeather' => true
    ];

    /**
     * Obtiene feature flags para una escuela específica
     */
    public function getFlagsForSchool(int $schoolId): array
    {
        $cacheKey = $this->getCacheKey($schoolId);
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($schoolId) {
            $school = School::find($schoolId);
            
            if (!$school) {
                return self::DEFAULT_FLAGS;
            }

            // Combinar flags por defecto con los de la escuela
            $schoolFlags = $school->feature_flags ?? [];
            $flags = array_merge(self::DEFAULT_FLAGS, $schoolFlags);

            // Aplicar reglas de negocio
            $flags = $this->applyBusinessRules($flags, $school);

            return $flags;
        });
    }

    /**
     * Actualiza feature flags para una escuela
     */
    public function updateFlagsForSchool(
        int $schoolId, 
        array $flags, 
        int $userId, 
        string $reason
    ): array {
        DB::beginTransaction();
        
        try {
            $school = School::findOrFail($schoolId);
            $oldFlags = $school->feature_flags ?? [];
            
            // Validar y sanitizar flags
            $validatedFlags = $this->validateFlags($flags);
            
            // Aplicar reglas de negocio
            $finalFlags = $this->applyBusinessRules($validatedFlags, $school);
            
            // Actualizar en base de datos
            $school->update([
                'feature_flags' => $finalFlags,
                'feature_flags_updated_at' => now()
            ]);
            
            // Guardar en historial
            $this->saveToHistory($schoolId, $oldFlags, $finalFlags, $userId, $reason);
            
            // Limpiar cache
            $this->clearCacheForSchool($schoolId);
            
            // Notificar cambios (webhook, eventos, etc.)
            $this->notifyFlagChanges($schoolId, $oldFlags, $finalFlags);
            
            DB::commit();
            
            return $finalFlags;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Aplica reglas de negocio a los feature flags
     */
    private function applyBusinessRules(array $flags, School $school): array
    {
        // Regla: Si está en mantenimiento, deshabilitar todo excepto auth
        if ($flags['maintenanceMode'] ?? false) {
            foreach (self::DEFAULT_FLAGS as $key => $value) {
                if (str_starts_with($key, 'useV5')) {
                    $flags[$key] = false;
                }
            }
        }

        // Regla: Clientes V5 siempre habilitado (ya implementado)
        $flags['useV5Clientes'] = true;

        // Regla: Beta features solo para escuelas de test
        if (!$school->is_test_school && ($flags['enableBetaFeatures'] ?? false)) {
            $flags['enableBetaFeatures'] = false;
        }

        // Regla: Debug mode solo en desarrollo
        if (app()->isProduction()) {
            $flags['enableDebugMode'] = false;
        }

        // Regla: Microgate solo si tienen la integración configurada
        if (!$school->has_microgate_integration) {
            $flags['enableMicrogate'] = false;
        }

        // Regla: WhatsApp solo si tienen credenciales configuradas
        if (empty($school->whatsapp_config)) {
            $flags['enableWhatsApp'] = false;
        }

        return $flags;
    }

    /**
     * Valida y sanitiza los feature flags
     */
    private function validateFlags(array $flags): array
    {
        $validated = [];
        
        foreach (self::DEFAULT_FLAGS as $key => $defaultValue) {
            if (isset($flags[$key])) {
                // Convertir a boolean
                $validated[$key] = (bool) $flags[$key];
            } else {
                $validated[$key] = $defaultValue;
            }
        }
        
        return $validated;
    }

    /**
     * Guarda cambios en el historial
     */
    private function saveToHistory(
        int $schoolId, 
        array $oldFlags, 
        array $newFlags, 
        int $userId, 
        string $reason
    ): void {
        $changes = [];
        
        foreach ($newFlags as $key => $newValue) {
            $oldValue = $oldFlags[$key] ?? null;
            if ($oldValue !== $newValue) {
                $changes[$key] = [
                    'from' => $oldValue,
                    'to' => $newValue
                ];
            }
        }
        
        if (!empty($changes)) {
            FeatureFlagHistory::create([
                'school_id' => $schoolId,
                'user_id' => $userId,
                'changes' => $changes,
                'reason' => $reason,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
        }
    }

    /**
     * Obtiene historial de cambios
     */
    public function getFlagHistory(int $schoolId, int $limit = 50): array
    {
        return FeatureFlagHistory::where('school_id', $schoolId)
            ->with('user:id,name,email')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($record) {
                return [
                    'id' => $record->id,
                    'user' => [
                        'id' => $record->user->id,
                        'name' => $record->user->name,
                        'email' => $record->user->email
                    ],
                    'changes' => $record->changes,
                    'reason' => $record->reason,
                    'created_at' => $record->created_at->toISOString(),
                    'ip_address' => $record->ip_address
                ];
            })
            ->toArray();
    }

    /**
     * Notifica cambios de feature flags
     */
    private function notifyFlagChanges(int $schoolId, array $oldFlags, array $newFlags): void
    {
        // Enviar evento para notificaciones en tiempo real
        event('feature-flags.updated', [
            'school_id' => $schoolId,
            'old_flags' => $oldFlags,
            'new_flags' => $newFlags,
            'timestamp' => now()
        ]);

        // Webhook para integraciones externas (opcional)
        $this->sendWebhookNotification($schoolId, $oldFlags, $newFlags);
    }

    /**
     * Envía webhook de notificación (opcional)
     */
    private function sendWebhookNotification(int $schoolId, array $oldFlags, array $newFlags): void
    {
        $school = School::find($schoolId);
        
        if (!$school || empty($school->webhook_url)) {
            return;
        }

        try {
            $payload = [
                'event' => 'feature_flags_updated',
                'school_id' => $schoolId,
                'school_name' => $school->name,
                'changes' => array_diff_assoc($newFlags, $oldFlags),
                'timestamp' => now()->toISOString()
            ];

            // Enviar webhook de forma asíncrona
            dispatch(function () use ($school, $payload) {
                \Http::timeout(10)->post($school->webhook_url, $payload);
            })->onQueue('webhooks');
            
        } catch (\Exception $e) {
            \Log::warning('Failed to send feature flag webhook', [
                'school_id' => $schoolId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Obtiene el cache key para una escuela
     */
    private function getCacheKey(int $schoolId): string
    {
        return self::CACHE_PREFIX . ':school:' . $schoolId;
    }

    /**
     * Limpia cache para una escuela específica
     */
    public function clearCacheForSchool(int $schoolId): void
    {
        $cacheKey = $this->getCacheKey($schoolId);
        Cache::forget($cacheKey);
        
        // También limpiar de Redis si se está usando
        if (Cache::getStore() instanceof \Illuminate\Cache\RedisStore) {
            Redis::del($cacheKey);
        }
    }

    /**
     * Limpia todo el cache de feature flags
     */
    public function clearAllCache(): void
    {
        // Si usamos Redis, podemos usar pattern matching
        if (Cache::getStore() instanceof \Illuminate\Cache\RedisStore) {
            $keys = Redis::keys(self::CACHE_PREFIX . ':*');
            if (!empty($keys)) {
                Redis::del($keys);
            }
        }
        
        // Fallback: limpiar cache por escuela conocida
        $schoolIds = School::pluck('id');
        foreach ($schoolIds as $schoolId) {
            $this->clearCacheForSchool($schoolId);
        }
    }

    /**
     * Obtiene estadísticas de uso de feature flags
     */
    public function getUsageStats(): array
    {
        $totalSchools = School::count();
        $stats = [];

        foreach (self::DEFAULT_FLAGS as $flag => $default) {
            $enabledCount = School::whereJsonContains('feature_flags->' . $flag, true)->count();
            $stats[$flag] = [
                'enabled_count' => $enabledCount,
                'disabled_count' => $totalSchools - $enabledCount,
                'percentage' => $totalSchools > 0 ? round(($enabledCount / $totalSchools) * 100, 2) : 0
            ];
        }

        return $stats;
    }

    /**
     * Migración gradual: habilita una feature para un porcentaje de escuelas
     */
    public function enableGradualRollout(string $flagName, float $percentage): array
    {
        if ($percentage < 0 || $percentage > 100) {
            throw new \InvalidArgumentException('Percentage must be between 0 and 100');
        }

        $schools = School::where('is_active', true)->get();
        $targetCount = intval(ceil($schools->count() * ($percentage / 100)));
        
        // Seleccionar escuelas de forma determinística (por ID)
        $selectedSchools = $schools->sortBy('id')->take($targetCount);
        
        $updated = 0;
        foreach ($selectedSchools as $school) {
            $flags = $school->feature_flags ?? [];
            $flags[$flagName] = true;
            
            $school->update(['feature_flags' => $flags]);
            $this->clearCacheForSchool($school->id);
            $updated++;
        }

        return [
            'flag' => $flagName,
            'target_percentage' => $percentage,
            'schools_updated' => $updated,
            'total_schools' => $schools->count()
        ];
    }
}