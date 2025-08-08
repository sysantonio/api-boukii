<?php

namespace App\Services\Migration;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class MigrationValidator
{
    private LegacyIdMapper $legacyMapper;

    public function __construct()
    {
        $this->legacyMapper = new LegacyIdMapper();
    }

    public function checkPrerequisites()
    {
        $checks = [
            'database_connection' => $this->checkDatabaseConnections(),
            'legacy_tables' => $this->checkLegacyTables(),
            'v5_tables' => $this->checkV5Tables(),
            'disk_space' => $this->checkDiskSpace(),
            'memory_limit' => $this->checkMemoryLimit(),
            'backup_system' => $this->checkBackupSystem()
        ];

        $passed = collect($checks)->every(fn($check) => $check['passed']);
        $errors = collect($checks)->filter(fn($check) => !$check['passed'])
                                  ->pluck('error')->toArray();

        return [
            'passed' => $passed,
            'checks' => $checks,
            'errors' => $errors
        ];
    }

    public function validateRecord($legacyRecord, $v5Record)
    {
        $errors = [];

        // Check required fields
        $requiredFieldErrors = $this->checkRequiredFields($v5Record);
        if (!empty($requiredFieldErrors)) {
            $errors = array_merge($errors, $requiredFieldErrors);
        }

        // Check foreign key constraints
        $foreignKeyErrors = $this->validateForeignKeys($v5Record);
        if (!empty($foreignKeyErrors)) {
            $errors = array_merge($errors, $foreignKeyErrors);
        }

        // Check data integrity
        $integrityErrors = $this->compareData($legacyRecord, $v5Record);
        if (!empty($integrityErrors)) {
            $errors = array_merge($errors, $integrityErrors);
        }

        // Check business rules
        $businessRuleErrors = $this->validateBusinessRules($v5Record);
        if (!empty($businessRuleErrors)) {
            $errors = array_merge($errors, $businessRuleErrors);
        }

        return $errors;
    }

    public function runValidation($validationType)
    {
        switch ($validationType) {
            case 'data_integrity':
                return $this->validateDataIntegrity();
            case 'relationship_validation':
                return $this->validateRelationships();
            case 'financial_accuracy':
                return $this->validateFinancialAccuracy();
            case 'performance_testing':
                return $this->runPerformanceTests();
            case 'business_logic':
                return $this->validateBusinessLogic();
            default:
                throw new \Exception("Unknown validation type: {$validationType}");
        }
    }

    public function validateBatch($legacyBatch, $v5Batch)
    {
        $validations = [];

        // Count validation
        $validations['count_match'] = count($legacyBatch) === count($v5Batch);

        // ID mapping validation
        $validations['id_mapping'] = $this->validateIdMapping($legacyBatch, $v5Batch);

        // Referential integrity
        $validations['referential_integrity'] = $this->checkReferences($v5Batch);

        return $validations;
    }

    private function checkDatabaseConnections()
    {
        try {
            // Test legacy database connection
            DB::connection('old')->getPdo();
            
            // Test main database connection
            DB::connection()->getPdo();
            
            return ['passed' => true];
        } catch (\Exception $e) {
            return [
                'passed' => false,
                'error' => 'Database connection failed: ' . $e->getMessage()
            ];
        }
    }

    private function checkLegacyTables()
    {
        $requiredTables = [
            'users', 'schools', 'courses2', 'bookings2', 'course_groups2',
            'sports', 'stations', 'degrees', 'vouchers'
        ];

        $missingTables = [];

        foreach ($requiredTables as $table) {
            try {
                if (!Schema::connection('old')->hasTable($table)) {
                    $missingTables[] = $table;
                }
            } catch (\Exception $e) {
                $missingTables[] = $table . ' (check failed)';
            }
        }

        if (empty($missingTables)) {
            return ['passed' => true];
        }

        return [
            'passed' => false,
            'error' => 'Missing legacy tables: ' . implode(', ', $missingTables)
        ];
    }

    private function checkV5Tables()
    {
        $requiredTables = [
            'seasons', 'schools', 'users', 'clients', 'monitors', 
            'courses', 'v5_bookings', 'legacy_id_mappings'
        ];

        $missingTables = [];

        foreach ($requiredTables as $table) {
            if (!Schema::hasTable($table)) {
                $missingTables[] = $table;
            }
        }

        if (empty($missingTables)) {
            return ['passed' => true];
        }

        return [
            'passed' => false,
            'error' => 'Missing V5 tables: ' . implode(', ', $missingTables)
        ];
    }

    private function checkDiskSpace()
    {
        $freeSpace = disk_free_space(storage_path());
        $requiredSpace = 5 * 1024 * 1024 * 1024; // 5GB

        if ($freeSpace >= $requiredSpace) {
            return ['passed' => true];
        }

        return [
            'passed' => false,
            'error' => 'Insufficient disk space. Required: 5GB, Available: ' . 
                      round($freeSpace / (1024 * 1024 * 1024), 2) . 'GB'
        ];
    }

    private function checkMemoryLimit()
    {
        $memoryLimit = ini_get('memory_limit');
        $memoryBytes = $this->parseMemoryLimit($memoryLimit);
        $requiredBytes = 512 * 1024 * 1024; // 512MB

        if ($memoryBytes >= $requiredBytes || $memoryBytes === -1) {
            return ['passed' => true];
        }

        return [
            'passed' => false,
            'error' => "Insufficient memory limit. Required: 512MB, Current: {$memoryLimit}"
        ];
    }

    private function checkBackupSystem()
    {
        // Check if we can create backups
        try {
            $testBackup = storage_path('migration-test-backup.sql');
            file_put_contents($testBackup, 'test backup');
            unlink($testBackup);
            
            return ['passed' => true];
        } catch (\Exception $e) {
            return [
                'passed' => false,
                'error' => 'Backup system check failed: ' . $e->getMessage()
            ];
        }
    }

    private function checkRequiredFields($record)
    {
        $errors = [];
        
        if (is_array($record)) {
            // Check for common required fields
            $requiredFields = ['id', 'created_at', 'updated_at'];
            
            foreach ($requiredFields as $field) {
                if (!isset($record[$field]) || $record[$field] === null) {
                    $errors[] = "Missing required field: {$field}";
                }
            }
        }

        return $errors;
    }

    private function validateForeignKeys($record)
    {
        $errors = [];

        if (is_array($record) && isset($record['school_id'])) {
            $schoolExists = DB::table('schools')
                             ->where('id', $record['school_id'])
                             ->exists();
            
            if (!$schoolExists) {
                $errors[] = "Invalid school_id: {$record['school_id']}";
            }
        }

        if (is_array($record) && isset($record['season_id'])) {
            $seasonExists = DB::table('seasons')
                             ->where('id', $record['season_id'])
                             ->exists();
            
            if (!$seasonExists) {
                $errors[] = "Invalid season_id: {$record['season_id']}";
            }
        }

        return $errors;
    }

    private function compareData($legacyRecord, $v5Record)
    {
        $errors = [];

        // Compare common fields that should be preserved
        $fieldsToCompare = ['name', 'email', 'created_at'];

        foreach ($fieldsToCompare as $field) {
            if (isset($legacyRecord->$field) && isset($v5Record[$field])) {
                if ($legacyRecord->$field !== $v5Record[$field]) {
                    $errors[] = "Data mismatch in field {$field}: " .
                               "legacy='{$legacyRecord->$field}' vs v5='{$v5Record[$field]}'";
                }
            }
        }

        return $errors;
    }

    private function validateBusinessRules($record)
    {
        $errors = [];

        // Example business rule validations
        if (is_array($record)) {
            // Email format validation
            if (isset($record['email']) && !filter_var($record['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Invalid email format: {$record['email']}";
            }

            // Date validation
            if (isset($record['birth_date']) && $record['birth_date'] > date('Y-m-d')) {
                $errors[] = "Birth date cannot be in the future: {$record['birth_date']}";
            }
        }

        return $errors;
    }

    private function validateDataIntegrity()
    {
        $errors = [];

        // Check record counts match
        $entities = ['schools', 'users', 'courses'];
        
        foreach ($entities as $entity) {
            $legacyCount = $this->getLegacyCount($entity);
            $v5Count = $this->getV5Count($entity);
            
            if ($legacyCount !== $v5Count) {
                $errors[] = "Count mismatch for {$entity}: legacy={$legacyCount}, v5={$v5Count}";
            }
        }

        return [
            'passed' => empty($errors),
            'errors' => $errors
        ];
    }

    private function validateRelationships()
    {
        $errors = [];

        // Check user-school relationships
        $orphanedUsers = DB::table('users')
                          ->leftJoin('schools', 'users.school_id', '=', 'schools.id')
                          ->whereNotNull('users.school_id')
                          ->whereNull('schools.id')
                          ->count();

        if ($orphanedUsers > 0) {
            $errors[] = "Found {$orphanedUsers} users with invalid school references";
        }

        // Check season relationships
        $orphanedSeasons = DB::table('seasons')
                            ->leftJoin('schools', 'seasons.school_id', '=', 'schools.id')
                            ->whereNull('schools.id')
                            ->count();

        if ($orphanedSeasons > 0) {
            $errors[] = "Found {$orphanedSeasons} seasons with invalid school references";
        }

        return [
            'passed' => empty($errors),
            'errors' => $errors
        ];
    }

    private function validateFinancialAccuracy()
    {
        $errors = [];

        // Compare booking totals
        $legacyTotal = DB::connection('old')
                        ->table('bookings2')
                        ->sum('total_amount');

        $v5Total = DB::table('v5_bookings')
                    ->sum('total_price');

        $difference = abs($legacyTotal - $v5Total);
        $tolerance = $legacyTotal * 0.01; // 1% tolerance

        if ($difference > $tolerance) {
            $errors[] = "Financial totals mismatch: legacy={$legacyTotal}, v5={$v5Total}, diff={$difference}";
        }

        return [
            'passed' => empty($errors),
            'errors' => $errors
        ];
    }

    private function runPerformanceTests()
    {
        $results = [];

        // Test database query performance
        $start = microtime(true);
        DB::table('users')->limit(1000)->get();
        $userQueryTime = microtime(true) - $start;

        $start = microtime(true);
        DB::table('v5_bookings')->limit(1000)->get();
        $bookingQueryTime = microtime(true) - $start;

        $results['user_query_time'] = $userQueryTime;
        $results['booking_query_time'] = $bookingQueryTime;

        // Performance thresholds (in seconds)
        $maxQueryTime = 2.0;

        $errors = [];
        if ($userQueryTime > $maxQueryTime) {
            $errors[] = "User query too slow: {$userQueryTime}s";
        }
        if ($bookingQueryTime > $maxQueryTime) {
            $errors[] = "Booking query too slow: {$bookingQueryTime}s";
        }

        return [
            'passed' => empty($errors),
            'errors' => $errors,
            'metrics' => $results
        ];
    }

    private function validateBusinessLogic()
    {
        $errors = [];

        // Test user authentication
        $testUser = DB::table('users')->first();
        if (!$testUser) {
            $errors[] = "No users found for authentication test";
        }

        // Test booking creation logic
        $testBooking = DB::table('v5_bookings')->first();
        if (!$testBooking) {
            $errors[] = "No bookings found for business logic test";
        }

        return [
            'passed' => empty($errors),
            'errors' => $errors
        ];
    }

    private function validateIdMapping($legacyBatch, $v5Batch)
    {
        foreach ($legacyBatch as $index => $legacyRecord) {
            if (!isset($v5Batch[$index])) {
                return false;
            }

            $v5Record = $v5Batch[$index];
            $mappingExists = $this->legacyMapper->findV5Id($legacyRecord->id, 'user') !== null;
            
            if (!$mappingExists) {
                return false;
            }
        }

        return true;
    }

    private function checkReferences($v5Batch)
    {
        foreach ($v5Batch as $record) {
            if (is_array($record) && isset($record['school_id'])) {
                $schoolExists = DB::table('schools')
                                 ->where('id', $record['school_id'])
                                 ->exists();
                if (!$schoolExists) {
                    return false;
                }
            }
        }

        return true;
    }

    private function getLegacyCount($entity)
    {
        $tableMappings = [
            'schools' => 'schools',
            'users' => 'users',
            'courses' => 'courses2',
            'bookings' => 'bookings2'
        ];

        $table = $tableMappings[$entity] ?? $entity;
        
        try {
            return DB::connection('old')->table($table)->count();
        } catch (\Exception $e) {
            Log::channel('migration')->warning("Could not count legacy {$entity}", [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    private function getV5Count($entity)
    {
        $tableMappings = [
            'schools' => 'schools',
            'users' => 'users',
            'courses' => 'courses',
            'bookings' => 'v5_bookings'
        ];

        $table = $tableMappings[$entity] ?? $entity;
        
        try {
            return DB::table($table)->count();
        } catch (\Exception $e) {
            Log::channel('migration')->warning("Could not count V5 {$entity}", [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    private function parseMemoryLimit($memoryLimit)
    {
        if ($memoryLimit === '-1') {
            return -1; // Unlimited
        }

        $unit = strtolower(substr($memoryLimit, -1));
        $value = (int) substr($memoryLimit, 0, -1);

        switch ($unit) {
            case 'g':
                return $value * 1024 * 1024 * 1024;
            case 'm':
                return $value * 1024 * 1024;
            case 'k':
                return $value * 1024;
            default:
                return (int) $memoryLimit;
        }
    }
}