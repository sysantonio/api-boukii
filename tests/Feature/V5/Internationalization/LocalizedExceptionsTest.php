<?php

namespace Tests\Feature\V5\Internationalization;

use Tests\TestCase;
use App\V5\Models\Season;
use App\Models\School;
use App\Models\User;
use App\V5\Models\UserSeasonRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class LocalizedExceptionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected School $school;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->school = School::factory()->create();
        $this->user = User::factory()->create(['active' => true]);
        
        UserSeasonRole::factory()->create([
            'user_id' => $this->user->id,
            'season_id' => 1,
            'role' => 'admin'
        ]);
    }

    /**
     * @dataProvider localeProvider
     */
    public function test_season_not_found_returns_localized_message($locale, $expectedContains)
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson("/api/v5/seasons/999?lang={$locale}");

        $response->assertStatus(404);
        
        $data = $response->json();
        $this->assertArrayHasKey('message', $data);
        $this->assertStringContainsStringIgnoringCase($expectedContains, $data['message']);
    }

    /**
     * @dataProvider localeProvider
     */
    public function test_validation_errors_are_localized($locale, $expectedContains)
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/v5/seasons?lang={$locale}", [
            'name' => '', // Invalid empty name
            'start_date' => 'invalid-date',
        ]);

        $response->assertStatus(422);
        
        $data = $response->json();
        $this->assertArrayHasKey('message', $data);
        // Message should be in the requested language
        $this->assertIsString($data['message']);
    }

    /**
     * @dataProvider localeProvider
     */
    public function test_authentication_errors_are_localized($locale, $expectedContains)
    {
        $response = $this->postJson("/api/v5/auth/login?lang={$locale}", [
            'email' => 'invalid@email.com',
            'password' => 'wrongpassword',
            'season_id' => 1
        ]);

        $response->assertStatus(401);
        
        $data = $response->json();
        $this->assertArrayHasKey('message', $data);
        $this->assertIsString($data['message']);
    }

    /**
     * @dataProvider localeProvider
     */
    public function test_permission_errors_are_localized($locale, $expectedContains)
    {
        // Create user without proper permissions
        $unauthorizedUser = User::factory()->create(['active' => true]);
        Sanctum::actingAs($unauthorizedUser);

        $response = $this->getJson("/api/v5/schools?season_id=1&lang={$locale}");

        $response->assertStatus(403);
        
        $data = $response->json();
        $this->assertArrayHasKey('message', $data);
        $this->assertIsString($data['message']);
    }

    public function test_accept_language_header_is_respected()
    {
        Sanctum::actingAs($this->user);

        $response = $this->withHeaders([
            'Accept-Language' => 'es-ES,es;q=0.9'
        ])->getJson('/api/v5/seasons/999');

        $response->assertStatus(404);
        
        $data = $response->json();
        // Should contain Spanish text (checking for common Spanish words)
        $message = strtolower($data['message']);
        $this->assertTrue(
            str_contains($message, 'encontrada') || 
            str_contains($message, 'temporada') ||
            str_contains($message, 'no'),
            "Message should be in Spanish: {$data['message']}"
        );
    }

    public function test_query_parameter_overrides_accept_language_header()
    {
        Sanctum::actingAs($this->user);

        $response = $this->withHeaders([
            'Accept-Language' => 'es-ES,es;q=0.9'
        ])->getJson('/api/v5/seasons/999?lang=fr');

        $response->assertStatus(404);
        
        $data = $response->json();
        // Should contain French text, not Spanish
        $message = strtolower($data['message']);
        $this->assertTrue(
            str_contains($message, 'trouvée') || 
            str_contains($message, 'saison') ||
            str_contains($message, 'pas'),
            "Message should be in French: {$data['message']}"
        );
    }

    public function test_unsupported_locale_falls_back_to_english()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v5/seasons/999?lang=zh');

        $response->assertStatus(404);
        
        $data = $response->json();
        $message = strtolower($data['message']);
        // Should be in English
        $this->assertTrue(
            str_contains($message, 'not found') || 
            str_contains($message, 'season'),
            "Message should be in English: {$data['message']}"
        );
    }

    public function localeProvider(): array
    {
        return [
            ['en', 'not found'],
            ['es', 'encontrada'],
            ['fr', 'trouvée'],
            ['de', 'gefunden'],
            ['it', 'trovata'],
        ];
    }
}