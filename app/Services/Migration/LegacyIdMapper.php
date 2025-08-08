<?php

namespace App\Services\Migration;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class LegacyIdMapper
{
    private $cacheEnabled = true;
    private $cachePrefix = 'legacy_mapping_';
    private $cacheTtl = 3600; // 1 hour

    public function createMapping($legacyId, $v5Id, $entityType, $additionalData = null)
    {
        $mapping = [
            'entity_type' => $entityType,
            'legacy_id' => $legacyId,
            'v5_id' => $v5Id,
            'additional_data' => $additionalData ? json_encode($additionalData) : null,
            'created_at' => now(),
            'updated_at' => now()
        ];

        try {
            DB::table('legacy_id_mappings')->insert($mapping);
            
            // Cache the mapping for quick lookups
            if ($this->cacheEnabled) {
                $cacheKey = $this->getCacheKey($entityType, $legacyId);
                Cache::put($cacheKey, $v5Id, $this->cacheTtl);
            }

            Log::channel('migration')->debug('Created ID mapping', [
                'entity_type' => $entityType,
                'legacy_id' => $legacyId,
                'v5_id' => $v5Id
            ]);

            return true;

        } catch (\Exception $e) {
            Log::channel('migration')->error('Failed to create ID mapping', [
                'entity_type' => $entityType,
                'legacy_id' => $legacyId,
                'v5_id' => $v5Id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    public function findV5Id($legacyId, $entityType)
    {
        if (!$legacyId) {
            return null;
        }

        // Check cache first
        if ($this->cacheEnabled) {
            $cacheKey = $this->getCacheKey($entityType, $legacyId);
            $cachedId = Cache::get($cacheKey);
            if ($cachedId !== null) {
                return $cachedId;
            }
        }

        // Query database
        $mapping = DB::table('legacy_id_mappings')
                     ->where('entity_type', $entityType)
                     ->where('legacy_id', $legacyId)
                     ->first();

        if ($mapping) {
            // Cache the result
            if ($this->cacheEnabled) {
                $cacheKey = $this->getCacheKey($entityType, $legacyId);
                Cache::put($cacheKey, $mapping->v5_id, $this->cacheTtl);
            }
            
            return $mapping->v5_id;
        }

        Log::channel('migration')->warning('Legacy ID mapping not found', [
            'entity_type' => $entityType,
            'legacy_id' => $legacyId
        ]);

        return null;
    }

    public function findLegacyId($v5Id, $entityType)
    {
        if (!$v5Id) {
            return null;
        }

        $mapping = DB::table('legacy_id_mappings')
                     ->where('entity_type', $entityType)
                     ->where('v5_id', $v5Id)
                     ->first();

        return $mapping ? $mapping->legacy_id : null;
    }

    public function getMappingWithData($legacyId, $entityType)
    {
        $mapping = DB::table('legacy_id_mappings')
                     ->where('entity_type', $entityType)
                     ->where('legacy_id', $legacyId)
                     ->first();

        if ($mapping) {
            return [
                'v5_id' => $mapping->v5_id,
                'additional_data' => $mapping->additional_data ? 
                                   json_decode($mapping->additional_data, true) : null,
                'created_at' => $mapping->created_at
            ];
        }

        return null;
    }

    public function updateMapping($legacyId, $entityType, $newV5Id = null, $additionalData = null)
    {
        $updateData = ['updated_at' => now()];

        if ($newV5Id !== null) {
            $updateData['v5_id'] = $newV5Id;
        }

        if ($additionalData !== null) {
            $updateData['additional_data'] = json_encode($additionalData);
        }

        $updated = DB::table('legacy_id_mappings')
                     ->where('entity_type', $entityType)
                     ->where('legacy_id', $legacyId)
                     ->update($updateData);

        if ($updated && $this->cacheEnabled) {
            $cacheKey = $this->getCacheKey($entityType, $legacyId);
            Cache::forget($cacheKey);
            
            if ($newV5Id !== null) {
                Cache::put($cacheKey, $newV5Id, $this->cacheTtl);
            }
        }

        return $updated > 0;
    }

    public function deleteMapping($legacyId, $entityType)
    {
        $deleted = DB::table('legacy_id_mappings')
                     ->where('entity_type', $entityType)
                     ->where('legacy_id', $legacyId)
                     ->delete();

        if ($deleted && $this->cacheEnabled) {
            $cacheKey = $this->getCacheKey($entityType, $legacyId);
            Cache::forget($cacheKey);
        }

        return $deleted > 0;
    }

    public function batchFindV5Ids($legacyIds, $entityType)
    {
        if (empty($legacyIds)) {
            return [];
        }

        $mappings = [];
        $uncachedIds = [];

        // Check cache for each ID
        if ($this->cacheEnabled) {
            foreach ($legacyIds as $legacyId) {
                $cacheKey = $this->getCacheKey($entityType, $legacyId);
                $cachedId = Cache::get($cacheKey);
                
                if ($cachedId !== null) {
                    $mappings[$legacyId] = $cachedId;
                } else {
                    $uncachedIds[] = $legacyId;
                }
            }
        } else {
            $uncachedIds = $legacyIds;
        }

        // Query database for uncached IDs
        if (!empty($uncachedIds)) {
            $dbMappings = DB::table('legacy_id_mappings')
                           ->where('entity_type', $entityType)
                           ->whereIn('legacy_id', $uncachedIds)
                           ->pluck('v5_id', 'legacy_id')
                           ->toArray();

            // Cache the results
            if ($this->cacheEnabled) {
                foreach ($dbMappings as $legacyId => $v5Id) {
                    $cacheKey = $this->getCacheKey($entityType, $legacyId);
                    Cache::put($cacheKey, $v5Id, $this->cacheTtl);
                }
            }

            $mappings = array_merge($mappings, $dbMappings);
        }

        return $mappings;
    }

    public function getMappingStats($entityType = null)
    {
        $query = DB::table('legacy_id_mappings');

        if ($entityType) {
            $query->where('entity_type', $entityType);
        }

        $stats = $query->selectRaw('
                entity_type,
                COUNT(*) as total_mappings,
                MIN(created_at) as first_mapping,
                MAX(created_at) as last_mapping
            ')
            ->groupBy('entity_type')
            ->get();

        return $stats->toArray();
    }

    public function validateMappings($entityType = null)
    {
        $query = DB::table('legacy_id_mappings as lim');

        if ($entityType) {
            $query->where('lim.entity_type', $entityType);
        }

        $issues = [];

        // Check for duplicate legacy IDs
        $duplicates = (clone $query)
                     ->selectRaw('entity_type, legacy_id, COUNT(*) as count')
                     ->groupBy('entity_type', 'legacy_id')
                     ->having('count', '>', 1)
                     ->get();

        foreach ($duplicates as $duplicate) {
            $issues[] = [
                'type' => 'duplicate_legacy_id',
                'entity_type' => $duplicate->entity_type,
                'legacy_id' => $duplicate->legacy_id,
                'count' => $duplicate->count
            ];
        }

        // Check for duplicate V5 IDs (shouldn't happen but worth checking)
        $v5Duplicates = (clone $query)
                       ->selectRaw('entity_type, v5_id, COUNT(*) as count')
                       ->groupBy('entity_type', 'v5_id')
                       ->having('count', '>', 1)
                       ->get();

        foreach ($v5Duplicates as $duplicate) {
            $issues[] = [
                'type' => 'duplicate_v5_id',
                'entity_type' => $duplicate->entity_type,
                'v5_id' => $duplicate->v5_id,
                'count' => $duplicate->count
            ];
        }

        return $issues;
    }

    public function exportMappings($entityType = null, $format = 'json')
    {
        $query = DB::table('legacy_id_mappings');

        if ($entityType) {
            $query->where('entity_type', $entityType);
        }

        $mappings = $query->orderBy('entity_type')
                         ->orderBy('legacy_id')
                         ->get();

        switch ($format) {
            case 'csv':
                return $this->exportToCsv($mappings);
            case 'json':
            default:
                return $mappings->toJson(JSON_PRETTY_PRINT);
        }
    }

    public function clearCache($entityType = null, $legacyId = null)
    {
        if (!$this->cacheEnabled) {
            return;
        }

        if ($entityType && $legacyId) {
            // Clear specific mapping
            $cacheKey = $this->getCacheKey($entityType, $legacyId);
            Cache::forget($cacheKey);
        } elseif ($entityType) {
            // Clear all mappings for entity type (requires pattern matching)
            $pattern = $this->cachePrefix . $entityType . '_*';
            // Note: This would require Redis or similar cache that supports pattern matching
            Log::channel('migration')->info('Clearing cache for entity type', ['entity_type' => $entityType]);
        } else {
            // Clear all mapping cache
            Cache::flush();
        }
    }

    public function rebuildCache($entityType = null)
    {
        if (!$this->cacheEnabled) {
            return;
        }

        $query = DB::table('legacy_id_mappings');

        if ($entityType) {
            $query->where('entity_type', $entityType);
        }

        $mappings = $query->get();
        $cached = 0;

        foreach ($mappings as $mapping) {
            $cacheKey = $this->getCacheKey($mapping->entity_type, $mapping->legacy_id);
            Cache::put($cacheKey, $mapping->v5_id, $this->cacheTtl);
            $cached++;
        }

        Log::channel('migration')->info('Rebuilt mapping cache', [
            'entity_type' => $entityType,
            'cached_count' => $cached
        ]);

        return $cached;
    }

    private function getCacheKey($entityType, $legacyId)
    {
        return $this->cachePrefix . $entityType . '_' . $legacyId;
    }

    private function exportToCsv($mappings)
    {
        $csv = "entity_type,legacy_id,v5_id,additional_data,created_at,updated_at\n";

        foreach ($mappings as $mapping) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%s,%s\n",
                $mapping->entity_type,
                $mapping->legacy_id,
                $mapping->v5_id,
                $mapping->additional_data ? '"' . str_replace('"', '""', $mapping->additional_data) . '"' : '',
                $mapping->created_at,
                $mapping->updated_at
            );
        }

        return $csv;
    }

    public function getOrphanedMappings()
    {
        // Find mappings where the V5 record no longer exists
        $orphaned = [];

        $entityTables = [
            'user' => 'users',
            'client' => 'clients',
            'monitor' => 'monitors',
            'school' => 'schools',
            'course' => 'courses',
            'booking' => 'v5_bookings',
            'season' => 'seasons'
        ];

        foreach ($entityTables as $entityType => $tableName) {
            $orphanedIds = DB::table('legacy_id_mappings as lim')
                            ->leftJoin($tableName . ' as t', 'lim.v5_id', '=', 't.id')
                            ->where('lim.entity_type', $entityType)
                            ->whereNull('t.id')
                            ->pluck('lim.legacy_id');

            if ($orphanedIds->isNotEmpty()) {
                $orphaned[$entityType] = $orphanedIds->toArray();
            }
        }

        return $orphaned;
    }

    public function cleanupOrphanedMappings()
    {
        $orphaned = $this->getOrphanedMappings();
        $cleaned = 0;

        foreach ($orphaned as $entityType => $legacyIds) {
            $deleted = DB::table('legacy_id_mappings')
                        ->where('entity_type', $entityType)
                        ->whereIn('legacy_id', $legacyIds)
                        ->delete();
            
            $cleaned += $deleted;

            // Clear cache for deleted mappings
            if ($this->cacheEnabled) {
                foreach ($legacyIds as $legacyId) {
                    $cacheKey = $this->getCacheKey($entityType, $legacyId);
                    Cache::forget($cacheKey);
                }
            }
        }

        Log::channel('migration')->info('Cleaned up orphaned mappings', [
            'cleaned_count' => $cleaned,
            'orphaned_by_type' => array_map('count', $orphaned)
        ]);

        return $cleaned;
    }
}