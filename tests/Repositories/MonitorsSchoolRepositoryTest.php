<?php

namespace Tests\Repositories;

use App\Models\MonitorsSchool;
use App\Repositories\MonitorsSchoolRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class MonitorsSchoolRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected MonitorsSchoolRepository $monitorsSchoolRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->monitorsSchoolRepo = app(MonitorsSchoolRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_monitors_school()
    {
        $monitorsSchool = MonitorsSchool::factory()->make()->toArray();

        $createdMonitorsSchool = $this->monitorsSchoolRepo->create($monitorsSchool);

        $createdMonitorsSchool = $createdMonitorsSchool->toArray();
        $this->assertArrayHasKey('id', $createdMonitorsSchool);
        $this->assertNotNull($createdMonitorsSchool['id'], 'Created MonitorsSchool must have id specified');
        $this->assertNotNull(MonitorsSchool::find($createdMonitorsSchool['id']), 'MonitorsSchool with given id must be in DB');
        $this->assertModelData($monitorsSchool, $createdMonitorsSchool);
    }

    /**
     * @test read
     */
    public function test_read_monitors_school()
    {
        $monitorsSchool = MonitorsSchool::factory()->create();

        $dbMonitorsSchool = $this->monitorsSchoolRepo->find($monitorsSchool->id);

        $dbMonitorsSchool = $dbMonitorsSchool->toArray();
        $this->assertModelData($monitorsSchool->toArray(), $dbMonitorsSchool);
    }

    /**
     * @test update
     */
    public function test_update_monitors_school()
    {
        $monitorsSchool = MonitorsSchool::factory()->create();
        $fakeMonitorsSchool = MonitorsSchool::factory()->make()->toArray();

        $updatedMonitorsSchool = $this->monitorsSchoolRepo->update($fakeMonitorsSchool, $monitorsSchool->id);

        $this->assertModelData($fakeMonitorsSchool, $updatedMonitorsSchool->toArray());
        $dbMonitorsSchool = $this->monitorsSchoolRepo->find($monitorsSchool->id);
        $this->assertModelData($fakeMonitorsSchool, $dbMonitorsSchool->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_monitors_school()
    {
        $monitorsSchool = MonitorsSchool::factory()->create();

        $resp = $this->monitorsSchoolRepo->delete($monitorsSchool->id);

        $this->assertTrue($resp);
        $this->assertNull(MonitorsSchool::find($monitorsSchool->id), 'MonitorsSchool should not exist in DB');
    }
}
