<?php

namespace Tests\Feature\V5;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\Season;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;

class SeasonSelectionAuthTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear roles necesarios para las pruebas
        Role::create(['name' => 'school_admin', 'guard_name' => 'web']);
        Role::create(['name' => 'superadmin', 'guard_name' => 'web']);
    }

    /**
     * Test de login inicial sin temporada válida
     */
    public function test_initial_login_without_valid_season_returns_season_selection(): void
    {
        // Crear datos de prueba
        $school = School::factory()->create([
            'id' => 2,
            'name' => 'Test School V5',
            'slug' => 'test-school-v5',
            'is_active' => true
        ]);

        $user = User::factory()->create([
            'email' => 'admin@test-school.com',
            'password' => Hash::make('password123'),
            'is_active' => true
        ]);

        $user->assignRole('school_admin');
        $user->schools()->attach($school->id);

        // Crear temporadas disponibles
        $currentSeason = Season::factory()->create([
            'name' => 'Temporada 2025-2026',
            'school_id' => $school->id,
            'start_date' => Carbon::now()->addMonths(1),
            'end_date' => Carbon::now()->addMonths(6),
            'is_active' => true,
            'is_current' => true
        ]);

        $futureSeason = Season::factory()->create([
            'name' => 'Temporada 2026-2027',
            'school_id' => $school->id,
            'start_date' => Carbon::now()->addYear(),
            'end_date' => Carbon::now()->addYear()->addMonths(5),
            'is_active' => true,
            'is_current' => false
        ]);

        // Realizar login inicial
        $response = $this->postJson('/api/v5/auth/initial-login', [
            'email' => 'admin@test-school.com',
            'password' => 'password123',
            'school_id' => $school->id,
            'remember_me' => false
        ]);

        // Verificar respuesta
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'access_token',
                         'token_type',
                         'expires_at',
                         'requires_season_selection',
                         'available_seasons',
                         'user',
                         'school'
                     ]
                 ]);

        $data = $response->json('data');
        
        // Verificar que requiere selección de temporada
        $this->assertTrue($data['requires_season_selection']);
        $this->assertIsArray($data['available_seasons']);
        $this->assertCount(2, $data['available_seasons']);
        
        // Verificar token temporal
        $this->assertNotNull($data['access_token']);
        $this->assertEquals('Bearer', $data['token_type']);
        
        // Verificar información del usuario y escuela
        $this->assertEquals($user->email, $data['user']['email']);
        $this->assertEquals($school->name, $data['school']['name']);
    }

    /**
     * Test de login inicial con temporada válida existente
     */
    public function test_initial_login_with_valid_season_completes_automatically(): void
    {
        // Crear datos de prueba
        $school = School::factory()->create([
            'is_active' => true
        ]);

        $user = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'is_active' => true
        ]);

        $user->assignRole('school_admin');
        $user->schools()->attach($school->id);

        // Crear temporada actual válida
        $currentSeason = Season::factory()->create([
            'school_id' => $school->id,
            'start_date' => Carbon::now()->subDays(30),
            'end_date' => Carbon::now()->addDays(90),
            'is_active' => true,
            'is_current' => true
        ]);

        // Asignar usuario a la temporada
        \DB::table('user_season_roles')->insert([
            'user_id' => $user->id,
            'season_id' => $currentSeason->id,
            'role' => 'school_admin',
            'is_active' => true,
            'assigned_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Realizar login inicial
        $response = $this->postJson('/api/v5/auth/initial-login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
            'school_id' => $school->id
        ]);

        // Debería completar el login automáticamente
        $response->assertStatus(200);
        
        $data = $response->json('data');
        
        // Verificar que NO requiere selección de temporada
        $this->assertArrayNotHasKey('requires_season_selection', $data);
        $this->assertArrayHasKey('season', $data);
        $this->assertEquals($currentSeason->id, $data['season']['id']);
    }

    /**
     * Test de selección de temporada existente
     */
    public function test_select_existing_season_successfully(): void
    {
        // Crear datos de prueba
        $school = School::factory()->create(['is_active' => true]);
        $user = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'is_active' => true
        ]);

        $user->assignRole('school_admin');
        $user->schools()->attach($school->id);

        $season = Season::factory()->create([
            'school_id' => $school->id,
            'is_active' => true
        ]);

        // 1. Realizar login inicial para obtener token temporal
        $initialResponse = $this->postJson('/api/v5/auth/initial-login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
            'school_id' => $school->id
        ]);

        $token = $initialResponse->json('data.access_token');

        // 2. Seleccionar temporada
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}"
        ])->postJson('/api/v5/auth/select-season', [
            'season_id' => $season->id
        ]);

        // Verificar respuesta
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'access_token',
                         'token_type',
                         'expires_at',
                         'user',
                         'school',
                         'season'
                     ]
                 ]);

        $data = $response->json('data');
        
        // Verificar que se asignó la temporada correcta
        $this->assertEquals($season->id, $data['season']['id']);
        $this->assertEquals($season->name, $data['season']['name']);
        
        // Verificar que se creó registro en user_season_roles
        $this->assertDatabaseHas('user_season_roles', [
            'user_id' => $user->id,
            'season_id' => $season->id,
            'role' => 'school_admin',
            'is_active' => true
        ]);
    }

    /**
     * Test de creación de nueva temporada
     */
    public function test_create_new_season_successfully(): void
    {
        // Crear datos de prueba
        $school = School::factory()->create(['is_active' => true]);
        $user = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'is_active' => true
        ]);

        $user->assignRole('school_admin');
        $user->schools()->attach($school->id);

        // 1. Realizar login inicial
        $initialResponse = $this->postJson('/api/v5/auth/initial-login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
            'school_id' => $school->id
        ]);

        $token = $initialResponse->json('data.access_token');

        // 2. Crear nueva temporada
        $newSeasonData = [
            'name' => 'Nueva Temporada 2026-2027',
            'start_date' => '2026-12-01',
            'end_date' => '2027-04-30'
        ];

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}"
        ])->postJson('/api/v5/auth/select-season', [
            'create_new_season' => true,
            'new_season_data' => $newSeasonData
        ]);

        // Verificar respuesta
        $response->assertStatus(200);
        
        $data = $response->json('data');
        
        // Verificar que se creó la temporada
        $this->assertEquals($newSeasonData['name'], $data['season']['name']);
        
        // Verificar en base de datos
        $this->assertDatabaseHas('seasons', [
            'name' => $newSeasonData['name'],
            'school_id' => $school->id,
            'start_date' => $newSeasonData['start_date'],
            'end_date' => $newSeasonData['end_date'],
            'is_active' => true
        ]);

        // Verificar asignación del usuario
        $newSeason = Season::where('name', $newSeasonData['name'])->first();
        $this->assertDatabaseHas('user_season_roles', [
            'user_id' => $user->id,
            'season_id' => $newSeason->id,
            'role' => 'school_admin'
        ]);
    }

    /**
     * Test de propagación de headers X-School-ID y X-Season-ID
     */
    public function test_headers_propagation_after_season_selection(): void
    {
        // Crear datos de prueba
        $school = School::factory()->create(['is_active' => true]);
        $season = Season::factory()->create([
            'school_id' => $school->id,
            'is_active' => true
        ]);
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'is_active' => true
        ]);

        $user->assignRole('school_admin');
        $user->schools()->attach($school->id);

        // Realizar login completo
        $loginResponse = $this->postJson('/api/v5/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
            'school_id' => $school->id,
            'season_id' => $season->id
        ]);

        $token = $loginResponse->json('data.access_token');

        // Realizar request a endpoint que requiere contexto
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}"
        ])->getJson('/api/v5/dashboard');

        // Verificar que los headers de contexto están presentes
        $response->assertHeader('X-School-Context', (string) $school->id);
        $response->assertHeader('X-Season-Context', (string) $season->id);
        
        // Verificar que el contexto está disponible en la respuesta
        $response->assertStatus(200);
        $data = $response->json('data');
        
        $this->assertEquals($school->id, $data['school_id']);
        $this->assertEquals($season->id, $data['season_id']);
    }

    /**
     * Test de validación de temporada inválida
     */
    public function test_select_invalid_season_fails(): void
    {
        // Crear datos de prueba
        $school = School::factory()->create(['is_active' => true]);
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'is_active' => true
        ]);

        $user->assignRole('school_admin');
        $user->schools()->attach($school->id);

        // Login inicial
        $initialResponse = $this->postJson('/api/v5/auth/initial-login', [
            'email' => $user->email,
            'password' => 'password123',
            'school_id' => $school->id
        ]);

        $token = $initialResponse->json('data.access_token');

        // Intentar seleccionar temporada inexistente
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}"
        ])->postJson('/api/v5/auth/select-season', [
            'season_id' => 999999 // ID inexistente
        ]);

        // Verificar error de validación
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['season_id']);
    }

    /**
     * Test de error cuando se intenta seleccionar temporada sin token temporal
     */
    public function test_season_selection_fails_without_temporary_token(): void
    {
        // Crear datos de prueba y hacer login completo
        $school = School::factory()->create(['is_active' => true]);
        $season = Season::factory()->create([
            'school_id' => $school->id,
            'is_active' => true
        ]);
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'is_active' => true
        ]);

        $user->assignRole('school_admin');
        $user->schools()->attach($school->id);

        // Login completo (no temporal)
        $loginResponse = $this->postJson('/api/v5/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
            'school_id' => $school->id,
            'season_id' => $season->id
        ]);

        $token = $loginResponse->json('data.access_token');

        // Intentar seleccionar temporada con token no temporal
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}"
        ])->postJson('/api/v5/auth/select-season', [
            'season_id' => $season->id
        ]);

        // Verificar error
        $response->assertStatus(400)
                 ->assertJson([
                     'success' => false,
                     'error_code' => 'SEASON_ALREADY_SELECTED'
                 ]);
    }
}