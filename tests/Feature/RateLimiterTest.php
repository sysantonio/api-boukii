<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RateLimiterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('api')->get('/test-api', fn () => 'ok');
        Route::post('/test-auth', fn () => 'ok')->middleware('throttle:auth');
        Route::post('/test-logging', fn () => 'ok')->middleware('throttle:logging');
    }

    public function test_api_rate_limiter_returns_429_and_headers()
    {
        config(['rate_limits.api' => '1,1']);

        $this->get('/test-api');
        $response = $this->get('/test-api');

        $response->assertStatus(429);
        $response->assertHeader('X-RateLimit-Limit', '1');
        $response->assertHeader('X-RateLimit-Remaining', '0');
    }

    public function test_auth_rate_limiter_returns_429_and_headers()
    {
        config(['rate_limits.auth' => '1,1']);

        $this->post('/test-auth');
        $response = $this->post('/test-auth');

        $response->assertStatus(429);
        $response->assertHeader('X-RateLimit-Limit', '1');
        $response->assertHeader('X-RateLimit-Remaining', '0');
    }

    public function test_logging_rate_limiter_returns_429_and_headers()
    {
        config(['rate_limits.logging' => '1,1']);

        $this->post('/test-logging');
        $response = $this->post('/test-logging');

        $response->assertStatus(429);
        $response->assertHeader('X-RateLimit-Limit', '1');
        $response->assertHeader('X-RateLimit-Remaining', '0');
    }
}

