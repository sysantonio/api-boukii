# Boukii V5 Legacy Data Migration Strategy

## Executive Summary

This document outlines a comprehensive strategy for migrating legacy data from the Boukii system to the new V5 architecture while ensuring zero data loss, maintaining business continuity, and providing robust fallback mechanisms.

## Table of Contents

1. [System Analysis](#system-analysis)
2. [Migration Architecture](#migration-architecture)
3. [Phase-by-Phase Implementation Plan](#phase-by-phase-implementation-plan)
4. [Data Transformation Specifications](#data-transformation-specifications)
5. [Risk Assessment and Mitigation](#risk-assessment-and-mitigation)
6. [Testing and Validation Procedures](#testing-and-validation-procedures)
7. [Rollback and Recovery Procedures](#rollback-and-recovery-procedures)
8. [Performance Considerations](#performance-considerations)
9. [Monitoring and Logging](#monitoring-and-logging)
10. [Special Considerations](#special-considerations)

## System Analysis

### Current State Assessment

Based on the analysis of the existing migration controller and V5 architecture:

#### Legacy System Characteristics:
- **Data Volume**: 100+ records across multiple entities
- **Core Entities**: Users, Schools, Courses, Bookings, Monitors, Clients, Degrees, Vouchers
- **Complex Relationships**: Multi-level hierarchies with booking_users, course relationships
- **Data Inconsistencies**: Mixed schema versions, nullable constraints, orphaned records
- **Authentication**: Legacy user management system with multiple user types

#### V5 System Characteristics:
- **Season-Based Architecture**: All data scoped to seasons for multi-tenancy
- **Enhanced Security**: Improved indexes and performance optimizations
- **Structured Booking System**: JSON-based booking data with comprehensive tracking
- **Advanced Logging**: V5-specific logging and monitoring capabilities
- **Modern Schema**: Standardized field naming and data types

### Key Migration Challenges Identified:

1. **Season Context Assignment**: Historical data lacks season context
2. **Multi-Tenant Isolation**: School_id enforcement throughout the system
3. **Authentication System Conflicts**: Legacy vs V5 auth services
4. **Data Schema Evolution**: Field mapping and transformation needs
5. **Complex Relationship Preservation**: Maintaining referential integrity
6. **Performance Impact**: Large dataset migration without downtime

## Migration Architecture

### Core Principles

1. **Zero Data Loss**: All legacy data must be preserved
2. **Fallback Capability**: Ability to revert to legacy system if V5 fails
3. **Data Traceability**: Track all migrated data with original IDs
4. **Incremental Migration**: Phase-by-phase approach with validation
5. **Parallel Operation**: Legacy and V5 systems coexist during transition

### Migration Components

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Legacy DB     │    │  Migration      │    │    V5 DB        │
│                 │───▶│  Engine         │───▶│                 │
│ - Old Models    │    │                 │    │ - Season Context│
│ - Mixed Schema  │    │ - Transformation│    │ - Enhanced      │
│ - No Seasons    │    │ - Validation    │    │   Security      │
└─────────────────┘    │ - Logging       │    │ - Modern Schema │
                       │ - Rollback      │    └─────────────────┘
                       └─────────────────┘
```

### Data Flow Architecture

1. **Extract**: Read legacy data with error handling
2. **Transform**: Apply V5 schema mappings and enhancements
3. **Validate**: Ensure data integrity and completeness
4. **Load**: Insert into V5 tables with transaction safety
5. **Verify**: Post-migration validation and testing

## Phase-by-Phase Implementation Plan

### Phase 1: Foundation and Infrastructure (Week 1)

**Objectives**: Establish migration infrastructure and baseline data

**Tasks**:
1. Create migration database tables for tracking
2. Set up comprehensive logging system
3. Implement data validation framework
4. Create season assignment logic
5. Establish rollback mechanisms

**Critical Tables**:
- `migration_tracking`
- `migration_logs`
- `migration_validation_results`
- `legacy_id_mappings`

**Deliverables**:
- Migration infrastructure code
- Logging and monitoring setup
- Initial validation scripts
- Documentation

### Phase 2: Core Reference Data (Week 2)

**Objectives**: Migrate foundational data required by all other entities

**Migration Order**:
1. Languages
2. Sport Types
3. Sports
4. Service Types
5. Stations
6. Schools
7. Seasons (create default seasons for historical data)

**Key Transformations**:
- Assign historical data to default seasons
- Ensure school_id consistency
- Preserve legacy IDs with mapping tables

**Validation Criteria**:
- 100% data transfer verification
- Foreign key integrity checks
- School isolation validation

### Phase 3: User Management and Authentication (Week 3)

**Objectives**: Migrate user accounts and establish V5 authentication

**Migration Order**:
1. School Users (Admin users)
2. Clients
3. Monitors
4. User-School relationships
5. Degrees and Certifications

**Key Transformations**:
- Merge legacy user types into V5 user model
- Assign season-based roles
- Transform degree relationships
- Handle authentication token migration

**Special Considerations**:
- Password hash compatibility
- Session management during transition
- Role assignment validation

### Phase 4: Course and Booking System (Week 4)

**Objectives**: Migrate complex course structures and booking data

**Migration Order**:
1. Courses (Collective and Private)
2. Course Dates and Groups
3. Course Subgroups
4. Bookings
5. Booking Users
6. Vouchers and Payments

**Key Transformations**:
- Convert to V5 JSON booking structure
- Assign season context to all bookings
- Transform course availability patterns
- Migrate payment and voucher data

**Critical Validations**:
- Booking integrity checks
- Financial data accuracy
- Course availability validation
- Monitor assignment verification

### Phase 5: Validation and Testing (Week 5)

**Objectives**: Comprehensive system validation and performance testing

**Activities**:
1. Data integrity validation
2. Performance benchmarking
3. User acceptance testing
4. Load testing with migrated data
5. Rollback procedure testing

**Success Criteria**:
- 100% data migration accuracy
- Performance within acceptable limits
- All business processes functional
- Rollback capability verified

### Phase 6: Go-Live and Monitoring (Week 6)

**Objectives**: Final cutover and system monitoring

**Activities**:
1. Final data synchronization
2. DNS/routing cutover
3. Legacy system deactivation
4. Continuous monitoring setup
5. User support and training

## Data Transformation Specifications

### Season Context Assignment Strategy

#### Default Season Creation
```sql
-- Create default seasons for schools without season data
INSERT INTO seasons (school_id, name, start_date, end_date, is_active) 
SELECT 
    s.id as school_id,
    CONCAT('Legacy Season ', YEAR(MIN(created_at))) as name,
    DATE(MIN(created_at)) as start_date,
    DATE(MAX(created_at)) as end_date,
    false as is_active
FROM schools s
LEFT JOIN [legacy_table] lt ON lt.school_id = s.id
GROUP BY s.id;
```

#### Historical Data Assignment
- All legacy data without explicit season context assigned to "Legacy Season"
- Date-based season assignment for time-bounded data
- School-specific season mapping where possible

### Data Mapping Specifications

#### User Data Migration
```php
// Legacy User -> V5 User + Client/Monitor
$v5User = [
    'email' => $legacyUser->email,
    'password' => $legacyUser->password, // Preserve hash
    'type' => $this->mapUserType($legacyUser->user_type),
    'school_id' => $legacyUser->school_id,
    'season_id' => $this->assignSeason($legacyUser),
    'legacy_id' => $legacyUser->id,
    'migrated_at' => now()
];

// Type-specific data
if ($legacyUser->user_type == 2) { // Client
    $client = $this->createClient($v5User, $legacyUser);
} elseif ($legacyUser->user_type == 3) { // Monitor
    $monitor = $this->createMonitor($v5User, $legacyUser);
}
```

#### Booking System Migration
```php
// Legacy Booking -> V5 Booking
$v5Booking = [
    'booking_reference' => $this->generateReference(),
    'season_id' => $this->determineSeason($legacyBooking),
    'school_id' => $legacyBooking->school_id,
    'client_id' => $clientMapping[$legacyBooking->user_main_id],
    'type' => $this->determineBookingType($legacyBooking),
    'status' => $this->mapBookingStatus($legacyBooking),
    'booking_data' => $this->transformBookingData($legacyBooking),
    'participants' => $this->extractParticipants($legacyBooking),
    'legacy_id' => $legacyBooking->id
];
```

#### Course Data Migration
```php
// Legacy Course -> V5 Course
$v5Course = [
    'name' => $legacyCourse->name,
    'school_id' => $legacyCourse->school_id,
    'season_id' => $this->assignCourseSeason($legacyCourse),
    'course_type' => $legacyCourse->course_type_id,
    'settings' => $this->transformCourseSettings($legacyCourse),
    'is_flexible' => $legacyCourse->duration_flexible ?? false,
    'legacy_id' => $legacyCourse->id
];
```

### Field Mapping Reference

| Legacy Field | V5 Field | Transformation |
|-------------|----------|---------------|
| `user_type` | `type` | Map 1→Admin, 2→Client, 3→Monitor |
| `school_id` | `school_id` + `season_id` | Add season context |
| `created_at` | `created_at` + `migrated_at` | Preserve + track migration |
| `degree_id` | `degree_id` | Remap to V5 degree structure |
| `course_type_id` | `type` | Transform to enum values |
| `duration_flexible` | `is_flexible` | Boolean standardization |

## Risk Assessment and Mitigation

### High-Risk Areas

#### 1. Data Loss During Migration
**Risk Level**: Critical
**Probability**: Low
**Impact**: Severe

**Mitigation Strategies**:
- Complete database backup before migration
- Transaction-based migration with rollback points
- Real-time data validation during transfer
- Parallel system operation during transition

#### 2. Performance Degradation
**Risk Level**: High
**Probability**: Medium
**Impact**: High

**Mitigation Strategies**:
- Batch processing with configurable sizes
- Off-peak migration scheduling
- Database optimization before migration
- Performance monitoring throughout process

#### 3. Authentication System Conflicts
**Risk Level**: Medium
**Probability**: Medium
**Impact**: High

**Mitigation Strategies**:
- Gradual user migration with dual authentication
- Session preservation mechanisms
- User communication and training
- Fallback to legacy authentication if needed

#### 4. Business Logic Incompatibility
**Risk Level**: Medium
**Probability**: Low
**Impact**: High

**Mitigation Strategies**:
- Comprehensive business process testing
- User acceptance testing with real data
- Parallel system validation
- Business rule documentation and verification

### Risk Monitoring

```php
// Migration risk monitoring
class MigrationRiskMonitor {
    public function checkDataIntegrity($table, $legacyCount, $v5Count) {
        if ($legacyCount !== $v5Count) {
            $this->alertCritical("Data count mismatch in {$table}");
        }
    }
    
    public function monitorPerformance($operation, $duration) {
        if ($duration > $this->thresholds[$operation]) {
            $this->alertWarning("Performance threshold exceeded");
        }
    }
    
    public function validateBusinessRules($entity) {
        // Custom validation logic per entity type
    }
}
```

## Testing and Validation Procedures

### Pre-Migration Validation

#### Data Quality Assessment
```sql
-- Check for data inconsistencies
SELECT 'users' as table_name, COUNT(*) as inconsistent_count
FROM users WHERE email IS NULL OR email = ''
UNION ALL
SELECT 'bookings', COUNT(*) 
FROM bookings WHERE school_id IS NULL
UNION ALL
SELECT 'courses', COUNT(*) 
FROM courses WHERE sport_id NOT IN (SELECT id FROM sports);
```

#### System Health Check
- Database connectivity and performance
- Available disk space and memory
- System load and resource utilization
- Backup system verification

### Migration Validation

#### Real-Time Validation
```php
class MigrationValidator {
    public function validateRecord($legacy, $v5) {
        $validations = [
            'required_fields' => $this->checkRequiredFields($v5),
            'foreign_keys' => $this->validateForeignKeys($v5),
            'data_integrity' => $this->compareData($legacy, $v5),
            'business_rules' => $this->validateBusinessRules($v5)
        ];
        
        return array_filter($validations, fn($result) => !$result);
    }
    
    public function validateBatch($legacyBatch, $v5Batch) {
        return [
            'count_match' => count($legacyBatch) === count($v5Batch),
            'id_mapping' => $this->validateIdMapping($legacyBatch, $v5Batch),
            'referential_integrity' => $this->checkReferences($v5Batch)
        ];
    }
}
```

### Post-Migration Validation

#### Data Integrity Checks
```sql
-- Verify user migration
SELECT 
    'Users' as entity,
    (SELECT COUNT(*) FROM old_users) as legacy_count,
    (SELECT COUNT(*) FROM users WHERE legacy_id IS NOT NULL) as migrated_count,
    (SELECT COUNT(*) FROM users WHERE legacy_id IS NOT NULL) / (SELECT COUNT(*) FROM old_users) * 100 as success_rate;

-- Verify booking relationships
SELECT 
    b.id,
    b.booking_reference,
    COUNT(bp.id) as participant_count
FROM v5_bookings b
LEFT JOIN v5_booking_participants bp ON bp.booking_id = b.id
WHERE b.legacy_id IS NOT NULL
GROUP BY b.id
HAVING participant_count = 0;
```

#### Business Process Validation
- User login and authentication
- Booking creation and modification
- Course enrollment processes
- Financial transaction handling
- Reporting and analytics functions

### Performance Validation

#### Load Testing Scenarios
```php
// Simulate concurrent user load
$scenarios = [
    'user_login' => ['concurrent_users' => 100, 'duration' => '5min'],
    'booking_creation' => ['concurrent_users' => 50, 'duration' => '10min'],
    'course_search' => ['concurrent_users' => 200, 'duration' => '3min'],
    'report_generation' => ['concurrent_users' => 20, 'duration' => '15min']
];
```

## Rollback and Recovery Procedures

### Rollback Strategy

#### Immediate Rollback (< 1 hour)
For critical issues discovered immediately after migration:

```bash
#!/bin/bash
# Emergency rollback script
echo "Initiating emergency rollback..."

# 1. Stop V5 application
systemctl stop boukii-v5

# 2. Restore legacy database
mysql boukii_legacy < /backup/pre_migration_backup.sql

# 3. Restart legacy application
systemctl start boukii-legacy

# 4. Update DNS/routing
# Switch load balancer back to legacy endpoints

echo "Emergency rollback complete"
```

#### Planned Rollback (1-24 hours)
For issues discovered during validation period:

```php
class MigrationRollback {
    public function initiateRollback($reason) {
        DB::beginTransaction();
        
        try {
            // 1. Mark migration as failed
            $this->logRollback($reason);
            
            // 2. Preserve V5 data for analysis
            $this->backupV5Data();
            
            // 3. Clear V5 tables
            $this->clearV5Tables();
            
            // 4. Restore legacy system state
            $this->restoreLegacyState();
            
            DB::commit();
            
        } catch (Exception $e) {
            DB::rollback();
            $this->alertCritical("Rollback failed: " . $e->getMessage());
        }
    }
}
```

### Data Recovery Procedures

#### Partial Data Recovery
For scenarios where only specific data segments need recovery:

```php
public function recoverEntityData($entityType, $timeRange) {
    $backupData = $this->getBackupData($entityType, $timeRange);
    
    foreach ($backupData as $record) {
        $existing = $this->findExistingRecord($record);
        
        if ($existing && $this->hasDataLoss($existing, $record)) {
            $this->mergeData($existing, $record);
        } elseif (!$existing) {
            $this->restoreRecord($record);
        }
    }
}
```

#### Point-in-Time Recovery
For specific timestamp recovery:

```sql
-- Restore data to specific point in time
RESTORE DATABASE boukii_v5 
FROM DISK = '/backup/boukii_v5_migration_checkpoint.bak'
WITH STOPAT = '2025-08-03 14:30:00';
```

### Recovery Validation

After any rollback or recovery operation:

```php
public function validateRecovery() {
    return [
        'data_integrity' => $this->checkDataIntegrity(),
        'system_functionality' => $this->testCriticalPaths(),
        'user_access' => $this->validateUserAuthentication(),
        'performance' => $this->benchmarkSystemPerformance()
    ];
}
```

## Performance Considerations

### Migration Performance Optimization

#### Batch Processing Strategy
```php
class OptimizedMigration {
    private $batchSize = 1000;
    private $maxMemoryUsage = '512M';
    
    public function migrateLargeTable($tableName) {
        $totalRecords = $this->getRecordCount($tableName);
        $batches = ceil($totalRecords / $this->batchSize);
        
        for ($i = 0; $i < $batches; $i++) {
            $offset = $i * $this->batchSize;
            
            // Monitor memory usage
            if (memory_get_usage() > $this->parseMemoryLimit($this->maxMemoryUsage)) {
                gc_collect_cycles();
                sleep(1); // Brief pause for memory cleanup
            }
            
            $batch = $this->getBatch($tableName, $offset, $this->batchSize);
            $this->processBatch($batch);
        }
    }
}
```

#### Database Optimization
```sql
-- Pre-migration database optimization
-- Disable foreign key checks during migration
SET FOREIGN_KEY_CHECKS = 0;

-- Optimize table settings for bulk inserts
SET SESSION sql_log_bin = 0;
SET SESSION innodb_flush_log_at_trx_commit = 0;
SET SESSION sync_binlog = 0;

-- Increase buffer sizes
SET SESSION bulk_insert_buffer_size = 256M;
SET SESSION innodb_buffer_pool_size = 2G;
```

#### Index Management
```php
public function optimizeIndexes() {
    // Drop non-essential indexes during migration
    $this->dropSecondaryIndexes();
    
    // Perform migration
    $this->runMigration();
    
    // Recreate indexes after migration
    $this->recreateIndexes();
}
```

### Runtime Performance Monitoring

```php
class PerformanceMonitor {
    public function trackMigrationProgress($entity, $processed, $total) {
        $progress = ($processed / $total) * 100;
        $eta = $this->calculateETA($processed, $total);
        
        Log::info("Migration Progress", [
            'entity' => $entity,
            'progress' => round($progress, 2) . '%',
            'processed' => $processed,
            'total' => $total,
            'eta' => $eta,
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        ]);
    }
}
```

## Monitoring and Logging

### Migration Logging Architecture

```php
class MigrationLogger {
    public function logMigrationStart($entity, $recordCount) {
        Log::channel('migration')->info('Migration Started', [
            'entity' => $entity,
            'total_records' => $recordCount,
            'timestamp' => now(),
            'memory_limit' => ini_get('memory_limit'),
            'time_limit' => ini_get('max_execution_time')
        ]);
    }
    
    public function logMigrationProgress($entity, $processed, $errors = []) {
        Log::channel('migration')->info('Migration Progress', [
            'entity' => $entity,
            'processed' => $processed,
            'errors_count' => count($errors),
            'errors' => $errors,
            'timestamp' => now()
        ]);
    }
    
    public function logMigrationError($entity, $record, $error) {
        Log::channel('migration')->error('Migration Error', [
            'entity' => $entity,
            'record_id' => $record->id ?? 'unknown',
            'error' => $error,
            'record_data' => $record,
            'stack_trace' => debug_backtrace()
        ]);
    }
}
```

### Real-Time Monitoring Dashboard

```php
class MigrationMonitoringService {
    public function getMigrationStatus() {
        return [
            'overall_progress' => $this->calculateOverallProgress(),
            'entity_progress' => $this->getEntityProgress(),
            'error_summary' => $this->getErrorSummary(),
            'performance_metrics' => $this->getPerformanceMetrics(),
            'estimated_completion' => $this->getEstimatedCompletion()
        ];
    }
    
    public function getPerformanceMetrics() {
        return [
            'records_per_second' => $this->calculateThroughput(),
            'memory_usage' => memory_get_usage(true),
            'cpu_usage' => sys_getloadavg()[0],
            'database_connections' => $this->getDbConnections(),
            'error_rate' => $this->getErrorRate()
        ];
    }
}
```

### Alert System

```php
class MigrationAlertSystem {
    public function checkAlertConditions() {
        $conditions = [
            'high_error_rate' => $this->getErrorRate() > 0.05,
            'slow_performance' => $this->getThroughput() < $this->minimumThroughput,
            'memory_pressure' => memory_get_usage() > $this->memoryThreshold,
            'timeout_risk' => $this->getEstimatedCompletion() > $this->maxDuration
        ];
        
        foreach ($conditions as $condition => $triggered) {
            if ($triggered) {
                $this->sendAlert($condition);
            }
        }
    }
}
```

## Special Considerations

### Season Context Assignment for Historical Data

#### Challenge
Legacy data lacks season context, but V5 architecture requires season_id for all operations.

#### Solution Strategy
```php
class SeasonContextAssigner {
    public function assignSeasonToHistoricalData($record, $entityType) {
        // Strategy 1: Date-based assignment
        if ($record->created_at) {
            $season = $this->findSeasonByDate($record->school_id, $record->created_at);
            if ($season) return $season->id;
        }
        
        // Strategy 2: Business logic assignment
        if ($entityType === 'booking') {
            $season = $this->findSeasonByBookingDate($record);
            if ($season) return $season->id;
        }
        
        // Strategy 3: Default legacy season
        return $this->getOrCreateLegacySeason($record->school_id);
    }
    
    private function getOrCreateLegacySeason($schoolId) {
        $season = Season::where('school_id', $schoolId)
                        ->where('name', 'LIKE', 'Legacy Season%')
                        ->first();
        
        if (!$season) {
            $season = Season::create([
                'school_id' => $schoolId,
                'name' => 'Legacy Season ' . date('Y'),
                'start_date' => '2020-01-01',
                'end_date' => '2030-12-31',
                'is_active' => false
            ]);
        }
        
        return $season->id;
    }
}
```

### Multi-Tenant Data Isolation

#### Challenge
Ensuring school_id consistency across all migrated data for proper tenant isolation.

#### Solution
```php
class TenantIsolationValidator {
    public function validateTenantIsolation($record, $entityType) {
        $violations = [];
        
        // Check direct school_id
        if (!$record->school_id) {
            $violations[] = "Missing school_id";
        }
        
        // Check related entities
        foreach ($this->getRelatedEntities($entityType) as $relation) {
            $relatedRecord = $record->{$relation};
            if ($relatedRecord && $relatedRecord->school_id !== $record->school_id) {
                $violations[] = "School_id mismatch in {$relation}";
            }
        }
        
        return $violations;
    }
}
```

### Legacy ID Preservation and Mapping

#### Challenge
Maintaining traceability between legacy and V5 systems for debugging and data verification.

#### Solution
```php
class LegacyIdMapper {
    public function createMapping($legacyId, $v5Id, $entityType) {
        DB::table('legacy_id_mappings')->insert([
            'legacy_id' => $legacyId,
            'v5_id' => $v5Id,
            'entity_type' => $entityType,
            'created_at' => now()
        ]);
    }
    
    public function findV5Id($legacyId, $entityType) {
        return DB::table('legacy_id_mappings')
                 ->where('legacy_id', $legacyId)
                 ->where('entity_type', $entityType)
                 ->value('v5_id');
    }
    
    public function findLegacyId($v5Id, $entityType) {
        return DB::table('legacy_id_mappings')
                 ->where('v5_id', $v5Id)
                 ->where('entity_type', $entityType)
                 ->value('legacy_id');
    }
}
```

### Complex Relationship Preservation

#### Challenge
Maintaining complex relationships like booking_users, course hierarchies, and monitor assignments.

#### Solution
```php
class RelationshipMapper {
    public function migrateBookingUserRelationships($legacyBooking) {
        $v5BookingId = $this->legacyMapper->findV5Id($legacyBooking->id, 'booking');
        
        foreach ($legacyBooking->booking_users as $legacyBookingUser) {
            $v5BookingUser = [
                'booking_id' => $v5BookingId,
                'client_id' => $this->legacyMapper->findV5Id($legacyBookingUser->user_id, 'client'),
                'course_id' => $this->legacyMapper->findV5Id($legacyBookingUser->course_id, 'course'),
                'monitor_id' => $this->legacyMapper->findV5Id($legacyBookingUser->monitor_id, 'monitor'),
                'status' => $this->mapBookingUserStatus($legacyBookingUser),
                'legacy_id' => $legacyBookingUser->id
            ];
            
            $this->validateRelationships($v5BookingUser);
            DB::table('v5_booking_participants')->insert($v5BookingUser);
        }
    }
}
```

### Financial Data Accuracy

#### Challenge
Ensuring all financial data (payments, vouchers, pricing) is accurately migrated without loss.

#### Solution
```php
class FinancialDataMigrator {
    public function migrateFinancialData($legacyBooking) {
        // Migrate voucher usage
        foreach ($legacyBooking->voucher_logs as $voucherLog) {
            $this->migrateVoucherLog($voucherLog, $legacyBooking);
        }
        
        // Migrate payment records
        foreach ($legacyBooking->payments as $payment) {
            $this->migratePayment($payment, $legacyBooking);
        }
        
        // Validate financial totals
        $this->validateFinancialTotals($legacyBooking);
    }
    
    private function validateFinancialTotals($legacyBooking) {
        $legacyTotal = $legacyBooking->total_amount;
        $v5Total = $this->calculateV5Total($legacyBooking);
        
        if (abs($legacyTotal - $v5Total) > 0.01) {
            throw new FinancialMismatchException(
                "Financial total mismatch for booking {$legacyBooking->id}"
            );
        }
    }
}
```

## Implementation Timeline

### Week 1: Infrastructure Setup
- [ ] Migration database schema creation
- [ ] Logging and monitoring infrastructure
- [ ] Validation framework development
- [ ] Season assignment logic implementation
- [ ] Initial testing environment setup

### Week 2: Core Data Migration
- [ ] Reference data migration (sports, stations, schools)
- [ ] Season creation and assignment
- [ ] Basic validation testing
- [ ] Performance optimization

### Week 3: User System Migration
- [ ] User account migration
- [ ] Authentication system setup
- [ ] Role and permission assignment
- [ ] Client and monitor data migration

### Week 4: Complex Data Migration
- [ ] Course system migration
- [ ] Booking data migration
- [ ] Financial data migration
- [ ] Relationship validation

### Week 5: Testing and Validation
- [ ] Comprehensive data validation
- [ ] Performance testing
- [ ] User acceptance testing
- [ ] Rollback procedure testing

### Week 6: Go-Live
- [ ] Final data synchronization
- [ ] System cutover
- [ ] Monitoring and support
- [ ] Legacy system deactivation

## Success Criteria

### Data Migration Success
- [ ] 100% data migration with zero loss
- [ ] All relationships preserved and validated
- [ ] Financial data accuracy within 0.01% tolerance
- [ ] Performance within acceptable limits (< 2x current response times)

### System Functionality Success
- [ ] All critical business processes functional
- [ ] User authentication and authorization working
- [ ] Booking system fully operational
- [ ] Reporting and analytics accurate

### Technical Success
- [ ] Database performance optimized
- [ ] Monitoring and alerting operational
- [ ] Rollback procedures tested and verified
- [ ] Documentation complete and accurate

## Conclusion

This migration strategy provides a comprehensive approach to transitioning from the legacy Boukii system to the new V5 architecture while maintaining data integrity, business continuity, and system performance. The phased approach with robust validation and rollback procedures ensures minimal risk and maximum success probability.

The strategy emphasizes:
- Zero data loss through comprehensive backup and validation
- Incremental migration with validation checkpoints
- Robust error handling and recovery procedures
- Performance optimization throughout the process
- Complete traceability and audit capabilities

Regular reviews and adjustments during implementation will ensure the strategy remains aligned with business needs and technical requirements.