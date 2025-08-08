<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\Migration\V5MigrationService;
use App\Services\Migration\MigrationValidator;
use App\Services\Migration\SeasonContextAssigner;
use App\Services\Migration\LegacyIdMapper;

class V5DataMigration extends Command
{
    protected $signature = 'boukii:migrate-to-v5 
                           {--phase=all : Migration phase to run (foundation|reference|users|courses|validation|all)}
                           {--entity= : Specific entity to migrate}
                           {--batch-size=1000 : Batch size for processing}
                           {--dry-run : Run migration in simulation mode}
                           {--force : Force migration even if validation fails}';

    protected $description = 'Migrate legacy Boukii data to V5 architecture';

    private V5MigrationService $migrationService;
    private MigrationValidator $validator;
    private SeasonContextAssigner $seasonAssigner;
    private LegacyIdMapper $legacyMapper;

    public function __construct()
    {
        parent::__construct();
        $this->migrationService = new V5MigrationService();
        $this->validator = new MigrationValidator();
        $this->seasonAssigner = new SeasonContextAssigner();
        $this->legacyMapper = new LegacyIdMapper();
    }

    public function handle()
    {
        $phase = $this->option('phase');
        $isDryRun = $this->option('dry-run');
        
        $this->info("🚀 Starting Boukii V5 Migration");
        $this->info("Phase: {$phase}");
        $this->info("Mode: " . ($isDryRun ? "DRY RUN" : "LIVE"));
        
        if ($isDryRun) {
            $this->warn("⚠️  Running in DRY RUN mode - no data will be modified");
        }

        try {
            $this->initializeMigration();
            
            switch ($phase) {
                case 'foundation':
                    $this->runFoundationPhase($isDryRun);
                    break;
                case 'reference':
                    $this->runReferenceDataPhase($isDryRun);
                    break;
                case 'users':
                    $this->runUserMigrationPhase($isDryRun);
                    break;
                case 'courses':
                    $this->runCourseMigrationPhase($isDryRun);
                    break;
                case 'validation':
                    $this->runValidationPhase();
                    break;
                case 'all':
                    $this->runAllPhases($isDryRun);
                    break;
                default:
                    $this->error("Unknown phase: {$phase}");
                    return 1;
            }

            $this->info("✅ Migration completed successfully");
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Migration failed: " . $e->getMessage());
            Log::channel('migration')->error('Migration failed', [
                'phase' => $phase,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    private function initializeMigration()
    {
        $this->info("Initializing migration infrastructure...");
        
        // Create migration tracking tables
        $this->migrationService->createMigrationTables();
        
        // Validate system prerequisites
        $prerequisites = $this->validator->checkPrerequisites();
        if (!$prerequisites['passed']) {
            throw new \Exception("Prerequisites check failed: " . implode(', ', $prerequisites['errors']));
        }
        
        $this->info("✅ Migration infrastructure ready");
    }

    private function runFoundationPhase($isDryRun)
    {
        $this->info("📋 Phase 1: Foundation and Infrastructure");
        
        $tasks = [
            'migration_tracking' => 'Setting up migration tracking',
            'validation_framework' => 'Initializing validation framework',
            'season_assignment' => 'Preparing season assignment logic',
            'rollback_mechanisms' => 'Setting up rollback mechanisms'
        ];

        foreach ($tasks as $task => $description) {
            $this->line("  • {$description}...");
            
            if (!$isDryRun) {
                $this->migrationService->executeFoundationTask($task);
            }
            
            $this->info("    ✅ Complete");
        }
    }

    private function runReferenceDataPhase($isDryRun)
    {
        $this->info("🗂️  Phase 2: Reference Data Migration");
        
        $entities = [
            'languages' => 'Languages',
            'sport_types' => 'Sport Types', 
            'sports' => 'Sports',
            'service_types' => 'Service Types',
            'stations' => 'Stations',
            'schools' => 'Schools',
            'seasons' => 'Seasons (created for historical data)'
        ];

        foreach ($entities as $table => $description) {
            $this->migrateEntity($table, $description, $isDryRun);
        }
    }

    private function runUserMigrationPhase($isDryRun)
    {
        $this->info("👥 Phase 3: User Management and Authentication");
        
        $entities = [
            'school_users' => 'School Admin Users',
            'clients' => 'Client Users',
            'monitors' => 'Monitor Users',
            'user_schools' => 'User-School Relationships',
            'degrees' => 'Degrees and Certifications'
        ];

        foreach ($entities as $entity => $description) {
            $this->migrateEntity($entity, $description, $isDryRun);
        }
    }

    private function runCourseMigrationPhase($isDryRun)
    {
        $this->info("📚 Phase 4: Course and Booking System");
        
        $entities = [
            'courses' => 'Courses (Collective and Private)',
            'course_dates' => 'Course Dates and Schedules',
            'course_groups' => 'Course Groups',
            'course_subgroups' => 'Course Subgroups',
            'bookings' => 'Bookings',
            'booking_users' => 'Booking Participants',
            'vouchers' => 'Vouchers and Payments'
        ];

        foreach ($entities as $entity => $description) {
            $this->migrateEntity($entity, $description, $isDryRun);
        }
    }

    private function runValidationPhase()
    {
        $this->info("✅ Phase 5: Comprehensive Validation");
        
        $validations = [
            'data_integrity' => 'Data Integrity Validation',
            'relationship_validation' => 'Relationship Validation',
            'financial_accuracy' => 'Financial Data Accuracy',
            'performance_testing' => 'Performance Testing',
            'business_logic' => 'Business Logic Validation'
        ];

        $results = [];
        foreach ($validations as $type => $description) {
            $this->line("  • {$description}...");
            
            $result = $this->validator->runValidation($type);
            $results[$type] = $result;
            
            if ($result['passed']) {
                $this->info("    ✅ Passed");
            } else {
                $this->error("    ❌ Failed: " . implode(', ', $result['errors']));
            }
        }

        $overallPass = collect($results)->every(fn($r) => $r['passed']);
        
        if ($overallPass) {
            $this->info("🎉 All validations passed!");
        } else {
            $this->error("⚠️  Some validations failed. Review logs for details.");
        }

        return $overallPass;
    }

    private function runAllPhases($isDryRun)
    {
        $this->runFoundationPhase($isDryRun);
        $this->runReferenceDataPhase($isDryRun);
        $this->runUserMigrationPhase($isDryRun);
        $this->runCourseMigrationPhase($isDryRun);
        
        if (!$isDryRun) {
            $this->runValidationPhase();
        }
    }

    private function migrateEntity($entity, $description, $isDryRun)
    {
        $this->line("  • Migrating {$description}...");
        
        $progress = $this->output->createProgressBar();
        
        try {
            if ($this->option('entity') && $this->option('entity') !== $entity) {
                $this->line("    ⏩ Skipped (not selected)");
                return;
            }

            $totalRecords = $this->migrationService->getEntityCount($entity);
            $progress->setMaxSteps($totalRecords);
            $progress->start();

            $batchSize = (int) $this->option('batch-size');
            $processed = 0;
            $errors = [];

            while ($processed < $totalRecords) {
                $batch = $this->migrationService->getEntityBatch($entity, $processed, $batchSize);
                
                foreach ($batch as $record) {
                    try {
                        if (!$isDryRun) {
                            $this->migrationService->migrateRecord($entity, $record);
                        }
                        
                        $processed++;
                        $progress->advance();
                        
                    } catch (\Exception $e) {
                        $errors[] = [
                            'record_id' => $record->id ?? 'unknown',
                            'error' => $e->getMessage()
                        ];
                        
                        Log::channel('migration')->error("Failed to migrate {$entity} record", [
                            'entity' => $entity,
                            'record' => $record,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            $progress->finish();
            $this->newLine();

            if (empty($errors)) {
                $this->info("    ✅ {$processed} records migrated successfully");
            } else {
                $errorCount = count($errors);
                $successCount = $processed - $errorCount;
                $this->warn("    ⚠️  {$successCount} successful, {$errorCount} errors");
                
                if ($errorCount > 0 && !$this->option('force')) {
                    throw new \Exception("Migration halted due to errors. Use --force to continue.");
                }
            }

        } catch (\Exception $e) {
            $progress->finish();
            $this->newLine();
            throw $e;
        }
    }
}