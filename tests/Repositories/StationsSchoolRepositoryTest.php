<?php

namespace Tests\Repositories;

use App\Models\StationsSchool;
use App\Repositories\StationsSchoolRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class StationsSchoolRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected StationsSchoolRepository $stationsSchoolRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->stationsSchoolRepo = app(StationsSchoolRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_stations_school()
    {
        $stationsSchool = StationsSchool::factory()->make()->toArray();

        $createdStationsSchool = $this->stationsSchoolRepo->create($stationsSchool);

        $createdStationsSchool = $createdStationsSchool->toArray();
        $this->assertArrayHasKey('id', $createdStationsSchool);
        $this->assertNotNull($createdStationsSchool['id'], 'Created StationsSchool must have id specified');
        $this->assertNotNull(StationsSchool::find($createdStationsSchool['id']), 'StationsSchool with given id must be in DB');
        $this->assertModelData($stationsSchool, $createdStationsSchool);
    }

    /**
     * @test read
     */
    public function test_read_stations_school()
    {
        $stationsSchool = StationsSchool::factory()->create();

        $dbStationsSchool = $this->stationsSchoolRepo->find($stationsSchool->id);

        $dbStationsSchool = $dbStationsSchool->toArray();
        $this->assertModelData($stationsSchool->toArray(), $dbStationsSchool);
    }

    /**
     * @test update
     */
    public function test_update_stations_school()
    {
        $stationsSchool = StationsSchool::factory()->create();
        $fakeStationsSchool = StationsSchool::factory()->make()->toArray();

        $updatedStationsSchool = $this->stationsSchoolRepo->update($fakeStationsSchool, $stationsSchool->id);

        $this->assertModelData($fakeStationsSchool, $updatedStationsSchool->toArray());
        $dbStationsSchool = $this->stationsSchoolRepo->find($stationsSchool->id);
        $this->assertModelData($fakeStationsSchool, $dbStationsSchool->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_stations_school()
    {
        $stationsSchool = StationsSchool::factory()->create();

        $resp = $this->stationsSchoolRepo->delete($stationsSchool->id);

        $this->assertTrue($resp);
        $this->assertNull(StationsSchool::find($stationsSchool->id), 'StationsSchool should not exist in DB');
    }
}
