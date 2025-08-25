<?php

namespace Tests\V5\Feature;

use Tests\TestCase;
use App\Models\School;

class RouteNamesTest extends TestCase
{
    public function test_seasons_index_route_requires_authentication(): void
    {
        $response = $this->getJson(route('v5.seasons.index'));
        $response->assertStatus(401);
    }

    public function test_login_route_returns_validation_error_without_credentials(): void
    {
        $response = $this->postJson(route('v5.auth.login'));
        $response->assertStatus(422);
    }

    public function test_schools_index_route_requires_authentication(): void
    {
        $response = $this->getJson(route('v5.schools.index'));
        $response->assertStatus(401);
    }

    public function test_schools_show_route_requires_authentication(): void
    {
        $school = School::factory()->create();

        $response = $this->getJson(route('v5.schools.show', $school));
        $response->assertStatus(401);
    }
}
