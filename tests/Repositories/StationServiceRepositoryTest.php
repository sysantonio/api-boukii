<?php

namespace Tests\Repositories;

use App\Models\StationService;
use App\Repositories\StationServiceRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class StationServiceRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected StationServiceRepository $stationServiceRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->stationServiceRepo = app(StationServiceRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_station_service()
    {
        $stationService = StationService::factory()->make()->toArray();

        $createdStationService = $this->stationServiceRepo->create($stationService);

        $createdStationService = $createdStationService->toArray();
        $this->assertArrayHasKey('id', $createdStationService);
        $this->assertNotNull($createdStationService['id'], 'Created StationService must have id specified');
        $this->assertNotNull(StationService::find($createdStationService['id']), 'StationService with given id must be in DB');
        $this->assertModelData($stationService, $createdStationService);
    }

    /**
     * @test read
     */
    public function test_read_station_service()
    {
        $stationService = StationService::factory()->create();

        $dbStationService = $this->stationServiceRepo->find($stationService->id);

        $dbStationService = $dbStationService->toArray();
        $this->assertModelData($stationService->toArray(), $dbStationService);
    }

    /**
     * @test update
     */
    public function test_update_station_service()
    {
        $stationService = StationService::factory()->create();
        $fakeStationService = StationService::factory()->make()->toArray();

        $updatedStationService = $this->stationServiceRepo->update($fakeStationService, $stationService->id);

        $this->assertModelData($fakeStationService, $updatedStationService->toArray());
        $dbStationService = $this->stationServiceRepo->find($stationService->id);
        $this->assertModelData($fakeStationService, $dbStationService->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_station_service()
    {
        $stationService = StationService::factory()->create();

        $resp = $this->stationServiceRepo->delete($stationService->id);

        $this->assertTrue($resp);
        $this->assertNull(StationService::find($stationService->id), 'StationService should not exist in DB');
    }
}
