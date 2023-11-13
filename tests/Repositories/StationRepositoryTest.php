<?php

namespace Tests\Repositories;

use App\Models\Station;
use App\Repositories\StationRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class StationRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected StationRepository $stationRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->stationRepo = app(StationRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_station()
    {
        $station = Station::factory()->make()->toArray();

        $createdStation = $this->stationRepo->create($station);

        $createdStation = $createdStation->toArray();
        $this->assertArrayHasKey('id', $createdStation);
        $this->assertNotNull($createdStation['id'], 'Created Station must have id specified');
        $this->assertNotNull(Station::find($createdStation['id']), 'Station with given id must be in DB');
        $this->assertModelData($station, $createdStation);
    }

    /**
     * @test read
     */
    public function test_read_station()
    {
        $station = Station::factory()->create();

        $dbStation = $this->stationRepo->find($station->id);

        $dbStation = $dbStation->toArray();
        $this->assertModelData($station->toArray(), $dbStation);
    }

    /**
     * @test update
     */
    public function test_update_station()
    {
        $station = Station::factory()->create();
        $fakeStation = Station::factory()->make()->toArray();

        $updatedStation = $this->stationRepo->update($fakeStation, $station->id);

        $this->assertModelData($fakeStation, $updatedStation->toArray());
        $dbStation = $this->stationRepo->find($station->id);
        $this->assertModelData($fakeStation, $dbStation->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_station()
    {
        $station = Station::factory()->create();

        $resp = $this->stationRepo->delete($station->id);

        $this->assertTrue($resp);
        $this->assertNull(Station::find($station->id), 'Station should not exist in DB');
    }
}
