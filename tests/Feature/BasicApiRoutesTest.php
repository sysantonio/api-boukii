<?php

namespace Tests\Feature;

use Tests\TestCase;

class BasicApiRoutesTest extends TestCase
{
    /** @test */
    public function weather_forecast_endpoints_return_success()
    {
        $this->getJson('/api/weather/forecast/12h')
            ->assertStatus(200)
            ->assertJsonStructure(['success','data' => ['location','forecast']]);

        $this->getJson('/api/weather/forecast/5d')
            ->assertStatus(200)
            ->assertJsonStructure(['success','data' => ['location','forecast']]);
    }

    /** @test */
    public function ski_conditions_endpoint_returns_success()
    {
        $this->getJson('/api/external/ski-conditions')
            ->assertStatus(200)
            ->assertJsonStructure(['success','data' => ['condition','snow_depth_cm','last_updated']]);
    }

    /** @test */
    public function system_validation_and_health_endpoints_work()
    {
        $this->getJson('/api/system/validate')
            ->assertStatus(200)
            ->assertJsonStructure(['success','data'=>['system']]);

        $this->getJson('/api/system/health')
            ->assertStatus(200)
            ->assertJson(['status' => 'ok']);
    }
}
