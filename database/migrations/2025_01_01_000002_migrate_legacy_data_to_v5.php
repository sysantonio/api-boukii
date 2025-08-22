<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Crear tablas V5 manteniendo compatibilidad con V4
        
        // Tabla mejorada para clientes (ya implementada en client-detail)
        Schema::create('clients_v5', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('email')->nullable()->index();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('birth_date')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('telephone', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('cp', 10)->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('country', 2)->default('ES');
            $table->string('image')->nullable();
            $table->json('preferences')->nullable(); // Nuevas preferencias
            $table->json('emergency_contacts')->nullable(); // Contactos de emergencia
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
            
            // Índices para performance
            $table->index(['school_id', 'is_active']);
            $table->index(['email', 'school_id']);
            $table->fullText(['first_name', 'last_name', 'email']);
        });

        // Tabla mejorada para utilizadores
        Schema::create('client_utilizadores_v5', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients_v5')->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('birth_date')->nullable();
            $table->string('image')->nullable();
            $table->json('medical_info')->nullable(); // Información médica
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['client_id', 'is_active']);
            $table->fullText(['first_name', 'last_name']);
        });

        // Tabla mejorada para deportes de clientes
        Schema::create('client_sports_v5', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients_v5')->cascadeOnDelete();
            $table->enum('person_type', ['client', 'utilizador']);
            $table->unsignedBigInteger('person_id'); // ID del client o utilizador
            $table->foreignId('sport_id')->constrained('sports')->cascadeOnDelete();
            $table->foreignId('degree_id')->nullable()->constrained('degrees')->nullOnDelete();
            $table->json('progress_data')->nullable(); // Progreso y estadísticas
            $table->decimal('skill_level', 3, 1)->nullable(); // Nivel de 1.0 a 10.0
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
            
            $table->index(['client_id', 'person_type', 'person_id']);
            $table->index(['sport_id', 'degree_id']);
            $table->unique(['person_type', 'person_id', 'sport_id']);
        });

        // Tabla mejorada para observaciones
        Schema::create('client_observations_v5', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients_v5')->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('content');
            $table->enum('type', ['general', 'medical', 'progress', 'incident'])->default('general');
            $table->boolean('is_private')->default(false); // Solo visible para staff
            $table->json('tags')->nullable(); // Tags para categorización
            $table->timestamps();
            
            $table->index(['client_id', 'type']);
            $table->index(['author_id', 'created_at']);
            $table->fullText(['title', 'content']);
        });

        // Tabla para historial de reservas expandido
        Schema::create('booking_history_v5', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients_v5')->cascadeOnDelete();
            $table->enum('type', ['booking', 'course']);
            $table->enum('status', ['completed', 'active', 'confirmed', 'cancelled', 'pending']);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('service')->nullable();
            $table->string('instructor')->nullable();
            $table->timestamp('event_date');
            $table->decimal('amount', 10, 2)->nullable();
            $table->decimal('duration_hours', 4, 2)->nullable();
            $table->json('metadata')->nullable(); // Datos adicionales
            $table->timestamps();
            
            $table->index(['client_id', 'type', 'status']);
            $table->index(['event_date', 'status']);
        });

        // Script para migrar datos existentes
        $this->migrateExistingData();
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_history_v5');
        Schema::dropIfExists('client_observations_v5');
        Schema::dropIfExists('client_sports_v5');
        Schema::dropIfExists('client_utilizadores_v5');
        Schema::dropIfExists('clients_v5');
    }

    private function migrateExistingData(): void
    {
        DB::transaction(function () {
            // Migrar clientes
            $this->migrateClients();
            
            // Migrar utilizadores
            $this->migrateUtilizadores();
            
            // Migrar deportes de clientes
            $this->migrateClientSports();
            
            // Migrar observaciones
            $this->migrateObservations();
            
            // Migrar historial de reservas
            $this->migrateBookingHistory();
        });
    }

    private function migrateClients(): void
    {
        if (!Schema::hasTable('clients')) return;

        $clients = DB::table('clients')->get();
        
        foreach ($clients->chunk(100) as $clientChunk) {
            $insertData = [];
            
            foreach ($clientChunk as $client) {
                $insertData[] = [
                    'id' => $client->id,
                    'school_id' => $client->school_id,
                    'email' => $client->email,
                    'first_name' => $client->first_name,
                    'last_name' => $client->last_name,
                    'birth_date' => $client->birth_date,
                    'phone' => $client->phone,
                    'telephone' => $client->telephone ?? null,
                    'address' => $client->address,
                    'cp' => $client->cp,
                    'city' => $client->city,
                    'province' => $client->province,
                    'country' => $client->country ?? 'ES',
                    'image' => $client->image,
                    'preferences' => null,
                    'emergency_contacts' => null,
                    'is_active' => $client->is_active ?? true,
                    'last_activity_at' => $client->updated_at,
                    'created_at' => $client->created_at,
                    'updated_at' => $client->updated_at,
                ];
            }
            
            if (!empty($insertData)) {
                DB::table('clients_v5')->insert($insertData);
            }
        }
        
        echo "Migrated " . $clients->count() . " clients\n";
    }

    private function migrateUtilizadores(): void
    {
        if (!Schema::hasTable('utilizadores')) return;

        $utilizadores = DB::table('utilizadores')
            ->join('clients_v5', 'utilizadores.client_id', '=', 'clients_v5.id')
            ->select('utilizadores.*')
            ->get();
        
        foreach ($utilizadores->chunk(100) as $utilizadorChunk) {
            $insertData = [];
            
            foreach ($utilizadorChunk as $utilizador) {
                $insertData[] = [
                    'id' => $utilizador->id,
                    'client_id' => $utilizador->client_id,
                    'first_name' => $utilizador->first_name,
                    'last_name' => $utilizador->last_name,
                    'birth_date' => $utilizador->birth_date,
                    'image' => $utilizador->image,
                    'medical_info' => null,
                    'is_active' => $utilizador->is_active ?? true,
                    'created_at' => $utilizador->created_at,
                    'updated_at' => $utilizador->updated_at,
                ];
            }
            
            if (!empty($insertData)) {
                DB::table('client_utilizadores_v5')->insert($insertData);
            }
        }
        
        echo "Migrated " . $utilizadores->count() . " utilizadores\n";
    }

    private function migrateClientSports(): void
    {
        if (!Schema::hasTable('client_sports')) return;

        $clientSports = DB::table('client_sports')
            ->join('clients_v5', 'client_sports.client_id', '=', 'clients_v5.id')
            ->select('client_sports.*')
            ->get();
        
        foreach ($clientSports->chunk(100) as $sportChunk) {
            $insertData = [];
            
            foreach ($sportChunk as $sport) {
                // Determinar person_type y person_id
                $personType = isset($sport->utilizador_id) && $sport->utilizador_id ? 'utilizador' : 'client';
                $personId = $personType === 'utilizador' ? $sport->utilizador_id : $sport->client_id;
                
                $insertData[] = [
                    'id' => $sport->id,
                    'client_id' => $sport->client_id,
                    'person_type' => $personType,
                    'person_id' => $personId,
                    'sport_id' => $sport->sport_id,
                    'degree_id' => $sport->degree_id,
                    'progress_data' => null,
                    'skill_level' => null,
                    'last_activity_at' => $sport->updated_at,
                    'created_at' => $sport->created_at,
                    'updated_at' => $sport->updated_at,
                ];
            }
            
            if (!empty($insertData)) {
                DB::table('client_sports_v5')->insert($insertData);
            }
        }
        
        echo "Migrated " . $clientSports->count() . " client sports\n";
    }

    private function migrateObservations(): void
    {
        if (!Schema::hasTable('client_observations')) return;

        $observations = DB::table('client_observations')
            ->join('clients_v5', 'client_observations.client_id', '=', 'clients_v5.id')
            ->select('client_observations.*')
            ->get();
        
        foreach ($observations->chunk(100) as $observationChunk) {
            $insertData = [];
            
            foreach ($observationChunk as $observation) {
                $insertData[] = [
                    'id' => $observation->id,
                    'client_id' => $observation->client_id,
                    'author_id' => $observation->user_id ?? 1, // Fallback to system user
                    'title' => $observation->title,
                    'content' => $observation->content,
                    'type' => 'general',
                    'is_private' => false,
                    'tags' => null,
                    'created_at' => $observation->created_at,
                    'updated_at' => $observation->updated_at,
                ];
            }
            
            if (!empty($insertData)) {
                DB::table('client_observations_v5')->insert($insertData);
            }
        }
        
        echo "Migrated " . $observations->count() . " observations\n";
    }

    private function migrateBookingHistory(): void
    {
        // Migrar desde diferentes fuentes de historial
        $this->migrateFromBookings();
        $this->migrateFromCourses();
    }

    private function migrateFromBookings(): void
    {
        if (!Schema::hasTable('bookings')) return;

        $bookings = DB::table('bookings')
            ->join('clients_v5', 'bookings.client_id', '=', 'clients_v5.id')
            ->leftJoin('courses', 'bookings.course_id', '=', 'courses.id')
            ->leftJoin('users as instructors', 'courses.instructor_id', '=', 'instructors.id')
            ->select(
                'bookings.*',
                'courses.name as course_name',
                'courses.sport_name',
                DB::raw("CONCAT(instructors.first_name, ' ', instructors.last_name) as instructor_name")
            )
            ->get();
        
        foreach ($bookings->chunk(100) as $bookingChunk) {
            $insertData = [];
            
            foreach ($bookingChunk as $booking) {
                $insertData[] = [
                    'client_id' => $booking->client_id,
                    'type' => 'booking',
                    'status' => $this->mapStatus($booking->status),
                    'title' => $booking->course_name ?? 'Reserva',
                    'description' => $booking->notes ?? null,
                    'service' => $booking->sport_name,
                    'instructor' => $booking->instructor_name,
                    'event_date' => $booking->start_date,
                    'amount' => $booking->total_amount,
                    'duration_hours' => $booking->duration / 60, // Convertir minutos a horas
                    'metadata' => json_encode([
                        'booking_id' => $booking->id,
                        'legacy_data' => true
                    ]),
                    'created_at' => $booking->created_at,
                    'updated_at' => $booking->updated_at,
                ];
            }
            
            if (!empty($insertData)) {
                DB::table('booking_history_v5')->insert($insertData);
            }
        }
        
        echo "Migrated " . $bookings->count() . " bookings to history\n";
    }

    private function migrateFromCourses(): void
    {
        if (!Schema::hasTable('course_participants')) return;

        $courseParticipants = DB::table('course_participants')
            ->join('courses', 'course_participants.course_id', '=', 'courses.id')
            ->join('clients_v5', 'course_participants.client_id', '=', 'clients_v5.id')
            ->leftJoin('users as instructors', 'courses.instructor_id', '=', 'instructors.id')
            ->select(
                'course_participants.*',
                'courses.name as course_name',
                'courses.description',
                'courses.sport_name',
                'courses.start_date',
                'courses.total_hours',
                'courses.price',
                DB::raw("CONCAT(instructors.first_name, ' ', instructors.last_name) as instructor_name")
            )
            ->get();
        
        foreach ($courseParticipants->chunk(100) as $participantChunk) {
            $insertData = [];
            
            foreach ($participantChunk as $participant) {
                $insertData[] = [
                    'client_id' => $participant->client_id,
                    'type' => 'course',
                    'status' => $this->mapCourseStatus($participant->status),
                    'title' => $participant->course_name,
                    'description' => $participant->description,
                    'service' => $participant->sport_name,
                    'instructor' => $participant->instructor_name,
                    'event_date' => $participant->start_date,
                    'amount' => $participant->price,
                    'duration_hours' => $participant->total_hours,
                    'metadata' => json_encode([
                        'course_id' => $participant->course_id,
                        'participant_id' => $participant->id,
                        'legacy_data' => true
                    ]),
                    'created_at' => $participant->created_at,
                    'updated_at' => $participant->updated_at,
                ];
            }
            
            if (!empty($insertData)) {
                DB::table('booking_history_v5')->insert($insertData);
            }
        }
        
        echo "Migrated " . $courseParticipants->count() . " course participants to history\n";
    }

    private function mapStatus(string $oldStatus): string
    {
        $statusMap = [
            'confirmed' => 'confirmed',
            'completed' => 'completed',
            'cancelled' => 'cancelled',
            'pending' => 'pending',
            'active' => 'active',
        ];

        return $statusMap[$oldStatus] ?? 'pending';
    }

    private function mapCourseStatus(string $oldStatus): string
    {
        $statusMap = [
            'enrolled' => 'active',
            'completed' => 'completed',
            'cancelled' => 'cancelled',
            'pending' => 'pending',
        ];

        return $statusMap[$oldStatus] ?? 'active';
    }
};