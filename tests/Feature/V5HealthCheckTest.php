<?php

namespace Tests\Feature;

use Tests\TestCase;

class V5HealthCheckTest extends TestCase
{
    /** @test */
    public function health_check_endpoint_returns_ok()
    {
        $this->getJson('/api/v5/health-check')
            ->assertStatus(200)
            ->assertExactJson(['status' => 'ok']);
    }
}
