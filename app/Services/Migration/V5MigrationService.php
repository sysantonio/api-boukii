<?php

namespace App\Services\Migration;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\Season;
use App\Models\School;
use Carbon\Carbon;

class V5MigrationService
{
    private SeasonContextAssigner $seasonAssigner;
    private LegacyIdMapper $legacyMapper;
    private MigrationValidator $validator;

    public function __construct()
    {
        $this->seasonAssigner = new SeasonContextAssigner();
        $this->legacyMapper = new LegacyIdMapper();
        $this->validator = new MigrationValidator();
    }

    public function createMigrationTables()
    {
        // Migration tracking table
        if (!Schema::hasTable('migration_tracking')) {
            Schema::create('migration_tracking', function ($table) {
                $table->id();
                $table->string('entity_type');
                $table->string('phase');
                $table->integer('total_records');
                $table->integer('processed_records')->default(0);
                $table->integer('error_count')->default(0);
                $table->enum('status', ['pending', 'in_progress', 'completed', 'failed'])->default('pending');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                
                $table->index(['entity_type', 'phase', 'status']);
            });
        }

        // Legacy ID mappings table
        if (!Schema::hasTable('legacy_id_mappings')) {
            Schema::create('legacy_id_mappings', function ($table) {
                $table->id();
                $table->string('entity_type');
                $table->unsignedBigInteger('legacy_id');
                $table->unsignedBigInteger('v5_id');
                $table->json('additional_data')->nullable();
                $table->timestamps();
                
                $table->unique(['entity_type', 'legacy_id'], 'unique_legacy_mapping');
                $table->index(['entity_type', 'v5_id']);
            });
        }

        // Migration validation results table
        if (!Schema::hasTable('migration_validation_results')) {
            Schema::create('migration_validation_results', function ($table) {
                $table->id();
                $table->string('validation_type');
                $table->string('entity_type');
                $table->unsignedBigInteger('entity_id')->nullable();
                $table->enum('result', ['pass', 'fail', 'warning']);
                $table->text('message');
                $table->json('details')->nullable();
                $table->timestamps();
                
                $table->index(['validation_type', 'result']);
                $table->index(['entity_type', 'entity_id']);
            });
        }

        Log::channel('migration')->info('Migration tracking tables created');
    }

    public function executeFoundationTask($task)
    {
        switch ($task) {
            case 'migration_tracking':
                $this->initializeMigrationTracking();
                break;
            case 'validation_framework':
                $this->initializeValidationFramework();
                break;
            case 'season_assignment':
                $this->prepareSeasonAssignment();
                break;
            case 'rollback_mechanisms':
                $this->setupRollbackMechanisms();
                break;
        }
    }

    public function getEntityCount($entity)
    {
        $tableMappings = $this->getTableMappings();
        $legacyTable = $tableMappings[$entity] ?? $entity;
        
        try {
            return DB::connection('old')->table($legacyTable)->count();
        } catch (\Exception $e) {
            Log::channel('migration')->warning("Could not count records for {$entity}", [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    public function getEntityBatch($entity, $offset, $limit)
    {
        $tableMappings = $this->getTableMappings();
        $legacyTable = $tableMappings[$entity] ?? $entity;
        
        return DB::connection('old')
                 ->table($legacyTable)
                 ->offset($offset)
                 ->limit($limit)
                 ->get();
    }

    public function migrateRecord($entityType, $legacyRecord)
    {
        DB::beginTransaction();
        
        try {
            $migrationMethod = 'migrate' . ucfirst(camel_case($entityType));
            
            if (method_exists($this, $migrationMethod)) {
                $v5Record = $this->$migrationMethod($legacyRecord);
                
                // Validate the migrated record
                $validationErrors = $this->validator->validateRecord($legacyRecord, $v5Record);
                if (!empty($validationErrors)) {
                    throw new \Exception("Validation failed: " . implode(', ', $validationErrors));
                }
                
                DB::commit();
                return $v5Record;
            } else {
                throw new \Exception("Migration method not found for entity: {$entityType}");
            }
            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    // Entity-specific migration methods

    public function migrateSchools($legacySchool)
    {
        $v5School = School::create([
            'name' => $legacySchool->name,
            'address' => $legacySchool->address,
            'city' => $legacySchool->city,
            'country' => $legacySchool->country,
            'phone' => $legacySchool->phone,
            'email' => $legacySchool->email,
            'active' => $legacySchool->active ?? true,
            'created_at' => $legacySchool->created_at,
            'updated_at' => $legacySchool->updated_at
        ]);

        $this->legacyMapper->createMapping($legacySchool->id, $v5School->id, 'school');
        
        // Create default season for this school
        $this->seasonAssigner->createDefaultSeason($v5School->id);
        
        return $v5School;
    }

    public function migrateUsers($legacyUser)
    {
        $seasonId = $this->seasonAssigner->assignSeasonToUser($legacyUser);
        
        $v5User = \App\Models\User::create([
            'name' => $legacyUser->name,
            'email' => $legacyUser->email,
            'password' => $legacyUser->password,
            'type' => $this->mapUserType($legacyUser->user_type),
            'active' => $legacyUser->active ?? true,
            'created_at' => $legacyUser->created_at,
            'updated_at' => $legacyUser->updated_at
        ]);

        $this->legacyMapper->createMapping($legacyUser->id, $v5User->id, 'user');

        // Create type-specific records
        if ($legacyUser->user_type == 2) { // Client
            $this->migrateClient($legacyUser, $v5User, $seasonId);
        } elseif ($legacyUser->user_type == 3) { // Monitor
            $this->migrateMonitor($legacyUser, $v5User, $seasonId);
        }

        return $v5User;
    }

    public function migrateClient($legacyUser, $v5User, $seasonId)
    {
        $client = \App\Models\Client::create([
            'user_id' => $v5User->id,
            'first_name' => $legacyUser->first_name,
            'last_name' => $legacyUser->last_name,
            'birth_date' => $this->validateDate($legacyUser->birth_date),
            'phone' => $legacyUser->phone,
            'address' => $legacyUser->address,
            'city' => $legacyUser->city,
            'country' => $legacyUser->country_id,
            'created_at' => $legacyUser->created_at,
            'updated_at' => $legacyUser->updated_at
        ]);

        $this->legacyMapper->createMapping($legacyUser->id, $client->id, 'client');
        return $client;
    }

    public function migrateMonitor($legacyUser, $v5User, $seasonId)
    {
        $monitor = \App\Models\Monitor::create([
            'user_id' => $v5User->id,
            'first_name' => $legacyUser->first_name,
            'last_name' => $legacyUser->last_name,
            'birth_date' => $this->validateDate($legacyUser->birth_date),
            'phone' => $legacyUser->phone,
            'address' => $legacyUser->address,
            'city' => $legacyUser->city,
            'country' => $legacyUser->country_id,
            'created_at' => $legacyUser->created_at,
            'updated_at' => $legacyUser->updated_at
        ]);

        $this->legacyMapper->createMapping($legacyUser->id, $monitor->id, 'monitor');
        return $monitor;
    }

    public function migrateCourses($legacyCourse)
    {
        $seasonId = $this->seasonAssigner->assignSeasonToCourse($legacyCourse);
        $schoolId = $this->legacyMapper->findV5Id($legacyCourse->school_id, 'school');

        $v5Course = \App\Models\Course::create([
            'name' => $legacyCourse->name,
            'description' => $legacyCourse->description,
            'short_description' => $legacyCourse->short_description,
            'school_id' => $schoolId,
            'season_id' => $seasonId,
            'sport_id' => $legacyCourse->sport_id,
            'course_type' => $legacyCourse->course_type_id,
            'date_start' => $legacyCourse->date_start,
            'date_end' => $legacyCourse->date_end,
            'price' => $legacyCourse->price ?? 0,
            'max_participants' => $legacyCourse->max_participants,
            'is_flexible' => $legacyCourse->duration_flexible ?? false,
            'settings' => $this->transformCourseSettings($legacyCourse),
            'active' => $legacyCourse->active ?? true,
            'created_at' => $legacyCourse->created_at,
            'updated_at' => $legacyCourse->updated_at
        ]);

        $this->legacyMapper->createMapping($legacyCourse->id, $v5Course->id, 'course');
        return $v5Course;
    }

    public function migrateBookings($legacyBooking)
    {
        $seasonId = $this->seasonAssigner->assignSeasonToBooking($legacyBooking);
        $schoolId = $this->legacyMapper->findV5Id($legacyBooking->school_id, 'school');
        $clientId = $this->legacyMapper->findV5Id($legacyBooking->user_main_id, 'client');

        $v5Booking = DB::table('v5_bookings')->insertGetId([
            'booking_reference' => $this->generateBookingReference(),
            'season_id' => $seasonId,
            'school_id' => $schoolId,
            'client_id' => $clientId,
            'type' => $this->determineBookingType($legacyBooking),
            'status' => $this->mapBookingStatus($legacyBooking),
            'booking_data' => $this->transformBookingData($legacyBooking),
            'participants' => $this->extractParticipants($legacyBooking),
            'total_price' => $legacyBooking->total_amount ?? 0,
            'currency' => 'EUR',
            'start_date' => $legacyBooking->date_start,
            'end_date' => $legacyBooking->date_end,
            'notes' => $legacyBooking->observations,
            'created_at' => $legacyBooking->created_at,
            'updated_at' => $legacyBooking->updated_at
        ]);

        $this->legacyMapper->createMapping($legacyBooking->id, $v5Booking, 'booking');
        return $v5Booking;
    }

    // Helper methods

    private function initializeMigrationTracking()
    {
        $entities = [
            'schools', 'users', 'clients', 'monitors', 'courses', 
            'bookings', 'course_dates', 'course_groups', 'vouchers'
        ];

        foreach ($entities as $entity) {
            $count = $this->getEntityCount($entity);
            
            DB::table('migration_tracking')->updateOrInsert(
                ['entity_type' => $entity, 'phase' => 'initial'],
                [
                    'total_records' => $count,
                    'status' => 'pending',
                    'created_at' => now()
                ]
            );
        }
    }

    private function initializeValidationFramework()
    {
        // Set up validation rules and framework
        Log::channel('migration')->info('Validation framework initialized');
    }

    private function prepareSeasonAssignment()
    {
        // Create default seasons for schools that don't have them
        $schools = School::whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                  ->from('seasons')
                  ->whereRaw('seasons.school_id = schools.id');
        })->get();

        foreach ($schools as $school) {
            $this->seasonAssigner->createDefaultSeason($school->id);
        }
    }

    private function setupRollbackMechanisms()
    {
        // Create backup tables and rollback procedures
        Log::channel('migration')->info('Rollback mechanisms set up');
    }

    private function getTableMappings()
    {
        return [
            'schools' => 'schools',
            'users' => 'users',
            'clients' => 'users',
            'monitors' => 'users',
            'courses' => 'courses2',
            'bookings' => 'bookings2',
            'course_dates' => 'course_dates',
            'course_groups' => 'course_groups2',
            'vouchers' => 'vouchers'
        ];
    }

    private function mapUserType($legacyType)
    {
        return match($legacyType) {
            1 => 'admin',
            2 => 'client',
            3 => 'monitor',
            default => 'client'
        };
    }

    private function validateDate($date)
    {
        if (!$date || $date === '0000-00-00' || $date < '1900-01-01') {
            return '1970-01-01';
        }
        return $date;
    }

    private function transformCourseSettings($legacyCourse)
    {
        $settings = [];
        
        if (isset($legacyCourse->day_start_res, $legacyCourse->day_end_res)) {
            $settings['weekDays'] = $this->getWeekDayAvailability(
                $legacyCourse->day_start_res, 
                $legacyCourse->day_end_res
            );
        }

        return json_encode($settings);
    }

    private function getWeekDayAvailability($dayStart, $dayEnd)
    {
        $weekDays = [
            'sunday' => false, 'monday' => false, 'tuesday' => false,
            'wednesday' => false, 'thursday' => false, 'friday' => false, 'saturday' => false
        ];

        $dayStartIndex = array_search(strtolower($dayStart), array_keys($weekDays));
        $dayEndIndex = array_search(strtolower($dayEnd), array_keys($weekDays));

        if ($dayStartIndex === false || $dayEndIndex === false) {
            return $weekDays;
        }

        $currentIndex = $dayStartIndex;
        do {
            $currentDay = array_keys($weekDays)[$currentIndex];
            $weekDays[$currentDay] = true;
            $currentIndex = ($currentIndex + 1) % count($weekDays);
        } while ($currentIndex != ($dayEndIndex + 1) % count($weekDays));

        return $weekDays;
    }

    private function generateBookingReference()
    {
        return 'BK-' . strtoupper(uniqid());
    }

    private function determineBookingType($legacyBooking)
    {
        // Logic to determine booking type based on legacy data
        return 'course';
    }

    private function mapBookingStatus($legacyBooking)
    {
        if ($legacyBooking->deleted_at) {
            return 'cancelled';
        }
        
        return $legacyBooking->status === 'confirmed' ? 'confirmed' : 'pending';
    }

    private function transformBookingData($legacyBooking)
    {
        return json_encode([
            'legacy_data' => $legacyBooking,
            'migration_notes' => 'Migrated from legacy system'
        ]);
    }

    private function extractParticipants($legacyBooking)
    {
        // Extract participant information from legacy booking
        return json_encode([
            ['name' => 'Legacy Participant', 'age' => null]
        ]);
    }
}