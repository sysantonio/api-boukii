<?php

namespace Tests\Feature\API\V5;

use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\Season;
use App\Models\PersonalAccessToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

/**
 * Class SeasonV5ControllerTest
 * 
 * Feature tests for SeasonV5Controller API endpoints.
 * Tests all CRUD operations and business logic.
 * 
 * @package Tests\Feature\API\V5
 */
class SeasonV5ControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private School $school;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test school
        $this->school = School::create([
            'name' => 'Test School V5',
            'slug' => 'test-school-v5',
            'active' => true,
        ]);

        // Create test user with school_admin role
        $this->user = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@test-school-v5.com',
            'password' => Hash::make('password123'),
            'type' => 'admin',
        ]);

        // Attach user to school with admin role
        $this->user->schools()->attach($this->school->id, [
            'role' => 'school_admin',
            'active' => true,
        ]);

        // Create authentication token with school context
        $tokenModel = $this->user->createToken('test-token', ['*'], now()->addHours(24));
        $tokenModel->accessToken->update([
            'context_data' => [
                'school_id' => $this->school->id,
                'user_id' => $this->user->id,
            ]
        ]);
        
        $this->token = $tokenModel->plainTextToken;
    }

    // ==================== INDEX TESTS ====================

    /** @test */
    public function it_can_get_seasons_for_school(): void
    {
        // Create test seasons
        $season1 = Season::create([
            'name' => 'Winter 2024',
            'start_date' => Carbon::now()->subMonth(),
            'end_date' => Carbon::now()->addMonths(2),
            'school_id' => $this->school->id,
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);

        $season2 = Season::create([
            'name' => 'Summer 2024',
            'start_date' => Carbon::now()->addMonths(3),
            'end_date' => Carbon::now()->addMonths(6),
            'school_id' => $this->school->id,
            'is_active' => false,
            'created_by' => $this->user->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson('/api/v5/seasons');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'start_date',
                        'end_date',
                        'is_active',
                        'is_closed',
                        'is_historical',
                        'status',
                        'duration_days',
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function it_requires_authentication_to_get_seasons(): void
    {
        $response = $this->getJson('/api/v5/seasons');

        $response->assertStatus(401);
    }

    // ==================== STORE TESTS ====================

    /** @test */
    public function it_can_create_a_new_season(): void
    {
        $seasonData = [
            'name' => 'New Test Season',
            'description' => 'A test season description',
            'start_date' => Carbon::now()->addDays(30)->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(120)->format('Y-m-d'),
            'is_active' => false,
            'max_capacity' => 100,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/v5/seasons', $seasonData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'description',
                    'start_date',
                    'end_date',
                    'is_active',
                    'max_capacity',
                ]
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', $seasonData['name'])
            ->assertJsonPath('data.description', $seasonData['description']);

        // Verify season was created in database
        $this->assertDatabaseHas('seasons', [
            'name' => $seasonData['name'],
            'school_id' => $this->school->id,
            'created_by' => $this->user->id,
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_season(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/v5/seasons', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'start_date', 'end_date']);
    }

    /** @test */
    public function it_prevents_creating_seasons_with_overlapping_dates(): void
    {
        // Create existing season
        Season::create([
            'name' => 'Existing Season',
            'start_date' => Carbon::now()->addDays(10),
            'end_date' => Carbon::now()->addDays(50),
            'school_id' => $this->school->id,
            'created_by' => $this->user->id,
        ]);

        // Try to create overlapping season
        $seasonData = [
            'name' => 'Overlapping Season',
            'start_date' => Carbon::now()->addDays(20)->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(60)->format('Y-m-d'),
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/v5/seasons', $seasonData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_date']);
    }

    /** @test */
    public function it_can_create_active_season_and_deactivates_others(): void
    {
        // Create existing active season
        $existingSeason = Season::create([
            'name' => 'Currently Active Season',
            'start_date' => Carbon::now()->subDays(10),
            'end_date' => Carbon::now()->addDays(50),
            'school_id' => $this->school->id,
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);

        // Create new active season
        $seasonData = [
            'name' => 'New Active Season',
            'start_date' => Carbon::now()->addDays(60)->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(150)->format('Y-m-d'),
            'is_active' => true,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/v5/seasons', $seasonData);

        $response->assertStatus(201)
            ->assertJsonPath('data.is_active', true);

        // Verify existing season was deactivated
        $existingSeason->refresh();
        $this->assertFalse($existingSeason->is_active);
    }

    // ==================== SHOW TESTS ====================

    /** @test */
    public function it_can_get_specific_season(): void
    {
        $season = Season::create([
            'name' => 'Test Season',
            'start_date' => Carbon::now()->addDays(10),
            'end_date' => Carbon::now()->addDays(50),
            'school_id' => $this->school->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson("/api/v5/seasons/{$season->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $season->id)
            ->assertJsonPath('data.name', $season->name);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_season(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson('/api/v5/seasons/99999');

        $response->assertStatus(404)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error_code', 'SEASON_NOT_FOUND');
    }

    /** @test */
    public function it_prevents_access_to_seasons_from_other_schools(): void
    {
        // Create another school and season
        $otherSchool = School::create([
            'name' => 'Other School',
            'slug' => 'other-school',
            'active' => true,
        ]);

        $otherSeason = Season::create([
            'name' => 'Other School Season',
            'start_date' => Carbon::now()->addDays(10),
            'end_date' => Carbon::now()->addDays(50),
            'school_id' => $otherSchool->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson("/api/v5/seasons/{$otherSeason->id}");

        $response->assertStatus(404)
            ->assertJsonPath('error_code', 'SEASON_NOT_FOUND');
    }

    // ==================== UPDATE TESTS ====================

    /** @test */
    public function it_can_update_season(): void
    {
        $season = Season::create([
            'name' => 'Original Name',
            'description' => 'Original description',
            'start_date' => Carbon::now()->addDays(10),
            'end_date' => Carbon::now()->addDays(50),
            'school_id' => $this->school->id,
            'created_by' => $this->user->id,
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'description' => 'Updated description',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->putJson("/api/v5/seasons/{$season->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.description', 'Updated description');

        // Verify database was updated
        $season->refresh();
        $this->assertEquals('Updated Name', $season->name);
        $this->assertEquals('Updated description', $season->description);
    }

    /** @test */
    public function it_prevents_updating_closed_seasons(): void
    {
        $season = Season::create([
            'name' => 'Closed Season',
            'start_date' => Carbon::now()->subDays(50),
            'end_date' => Carbon::now()->subDays(10),
            'school_id' => $this->school->id,
            'is_closed' => true,
            'created_by' => $this->user->id,
        ]);

        $updateData = [
            'start_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->putJson("/api/v5/seasons/{$season->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_date']);
    }

    // ==================== CLOSE TESTS ====================

    /** @test */
    public function it_can_close_season(): void
    {
        $season = Season::create([
            'name' => 'Season to Close',
            'start_date' => Carbon::now()->subDays(50),
            'end_date' => Carbon::now()->subDays(10),
            'school_id' => $this->school->id,
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson("/api/v5/seasons/{$season->id}/close");

        $response->assertStatus(200)
            ->assertJsonPath('data.is_closed', true)
            ->assertJsonPath('data.is_active', false);

        // Verify database was updated
        $season->refresh();
        $this->assertTrue($season->is_closed);
        $this->assertFalse($season->is_active);
        $this->assertNotNull($season->closed_at);
        $this->assertEquals($this->user->id, $season->closed_by);
    }

    /** @test */
    public function it_prevents_closing_already_closed_season(): void
    {
        $season = Season::create([
            'name' => 'Already Closed Season',
            'start_date' => Carbon::now()->subDays(50),
            'end_date' => Carbon::now()->subDays(10),
            'school_id' => $this->school->id,
            'is_closed' => true,
            'created_by' => $this->user->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson("/api/v5/seasons/{$season->id}/close");

        $response->assertStatus(409)
            ->assertJsonPath('error_code', 'SEASON_ALREADY_CLOSED');
    }

    // ==================== DELETE TESTS ====================

    /** @test */
    public function it_can_delete_season_without_data(): void
    {
        $season = Season::create([
            'name' => 'Season to Delete',
            'start_date' => Carbon::now()->addDays(10),
            'end_date' => Carbon::now()->addDays(50),
            'school_id' => $this->school->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->deleteJson("/api/v5/seasons/{$season->id}");

        $response->assertStatus(204);

        // Verify season was soft deleted
        $this->assertSoftDeleted('seasons', ['id' => $season->id]);
    }

    /** @test */
    public function it_prevents_deleting_closed_seasons(): void
    {
        $season = Season::create([
            'name' => 'Closed Season',
            'start_date' => Carbon::now()->subDays(50),
            'end_date' => Carbon::now()->subDays(10),
            'school_id' => $this->school->id,
            'is_closed' => true,
            'created_by' => $this->user->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->deleteJson("/api/v5/seasons/{$season->id}");

        $response->assertStatus(409)
            ->assertJsonPath('error_code', 'SEASON_HAS_DATA');
    }

    // ==================== CURRENT SEASON TESTS ====================

    /** @test */
    public function it_can_get_current_active_season(): void
    {
        // Create active season
        $activeSeason = Season::create([
            'name' => 'Active Season',
            'start_date' => Carbon::now()->subDays(10),
            'end_date' => Carbon::now()->addDays(50),
            'school_id' => $this->school->id,
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);

        // Create non-active season
        Season::create([
            'name' => 'Non-Active Season',
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(45),
            'school_id' => $this->school->id,
            'is_active' => false,
            'created_by' => $this->user->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson('/api/v5/seasons/current');

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $activeSeason->id)
            ->assertJsonPath('data.is_active', true);
    }

    /** @test */
    public function it_returns_404_when_no_current_season_exists(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson('/api/v5/seasons/current');

        $response->assertStatus(404)
            ->assertJsonPath('error_code', 'NO_ACTIVE_SEASON');
    }

    // ==================== AUTHORIZATION TESTS ====================

    /** @test */
    public function it_prevents_unauthorized_users_from_managing_seasons(): void
    {
        // Create user without permissions
        $unauthorizedUser = User::create([
            'name' => 'Unauthorized User',
            'email' => 'unauthorized@test.com',
            'password' => Hash::make('password123'),
            'type' => 'client',
        ]);

        // Create token without school context
        $unauthorizedToken = $unauthorizedUser->createToken('unauthorized-token')->plainTextToken;

        $seasonData = [
            'name' => 'Unauthorized Season',
            'start_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(50)->format('Y-m-d'),
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $unauthorizedToken,
            'Accept' => 'application/json',
        ])->postJson('/api/v5/seasons', $seasonData);

        $response->assertStatus(403);
    }
}