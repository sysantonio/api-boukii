<?php

namespace Tests\V5\Feature;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ClientLogTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::dropIfExists('client_logs');
        Schema::create('client_logs', function (Blueprint $table) {
            $table->id();
            $table->string('level', 20);
            $table->text('message');
            $table->json('context')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index('created_at');
            $table->index('level');
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('client_logs');
        parent::tearDown();
    }

    public function test_creates_log_and_returns_accepted(): void
    {
        $response = $this->postJson('/api/v5/logs', [
            'level' => 'info',
            'message' => 'hello',
            'context' => ['foo' => 'bar'],
            'clientTime' => now()->toISOString(),
        ]);

        $response->assertStatus(202);
        $this->assertArrayHasKey('id', $response->json());
        $this->assertDatabaseCount('client_logs', 1);
    }

    public function test_rate_limiting_returns_429(): void
    {
        \Illuminate\Support\Facades\Cache::flush();
        config(['rate_limits.logging' => '1,1']);

        $payload = [
            'level' => 'info',
            'message' => 'hi',
            'context' => [],
            'clientTime' => now()->toISOString(),
        ];

        $this->postJson('/api/v5/logs', $payload)->assertStatus(202);
        $this->postJson('/api/v5/logs', $payload)->assertStatus(429);
    }
}
