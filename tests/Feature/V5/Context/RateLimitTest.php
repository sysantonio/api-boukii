<?php

namespace Tests\Feature\V5\Context;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    private string $token;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;

        RateLimiter::clear('context:' . $this->user->id);
    }

    public function test_allows_30_requests_per_minute()
    {
        for ($i = 0; $i < 30; $i++) {
            $response = $this->getJson('/api/v5/context', [
                'Authorization' => 'Bearer ' . $this->token,
            ]);

            $response->assertStatus(200);
        }
    }

    public function test_blocks_after_30_requests_per_minute()
    {
        for ($i = 0; $i < 30; $i++) {
            $this->getJson('/api/v5/context', [
                'Authorization' => 'Bearer ' . $this->token,
            ]);
        }

        $response = $this->getJson('/api/v5/context', [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(429);
    }
}

