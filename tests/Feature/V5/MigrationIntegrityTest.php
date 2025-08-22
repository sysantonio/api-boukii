<?php

namespace Tests\Feature\V5;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\Season;

class MigrationIntegrityTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Verificar que las migraciones V5 no afectan datos legacy
     */
    public function test_migrations_preserve_legacy_data(): void
    {
        // 1. Crear datos legacy simulados
        $legacySchool = School::factory()->create([
            'name' => 'Legacy School',
            'is_active' => true
        ]);

        $legacyUser = User::factory()->create([
            'name' => 'Legacy User',
            'email' => 'legacy@test.com',
            'is_active' => true
        ]);

        // Asociar usuario con escuela
        $legacyUser->schools()->attach($legacySchool->id);

        // 2. Ejecutar migraciones (se ejecutan automáticamente con RefreshDatabase)
        // Verificar que las tablas existen
        $this->assertTrue(Schema::hasTable('seasons'));
        $this->assertTrue(Schema::hasTable('user_season_roles'));
        $this->assertTrue(Schema::hasTable('personal_access_tokens'));

        // 3. Verificar que los datos legacy siguen intactos
        $this->assertDatabaseHas('schools', [
            'id' => $legacySchool->id,
            'name' => 'Legacy School',
            'is_active' => true
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $legacyUser->id,
            'name' => 'Legacy User',
            'email' => 'legacy@test.com',
            'is_active' => true
        ]);

        // 4. Verificar que las nuevas columnas existen
        $this->assertTrue(Schema::hasColumn('seasons', 'is_current'));
        $this->assertTrue(Schema::hasColumn('seasons', 'is_historical'));
        $this->assertTrue(Schema::hasColumn('user_season_roles', 'is_active'));
        $this->assertTrue(Schema::hasColumn('user_season_roles', 'assigned_at'));
        $this->assertTrue(Schema::hasColumn('user_season_roles', 'deleted_at'));
        $this->assertTrue(Schema::hasColumn('personal_access_tokens', 'school_id'));
        $this->assertTrue(Schema::hasColumn('personal_access_tokens', 'season_id'));
    }

    /**
     * Verificar estructura de la tabla seasons
     */
    public function test_seasons_table_structure(): void
    {
        $expectedColumns = [
            'id', 'name', 'start_date', 'end_date', 'hour_start', 'hour_end',
            'is_active', 'is_current', 'is_historical', 'vacation_days', 
            'school_id', 'is_closed', 'closed_at', 'created_at', 'updated_at', 'deleted_at'
        ];

        foreach ($expectedColumns as $column) {
            $this->assertTrue(Schema::hasColumn('seasons', $column), "Column {$column} missing in seasons table");
        }

        // Verificar índices
        $indexes = DB::select("SHOW INDEX FROM seasons");
        $indexNames = collect($indexes)->pluck('Key_name')->unique()->toArray();
        
        $this->assertContains('idx_seasons_school_dates', $indexNames);
        $this->assertContains('idx_seasons_current_active', $indexNames);
    }

    /**
     * Verificar estructura de la tabla user_season_roles
     */
    public function test_user_season_roles_table_structure(): void
    {
        $expectedColumns = [
            'user_id', 'season_id', 'role', 'is_active',
            'assigned_at', 'assigned_by', 'created_at', 'updated_at', 'deleted_at'
        ];

        foreach ($expectedColumns as $column) {
            $this->assertTrue(Schema::hasColumn('user_season_roles', $column), "Column {$column} missing in user_season_roles table");
        }

        // Verificar foreign keys y unique constraint
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_NAME = 'user_season_roles' 
            AND TABLE_SCHEMA = DATABASE()
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        $this->assertCount(3, $foreignKeys); // user_id, season_id, assigned_by
    }

    /**
     * Verificar que se pueden crear temporadas para la escuela ID=2
     */
    public function test_can_create_seasons_for_school_2(): void
    {
        // Crear escuela con ID específico
        $school = School::factory()->create([
            'id' => 2,
            'name' => 'Test School V5',
            'slug' => 'test-school-v5',
            'is_active' => true
        ]);

        // Crear temporadas de prueba
        $currentSeason = Season::create([
            'name' => 'Temporada 2025-2026',
            'start_date' => '2025-12-01',
            'end_date' => '2026-04-30',
            'school_id' => $school->id,
            'is_active' => true,
            'is_current' => true,
            'hour_start' => '08:00:00',
            'hour_end' => '18:00:00'
        ]);

        $historicalSeason = Season::create([
            'name' => 'Temporada 2024-2025',
            'start_date' => '2024-12-01',
            'end_date' => '2025-04-30',
            'school_id' => $school->id,
            'is_active' => false,
            'is_current' => false,
            'is_historical' => true,
            'hour_start' => '08:00:00',
            'hour_end' => '18:00:00'
        ]);

        // Verificar que se guardaron correctamente
        $this->assertDatabaseHas('seasons', [
            'id' => $currentSeason->id,
            'school_id' => $school->id,
            'is_current' => true,
            'is_active' => true
        ]);

        $this->assertDatabaseHas('seasons', [
            'id' => $historicalSeason->id,
            'school_id' => $school->id,
            'is_historical' => true,
            'is_active' => false
        ]);

        // Verificar relaciones
        $this->assertEquals($school->id, $currentSeason->school->id);
        $this->assertCount(2, $school->seasons);
    }

    /**
     * Verificar que se pueden asignar usuarios a temporadas
     */
    public function test_can_assign_users_to_seasons(): void
    {
        $school = School::factory()->create(['is_active' => true]);
        $season = Season::factory()->create([
            'school_id' => $school->id,
            'is_active' => true
        ]);
        $user = User::factory()->create(['is_active' => true]);

        // Asociar usuario con escuela
        $user->schools()->attach($school->id);

        // Asignar usuario a temporada
        DB::table('user_season_roles')->insert([
            'user_id' => $user->id,
            'season_id' => $season->id,
            'role' => 'school_admin',
            'is_active' => true,
            'assigned_at' => now(),
            'assigned_by' => $user->id,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Verificar asignación
        $this->assertDatabaseHas('user_season_roles', [
            'user_id' => $user->id,
            'season_id' => $season->id,
            'role' => 'school_admin',
            'is_active' => true
        ]);

        // Verificar que se puede consultar
        $userSeasonRole = DB::table('user_season_roles')
            ->where('user_id', $user->id)
            ->where('season_id', $season->id)
            ->first();

        $this->assertNotNull($userSeasonRole);
        $this->assertEquals('school_admin', $userSeasonRole->role);
    }

    /**
     * Ensure composite key enforces uniqueness.
     */
    public function test_user_season_role_composite_key_uniqueness(): void
    {
        $school = School::factory()->create();
        $season = Season::factory()->create(['school_id' => $school->id]);
        $user = User::factory()->create();
        $user->schools()->attach($school->id);

        $data = [
            'user_id' => $user->id,
            'season_id' => $season->id,
            'role' => 'school_admin',
            'is_active' => true,
            'assigned_at' => now(),
            'assigned_by' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('user_season_roles')->insert($data);

        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('user_season_roles')->insert($data);
    }

    /**
     * Ensure foreign key constraints are enforced.
     */
    public function test_user_season_role_foreign_keys(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        DB::table('user_season_roles')->insert([
            'user_id' => 99999,
            'season_id' => 99999,
            'role' => 'school_admin',
            'is_active' => true,
            'assigned_at' => now(),
            'assigned_by' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Verificar estructura de personal_access_tokens para contexto V5
     */
    public function test_personal_access_tokens_v5_context(): void
    {
        // Verificar que las columnas de contexto existen
        $this->assertTrue(Schema::hasColumn('personal_access_tokens', 'school_id'));
        $this->assertTrue(Schema::hasColumn('personal_access_tokens', 'season_id'));
        $this->assertTrue(Schema::hasColumn('personal_access_tokens', 'context_data'));

        // Crear datos de prueba
        $school = School::factory()->create();
        $season = Season::factory()->create(['school_id' => $school->id]);
        $user = User::factory()->create();

        // Crear token con contexto
        $token = $user->createToken('test-token');
        $token->accessToken->update([
            'school_id' => $school->id,
            'season_id' => $season->id,
            'context_data' => json_encode([
                'school_slug' => $school->slug,
                'season_name' => $season->name,
                'login_at' => now()->toISOString()
            ])
        ]);

        // Verificar que se guardó correctamente
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'school_id' => $school->id,
            'season_id' => $season->id
        ]);

        // Verificar que se puede recuperar el contexto
        $savedToken = $user->tokens()->first();
        $this->assertEquals($school->id, $savedToken->school_id);
        $this->assertEquals($season->id, $savedToken->season_id);
        
        $contextData = json_decode($savedToken->context_data, true);
        $this->assertEquals($school->slug, $contextData['school_slug']);
        $this->assertEquals($season->name, $contextData['season_name']);
    }
}