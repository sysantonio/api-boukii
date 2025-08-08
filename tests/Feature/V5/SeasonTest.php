<?php

namespace Tests\Feature\V5;

use App\V5\Models\Season;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SeasonTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private School $school;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user and school
        $this->school = School::factory()->create();
        $this->user = User::factory()->create([
            'school_id' => $this->school->id
        ]);
        
        // Mock season context middleware
        $this->withoutMiddleware([
            'season.context',
            'season.permission'
        ]);
    }

    /** @test */
    public function it_can_list_seasons()
    {
        // Create test seasons
        Season::factory()->count(3)->create([
            'school_id' => $this->school->id
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v5/seasons');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_can_create_a_season()
    {
        $seasonData = [
            'name' => 'Winter Season 2024',
            'school_id' => $this->school->id,
            'start_date' => '2024-12-01',
            'end_date' => '2025-03-31',
            'is_active' => true,
            'is_closed' => false
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v5/seasons', $seasonData);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'Winter Season 2024',
                'school_id' => $this->school->id
            ]);

        $this->assertDatabaseHas('seasons', [
            'name' => 'Winter Season 2024',
            'school_id' => $this->school->id
        ]);
    }

    /** @test */
    public function it_validates_season_creation_data()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v5/seasons', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'school_id', 'start_date', 'end_date']);
    }

    /** @test */
    public function it_validates_season_date_range()
    {
        $seasonData = [
            'name' => 'Invalid Season',
            'school_id' => $this->school->id,
            'start_date' => '2024-12-01',
            'end_date' => '2024-11-01', // End date before start date
            'is_active' => true
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v5/seasons', $seasonData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_date']);
    }

    /** @test */
    public function it_validates_overlapping_seasons()
    {
        // Create existing season
        Season::factory()->create([
            'school_id' => $this->school->id,
            'start_date' => '2024-01-01',
            'end_date' => '2024-06-30'
        ]);

        // Try to create overlapping season
        $seasonData = [
            'name' => 'Overlapping Season',
            'school_id' => $this->school->id,
            'start_date' => '2024-03-01',
            'end_date' => '2024-09-30',
            'is_active' => true
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v5/seasons', $seasonData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_date']);
    }

    /** @test */
    public function it_can_show_a_season()
    {
        $season = Season::factory()->create([
            'school_id' => $this->school->id
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v5/seasons/{$season->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $season->id,
                'name' => $season->name
            ]);
    }

    /** @test */
    public function it_returns_404_for_non_existent_season()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v5/seasons/999');

        $response->assertStatus(404)
            ->assertJson(['message' => 'Season not found']);
    }

    /** @test */
    public function it_can_update_a_season()
    {
        $season = Season::factory()->create([
            'school_id' => $this->school->id,
            'name' => 'Original Name'
        ]);

        $updateData = [
            'name' => 'Updated Season Name'
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v5/seasons/{$season->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated Season Name']);

        $this->assertDatabaseHas('seasons', [
            'id' => $season->id,
            'name' => 'Updated Season Name'
        ]);
    }

    /** @test */
    public function it_can_delete_a_season()
    {
        $season = Season::factory()->create([
            'school_id' => $this->school->id
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v5/seasons/{$season->id}");

        $response->assertStatus(200)
            ->assertJson(['deleted' => true]);

        $this->assertDatabaseMissing('seasons', [
            'id' => $season->id
        ]);
    }

    /** @test */
    public function it_can_get_current_season()
    {
        // Create inactive season
        Season::factory()->create([
            'school_id' => $this->school->id,
            'is_active' => false
        ]);

        // Create active season
        $activeSeason = Season::factory()->create([
            'school_id' => $this->school->id,
            'is_active' => true,
            'is_closed' => false
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v5/seasons/current?school_id={$this->school->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $activeSeason->id,
                'is_active' => true
            ]);
    }

    /** @test */
    public function it_can_close_a_season()
    {
        $season = Season::factory()->create([
            'school_id' => $this->school->id,
            'is_active' => true,
            'is_closed' => false
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v5/seasons/{$season->id}/close");

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

    /** @test */
    public function it_can_clone_a_season()
    {
        $originalSeason = Season::factory()->create([
            'school_id' => $this->school->id,
            'name' => 'Original Season',
            'is_active' => true
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v5/seasons/{$originalSeason->id}/clone");

        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'Original Season',
                'school_id' => $this->school->id,
                'is_active' => false // Cloned seasons should be inactive
            ]);

        // Verify we now have 2 seasons with the same name but different IDs
        $this->assertEquals(2, Season::where('name', 'Original Season')->count());
    }

    /** @test */
    public function it_returns_404_when_closing_non_existent_season()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v5/seasons/999/close');

        $response->assertStatus(404)
            ->assertJson(['message' => 'Season not found']);
    }

    /** @test */
    public function it_returns_404_when_cloning_non_existent_season()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v5/seasons/999/clone');

        $response->assertStatus(404)
            ->assertJson(['message' => 'Season not found']);
    }

    /** @test */
    public function it_requires_authentication_for_all_endpoints()
    {
        $season = Season::factory()->create();

        // Test all endpoints without authentication
        $endpoints = [
            ['GET', '/api/v5/seasons'],
            ['POST', '/api/v5/seasons'],
            ['GET', "/api/v5/seasons/{$season->id}"],
            ['PUT', "/api/v5/seasons/{$season->id}"],
            ['DELETE', "/api/v5/seasons/{$season->id}"],
            ['GET', '/api/v5/seasons/current'],
            ['POST', "/api/v5/seasons/{$season->id}/close"],
            ['POST', "/api/v5/seasons/{$season->id}/clone"],
        ];

        foreach ($endpoints as [$method, $endpoint]) {
            $response = $this->json($method, $endpoint);
            $response->assertStatus(401);
        }
    }
}