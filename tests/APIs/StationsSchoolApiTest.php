<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\StationsSchool;

class StationsSchoolApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_stations_school()
    {
        $stationsSchool = StationsSchool::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/stations-schools', $stationsSchool
        );

        $this->assertApiResponse($stationsSchool);
    }

    /**
     * @test
     */
    public function test_read_stations_school()
    {
        $stationsSchool = StationsSchool::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/stations-schools/'.$stationsSchool->id
        );

        $this->assertApiResponse($stationsSchool->toArray());
    }

    /**
     * @test
     */
    public function test_update_stations_school()
    {
        $stationsSchool = StationsSchool::factory()->create();
        $editedStationsSchool = StationsSchool::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/stations-schools/'.$stationsSchool->id,
            $editedStationsSchool
        );

        $this->assertApiResponse($editedStationsSchool);
    }

    /**
     * @test
     */
    public function test_delete_stations_school()
    {
        $stationsSchool = StationsSchool::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/stations-schools/'.$stationsSchool->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/stations-schools/'.$stationsSchool->id
        );

        $this->response->assertStatus(404);
    }
}
