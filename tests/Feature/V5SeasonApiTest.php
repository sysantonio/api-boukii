<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\V5\Models\Season;
use App\Models\School;
use App\Models\User;
use App\V5\Models\UserSeasonRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class V5SeasonApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected School $school;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->school = School::factory()->create();
        $this->user = User::factory()->create(['active' => true]);
        
        // Create user season role
        UserSeasonRole::factory()->create([
            'user_id' => $this->user->id,
            'season_id' => 1, // Will be created in tests
            'role' => 'admin'
        ]);
    }

    public function test_can_list_seasons()
    {
        $seasons = Season::factory()->count(3)->create([
            'school_id' => $this->school->id
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v5/seasons');

        $response->assertStatus(200)
                ->assertJsonCount(3)
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'name',
                        'start_date',
                        'end_date',
                        'school_id',
                        'is_active',
                        'is_closed'
                    ]
                ]);
    }

    public function test_can_create_season()
    {
        Sanctum::actingAs($this->user);

        $seasonData = [
            'name' => 'Test Season 2024',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'school_id' => $this->school->id,
            'is_active' => true,
        ];

        $response = $this->postJson('/api/v5/seasons', $seasonData);

        $response->assertStatus(201)
                ->assertJsonFragment([
                    'name' => 'Test Season 2024',
                    'school_id' => $this->school->id
                ]);

        $this->assertDatabaseHas('seasons', [
            'name' => 'Test Season 2024',
            'school_id' => $this->school->id
        ]);
    }

    public function test_can_show_season()
    {
        $season = Season::factory()->create([
            'school_id' => $this->school->id
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson("/api/v5/seasons/{$season->id}");

        $response->assertStatus(200)
                ->assertJsonFragment([
                    'id' => $season->id,
                    'name' => $season->name
                ]);
    }

    public function test_can_update_season()
    {
        $season = Season::factory()->create([
            'school_id' => $this->school->id
        ]);

        Sanctum::actingAs($this->user);

        $updateData = [
            'name' => 'Updated Season Name',
            'is_active' => false
        ];

        $response = $this->putJson("/api/v5/seasons/{$season->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonFragment([
                    'name' => 'Updated Season Name',
                    'is_active' => false
                ]);

        $this->assertDatabaseHas('seasons', [
            'id' => $season->id,
            'name' => 'Updated Season Name',
            'is_active' => false
        ]);
    }

    public function test_can_delete_season()
    {
        $season = Season::factory()->create([
            'school_id' => $this->school->id
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->deleteJson("/api/v5/seasons/{$season->id}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('seasons', [
            'id' => $season->id
        ]);
    }

    public function test_can_close_season()
    {
        $season = Season::factory()->create([
            'school_id' => $this->school->id,
            'is_active' => true,
            'is_closed' => false
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/v5/seasons/{$season->id}/close");

        $response->assertStatus(200)
                ->assertJsonFragment([
                    'is_active' => false,
                    'is_closed' => true
                ]);

        $this->assertDatabaseHas('seasons', [
            'id' => $season->id,
            'is_active' => false,
            'is_closed' => true
        ]);
    }

    public function test_can_clone_season()
    {
        $originalSeason = Season::factory()->create([
            'school_id' => $this->school->id,
            'name' => 'Original Season'
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/v5/seasons/{$originalSeason->id}/clone");

        $response->assertStatus(201)
                ->assertJsonFragment([
                    'name' => 'Original Season',
                    'school_id' => $this->school->id,
                    'is_active' => false
                ]);

        // Should have 2 seasons now
        $this->assertEquals(2, Season::count());
    }

    public function test_can_get_current_season()
    {
        $season = Season::factory()->create([
            'school_id' => $this->school->id,
            'is_active' => true
        ]);

        // Update school to have current season
        $this->school->update(['current_season_id' => $season->id]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson("/api/v5/seasons/current?school_id={$this->school->id}");

        $response->assertStatus(200)
                ->assertJsonFragment([
                    'id' => $season->id,
                    'is_active' => true
                ]);
    }

    public function test_unauthorized_access_denied()
    {
        $response = $this->getJson('/api/v5/seasons');

        $response->assertStatus(401);
    }

    public function test_validation_errors()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v5/seasons', [
            'name' => '',  // Invalid empty name
            'start_date' => 'invalid-date',  // Invalid date format
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['start_date', 'end_date', 'school_id']);
    }
}