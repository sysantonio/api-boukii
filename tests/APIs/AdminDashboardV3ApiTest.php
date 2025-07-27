<?php

namespace Tests\APIs;

use App\Services\Weather\WeatherProviderInterface;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\TestCase;

class AdminDashboardV3ApiTest extends TestCase
{
    use WithoutMiddleware, DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $mock = Mockery::mock(WeatherProviderInterface::class);
        $mock->shouldReceive('get12HourForecast')
            ->andReturn([['time' => '10:00', 'temperature' => 5, 'icon' => 1]]);
        $mock->shouldReceive('get5DayForecast')
            ->andReturn([
                ['day' => '2024-01-01', 'temperature_min' => 0, 'temperature_max' => 10, 'icon' => 1]
            ]);

        $this->app->instance(WeatherProviderInterface::class, $mock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function summary_endpoint_returns_expected_structure()
    {
        $this->getJson('/api/v3/admin/dashboard/summary')
            ->assertStatus(200)
            ->assertJson(['message' => 'summary']);
    }

    /** @test */
    public function courses_endpoint_returns_expected_structure()
    {
        $this->getJson('/api/v3/admin/dashboard/courses')
            ->assertStatus(200)
            ->assertJson(['message' => 'courses']);
    }

    /** @test */
    public function sales_endpoint_returns_expected_structure()
    {
        $this->getJson('/api/v3/admin/dashboard/sales')
            ->assertStatus(200)
            ->assertJson(['message' => 'sales']);
    }

    /** @test */
    public function reservations_endpoint_returns_expected_structure()
    {
        $this->getJson('/api/v3/admin/dashboard/reservations')
            ->assertStatus(200)
            ->assertJson(['message' => 'reservations']);
    }

    /** @test */
    public function weather_endpoint_returns_expected_structure()
    {
        $this->getJson('/api/v3/admin/dashboard/weather?station_id=1')
            ->assertStatus(200)
            ->assertJson(['message' => 'weather']);
    }
}
