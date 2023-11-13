<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\StationService;

class StationServiceApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_station_service()
    {
        $stationService = StationService::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/station-services', $stationService
        );

        $this->assertApiResponse($stationService);
    }

    /**
     * @test
     */
    public function test_read_station_service()
    {
        $stationService = StationService::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/station-services/'.$stationService->id
        );

        $this->assertApiResponse($stationService->toArray());
    }

    /**
     * @test
     */
    public function test_update_station_service()
    {
        $stationService = StationService::factory()->create();
        $editedStationService = StationService::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/station-services/'.$stationService->id,
            $editedStationService
        );

        $this->assertApiResponse($editedStationService);
    }

    /**
     * @test
     */
    public function test_delete_station_service()
    {
        $stationService = StationService::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/station-services/'.$stationService->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/station-services/'.$stationService->id
        );

        $this->response->assertStatus(404);
    }
}
