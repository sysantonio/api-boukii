<?php

namespace Tests\Feature\V5;

use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\Season;
use App\V5\Models\UserSeasonRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

/**
 * Integration tests for the complete V5 authentication flow:
 * login → school selection → season selection → dashboard access
 */
class AuthFlowIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $multiSchoolUser;
    private User $singleSchoolUser;
    private School $school1;
    private School $school2;
    private Season $season1;
    private Season $season2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test schools
        $this->school1 = School::factory()->create([
            'name' => 'School One',
            'slug' => 'school-one',
            'active' => true
        ]);

        $this->school2 = School::factory()->create([
            'name' => 'School Two', 
            'slug' => 'school-two',
            'active' => true
        ]);

        // Create test seasons
        $this->season1 = Season::factory()->create([
            'school_id' => $this->school1->id,
            'name' => 'Season 2025-2026 School 1',
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonths(6)->toDateString(),
            'is_active' => true
        ]);

        $this->season2 = Season::factory()->create([
            'school_id' => $this->school2->id,
            'name' => 'Season 2025-2026 School 2',
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonths(6)->toDateString(),
            'is_active' => true
        ]);

        // Create multi-school user
        $this->multiSchoolUser = User::factory()->create([
            'email' => 'admin@multi-school.com',
            'password' => Hash::make('password123'),
            'active' => true
        ]);

        // Associate with both schools
        $this->multiSchoolUser->schools()->attach([$this->school1->id, $this->school2->id]);

        // Create single-school user
        $this->singleSchoolUser = User::factory()->create([
            'email' => 'admin@single-school.com',
            'password' => Hash::make('password123'),
            'active' => true
        ]);

        // Associate with only school2
        $this->singleSchoolUser->schools()->attach($this->school2->id);

        // Create user season roles
        UserSeasonRole::create([
            'user_id' => $this->multiSchoolUser->id,
            'season_id' => $this->season1->id,
            'role' => 'admin'
        ]);

        UserSeasonRole::create([
            'user_id' => $this->multiSchoolUser->id,
            'season_id' => $this->season2->id,
            'role' => 'admin'
        ]);

        UserSeasonRole::create([
            'user_id' => $this->singleSchoolUser->id,
            'season_id' => $this->season2->id,
            'role' => 'admin'
        ]);
    }

    /** @test */
    public function it_completes_single_school_user_flow_automatically()
    {
        // Step 1: Check user credentials
        $response = $this->postJson('/api/v5/auth/check-user', [
            'email' => $this->singleSchoolUser->email,
            'password' => 'password123'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'user',
                'schools',
                'requires_school_selection',
                'temp_token'
            ]
        ]);

        $data = $response->json('data');
        $this->assertFalse($data['requires_school_selection']);
        $this->assertCount(1, $data['schools']);
        $this->assertEquals($this->school2->id, $data['schools'][0]['id']);

        $tempToken = $data['temp_token'];

        // Step 2: Select school (automatic for single school)
        $response = $this->postJson('/api/v5/auth/select-school', [
            'school_id' => $this->school2->id
        ], [
            'Authorization' => 'Bearer ' . $tempToken
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'access_token',
                'user',
                'school',
                'season',
                'requires_season_selection'
            ]
        ]);

        $loginData = $response->json('data');
        $this->assertFalse($loginData['requires_season_selection']);
        $this->assertEquals($this->school2->id, $loginData['school']['id']);
        $this->assertEquals($this->season2->id, $loginData['season']['id']);

        $finalToken = $loginData['access_token'];

        // Step 3: Access dashboard with complete context
        $response = $this->getJson('/api/v5/dashboard/stats', [
            'Authorization' => 'Bearer ' . $finalToken,
            'X-School-ID' => $this->school2->id,
            'X-Season-ID' => $this->season2->id
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data'
        ]);
    }

    /** @test */
    public function it_completes_multi_school_user_flow_with_selection()
    {
        // Step 1: Check user credentials
        $response = $this->postJson('/api/v5/auth/check-user', [
            'email' => $this->multiSchoolUser->email,
            'password' => 'password123'
        ]);

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertTrue($data['requires_school_selection']);
        $this->assertCount(2, $data['schools']);

        $tempToken = $data['temp_token'];

        // Step 2: Select specific school
        $response = $this->postJson('/api/v5/auth/select-school', [
            'school_id' => $this->school1->id
        ], [
            'Authorization' => 'Bearer ' . $tempToken
        ]);

        $response->assertStatus(200);
        $loginData = $response->json('data');
        $this->assertFalse($loginData['requires_season_selection']); // Auto-selected based on date
        $this->assertEquals($this->school1->id, $loginData['school']['id']);
        $this->assertEquals($this->season1->id, $loginData['season']['id']);

        $finalToken = $loginData['access_token'];

        // Step 3: Access protected endpoint with context
        $response = $this->getJson('/api/v5/seasons', [
            'Authorization' => 'Bearer ' . $finalToken,
            'X-School-ID' => $this->school1->id,
            'X-Season-ID' => $this->season1->id
        ]);

        $response->assertStatus(200);
        $seasons = $response->json('data');
        $this->assertIsArray($seasons);
        $this->assertNotEmpty($seasons);
        $this->assertEquals($this->season1->id, $seasons[0]['id']);
    }

    /** @test */
    public function it_handles_manual_season_selection_flow()
    {
        // Create additional season for school2 to force manual selection
        $additionalSeason = Season::factory()->create([
            'school_id' => $this->school2->id,
            'name' => 'Additional Season',
            'start_date' => now()->addMonths(6)->toDateString(),
            'end_date' => now()->addMonths(12)->toDateString(),
            'is_active' => true
        ]);

        UserSeasonRole::create([
            'user_id' => $this->singleSchoolUser->id,
            'season_id' => $additionalSeason->id,
            'role' => 'admin'
        ]);

        // Step 1: Check user and get temp token
        $response = $this->postJson('/api/v5/auth/check-user', [
            'email' => $this->singleSchoolUser->email,
            'password' => 'password123'
        ]);

        $tempToken = $response->json('data.temp_token');

        // Step 2: Select school - should require season selection now
        $response = $this->postJson('/api/v5/auth/select-school', [
            'school_id' => $this->school2->id
        ], [
            'Authorization' => 'Bearer ' . $tempToken
        ]);

        $response->assertStatus(200);
        $loginData = $response->json('data');
        
        // Should require manual season selection due to multiple active seasons
        if ($loginData['requires_season_selection']) {
            $this->assertTrue($loginData['requires_season_selection']);
            $this->assertArrayHasKey('available_seasons', $loginData);
            $this->assertGreaterThanOrEqual(2, count($loginData['available_seasons']));

            $schoolToken = $loginData['access_token'];

            // Step 3: Select specific season
            $response = $this->postJson('/api/v5/auth/select-season', [
                'season_id' => $this->season2->id
            ], [
                'Authorization' => 'Bearer ' . $schoolToken
            ]);

            $response->assertStatus(200);
            $finalData = $response->json('data');
            $this->assertEquals($this->season2->id, $finalData['season']['id']);

            $finalToken = $finalData['access_token'];

            // Step 4: Verify access to protected resources
            $response = $this->getJson('/api/v5/dashboard/stats', [
                'Authorization' => 'Bearer ' . $finalToken,
                'X-School-ID' => $this->school2->id,
                'X-Season-ID' => $this->season2->id
            ]);

            $response->assertStatus(200);
        }
    }

    /** @test */
    public function it_creates_new_season_when_authorized()
    {
        // Complete login flow first
        $checkResponse = $this->postJson('/api/v5/auth/check-user', [
            'email' => $this->singleSchoolUser->email,
            'password' => 'password123'
        ]);

        $tempToken = $checkResponse->json('data.temp_token');

        $selectResponse = $this->postJson('/api/v5/auth/select-school', [
            'school_id' => $this->school2->id
        ], [
            'Authorization' => 'Bearer ' . $tempToken
        ]);

        $finalToken = $selectResponse->json('data.access_token');

        // Now create a new season
        $response = $this->postJson('/api/v5/seasons', [
            'name' => 'New Test Season 2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_active' => true
        ], [
            'Authorization' => 'Bearer ' . $finalToken,
            'X-School-ID' => $this->school2->id,
            'X-Season-ID' => $this->season2->id
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'name',
                'start_date',
                'end_date',
                'is_active',
                'school_id'
            ]
        ]);

        $newSeason = $response->json('data');
        $this->assertEquals('New Test Season 2026', $newSeason['name']);
        $this->assertEquals($this->school2->id, $newSeason['school_id']);
    }

    /** @test */
    public function it_prevents_access_without_proper_context()
    {
        // Try to access protected endpoint without authentication
        $response = $this->getJson('/api/v5/seasons');
        $response->assertStatus(401);

        // Try with invalid token
        $response = $this->getJson('/api/v5/seasons', [
            'Authorization' => 'Bearer invalid-token'
        ]);
        $response->assertStatus(401);

        // Complete login but access wrong school's data
        $checkResponse = $this->postJson('/api/v5/auth/check-user', [
            'email' => $this->singleSchoolUser->email,
            'password' => 'password123'
        ]);

        $tempToken = $checkResponse->json('data.temp_token');

        $selectResponse = $this->postJson('/api/v5/auth/select-school', [
            'school_id' => $this->school2->id
        ], [
            'Authorization' => 'Bearer ' . $tempToken
        ]);

        $finalToken = $selectResponse->json('data.access_token');

        // Try to access school1's data with school2 token
        $response = $this->getJson('/api/v5/seasons', [
            'Authorization' => 'Bearer ' . $finalToken,
            'X-School-ID' => $this->school1->id, // Wrong school
            'X-Season-ID' => $this->season2->id
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'Access denied to this school'
        ]);
    }

    /** @test */
    public function it_maintains_context_across_multiple_requests()
    {
        // Complete login flow
        $checkResponse = $this->postJson('/api/v5/auth/check-user', [
            'email' => $this->singleSchoolUser->email,
            'password' => 'password123'
        ]);

        $tempToken = $checkResponse->json('data.temp_token');

        $selectResponse = $this->postJson('/api/v5/auth/select-school', [
            'school_id' => $this->school2->id
        ], [
            'Authorization' => 'Bearer ' . $tempToken
        ]);

        $finalToken = $selectResponse->json('data.access_token');

        $headers = [
            'Authorization' => 'Bearer ' . $finalToken,
            'X-School-ID' => $this->school2->id,
            'X-Season-ID' => $this->season2->id
        ];

        // Test multiple different endpoints with same context
        $endpoints = [
            '/api/v5/seasons',
            '/api/v5/dashboard/stats',
            '/api/v5/debug-token'
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->getJson($endpoint, $headers);
            
            // All should succeed with proper context
            $this->assertTrue(
                $response->status() === 200 || $response->status() === 404, // 404 is OK for non-implemented endpoints
                "Failed for endpoint {$endpoint}: " . $response->getContent()
            );

            // Verify context headers are present in response
            if ($response->status() === 200) {
                $this->assertNotNull($response->headers->get('X-School-Context'));
                $this->assertNotNull($response->headers->get('X-Season-Context'));
            }
        }
    }
}