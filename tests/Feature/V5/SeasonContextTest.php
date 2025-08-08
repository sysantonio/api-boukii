<?php

namespace Tests\Feature\V5;

use App\Models\User;
use App\Models\School;
use App\Models\Season;
use App\V5\Models\UserSeasonRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test V5 Season Context Functionality
 * Tests the fixes for "School context is required" errors
 */
class SeasonContextTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private School $school;
    private Season $season;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = User::factory()->create([
            'email' => 'test@boukii.test',
            'password' => bcrypt('password123')
        ]);

        // Create test school
        $this->school = School::factory()->create([
            'name' => 'Test School V5',
            'slug' => 'test-school-v5',
            'active' => true
        ]);

        $this->user->schools()->attach($this->school->id);

        $this->season = Season::factory()->create([
            'school_id' => $this->school->id,
            'name' => 'Test Season 2025',
            'start_date' => now()->subMonths(2)->toDateString(),
            'end_date' => now()->addMonths(4)->toDateString(),
            'is_active' => true,
            'is_current' => false // Will be auto-selected by date
        ]);

        UserSeasonRole::create([
            'user_id' => $this->user->id,
            'season_id' => $this->season->id,
            'role' => 'admin'
        ]);
    }

    /** @test */
    public function it_can_login_with_automatic_season_selection()
    {
        $response = $this->postJson('/api/v5/auth/initial-login', [
            'email' => $this->user->email,
            'password' => 'password123'
        ]);

        $response->assertStatus(200);
        
        $data = $response->json('data');
        
        // Should complete login automatically with date-based season
        $this->assertArrayHasKey('access_token', $data);
        $this->assertArrayHasKey('season', $data);
        $this->assertEquals($this->season->id, $data['season']['id']);
        $this->assertEquals($this->season->name, $data['season']['name']);
    }

    /** @test */
    public function it_can_list_seasons_after_school_selection()
    {
        // First, do initial login to get temp token
        $loginResponse = $this->postJson('/api/v5/auth/initial-login', [
            'email' => $this->user->email,
            'password' => 'password123'
        ]);

        $token = $loginResponse->json('data.access_token');

        // Now test seasons API call with proper headers
        $response = $this->getJson('/api/v5/seasons', [
            'Authorization' => 'Bearer ' . $token,
            'X-School-ID' => $this->school->id,
            'X-Season-ID' => $this->season->id
        ]);

        $response->assertStatus(200);
        
        $seasons = $response->json('data');
        $this->assertIsArray($seasons);
        $this->assertNotEmpty($seasons);
        $this->assertEquals($this->season->id, $seasons[0]['id']);
    }

    /** @test */
    public function it_properly_sets_context_data_in_tokens()
    {
        // Login to get a token
        $response = $this->postJson('/api/v5/auth/initial-login', [
            'email' => $this->user->email,
            'password' => 'password123'
        ]);

        $token = $response->json('data.access_token');
        $this->assertNotNull($token);

        // Test debug endpoint to verify token context
        $debugResponse = $this->postJson('/api/v5/debug-raw-token', [], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $debugResponse->assertStatus(200);
        $debugData = $debugResponse->json('data');
        
        $this->assertTrue($debugData['authenticated']);
        $this->assertTrue($debugData['has_school_context']);
        $this->assertEquals($this->school->id, $debugData['school_id_in_context']);
    }

    /** @test */
    public function school_context_middleware_works_correctly()
    {
        // Login to get a token
        $loginResponse = $this->postJson('/api/v5/auth/initial-login', [
            'email' => $this->user->email,
            'password' => 'password123'
        ]);

        $token = $loginResponse->json('data.access_token');

        // Test school context debug endpoint
        $response = $this->postJson('/api/v5/debug-token', [], [
            'Authorization' => 'Bearer ' . $token,
            'X-School-ID' => $this->school->id,
            'X-Season-ID' => $this->season->id,
        ]);

        $response->assertStatus(200);
        $debugData = $response->json('data');

        $this->assertEquals($this->school->id, $debugData['school_id_from_context']);
        $this->assertTrue($debugData['middleware_applied']);
    }

    /** @test */
    public function seasons_api_no_longer_returns_school_context_required_error()
    {
        // This test specifically addresses the reported "School context is required" error
        
        // Login to get proper token with context
        $loginResponse = $this->postJson('/api/v5/auth/initial-login', [
            'email' => $this->user->email,
            'password' => 'password123'
        ]);

        $token = $loginResponse->json('data.access_token');

        // Call seasons API - should NOT return "School context is required"
        $response = $this->getJson('/api/v5/seasons', [
            'Authorization' => 'Bearer ' . $token,
            'X-School-ID' => $this->school->id,
            'X-Season-ID' => $this->season->id,
        ]);

        // Should be successful, not forbidden
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'start_date',
                    'end_date',
                    'is_active'
                ]
            ]
        ]);

        // Specifically assert it's NOT the context error
        $this->assertNotEquals('School context is required', $response->json('message'));
    }
}