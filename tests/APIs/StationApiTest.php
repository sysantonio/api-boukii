<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\Station;

class StationApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_station()
    {
        $station = Station::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/stations', $station
        );

        $this->assertApiResponse($station);
    }

    /**
     * @test
     */
    public function test_read_station()
    {
        $station = Station::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/stations/'.$station->id
        );

        $this->assertApiResponse($station->toArray());
    }

    /**
     * @test
     */
    public function test_update_station()
    {
        $station = Station::factory()->create();
        $editedStation = Station::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/stations/'.$station->id,
            $editedStation
        );

        $this->assertApiResponse($editedStation);
    }

    /**
     * @test
     */
    public function test_delete_station()
    {
        $station = Station::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/stations/'.$station->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/stations/'.$station->id
        );

        $this->response->assertStatus(404);
    }
}
