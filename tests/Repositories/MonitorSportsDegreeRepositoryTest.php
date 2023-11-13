<?php

namespace Tests\Repositories;

use App\Models\MonitorSportsDegree;
use App\Repositories\MonitorSportsDegreeRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class MonitorSportsDegreeRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected MonitorSportsDegreeRepository $monitorSportsDegreeRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->monitorSportsDegreeRepo = app(MonitorSportsDegreeRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_monitor_sports_degree()
    {
        $monitorSportsDegree = MonitorSportsDegree::factory()->make()->toArray();

        $createdMonitorSportsDegree = $this->monitorSportsDegreeRepo->create($monitorSportsDegree);

        $createdMonitorSportsDegree = $createdMonitorSportsDegree->toArray();
        $this->assertArrayHasKey('id', $createdMonitorSportsDegree);
        $this->assertNotNull($createdMonitorSportsDegree['id'], 'Created MonitorSportsDegree must have id specified');
        $this->assertNotNull(MonitorSportsDegree::find($createdMonitorSportsDegree['id']), 'MonitorSportsDegree with given id must be in DB');
        $this->assertModelData($monitorSportsDegree, $createdMonitorSportsDegree);
    }

    /**
     * @test read
     */
    public function test_read_monitor_sports_degree()
    {
        $monitorSportsDegree = MonitorSportsDegree::factory()->create();

        $dbMonitorSportsDegree = $this->monitorSportsDegreeRepo->find($monitorSportsDegree->id);

        $dbMonitorSportsDegree = $dbMonitorSportsDegree->toArray();
        $this->assertModelData($monitorSportsDegree->toArray(), $dbMonitorSportsDegree);
    }

    /**
     * @test update
     */
    public function test_update_monitor_sports_degree()
    {
        $monitorSportsDegree = MonitorSportsDegree::factory()->create();
        $fakeMonitorSportsDegree = MonitorSportsDegree::factory()->make()->toArray();

        $updatedMonitorSportsDegree = $this->monitorSportsDegreeRepo->update($fakeMonitorSportsDegree, $monitorSportsDegree->id);

        $this->assertModelData($fakeMonitorSportsDegree, $updatedMonitorSportsDegree->toArray());
        $dbMonitorSportsDegree = $this->monitorSportsDegreeRepo->find($monitorSportsDegree->id);
        $this->assertModelData($fakeMonitorSportsDegree, $dbMonitorSportsDegree->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_monitor_sports_degree()
    {
        $monitorSportsDegree = MonitorSportsDegree::factory()->create();

        $resp = $this->monitorSportsDegreeRepo->delete($monitorSportsDegree->id);

        $this->assertTrue($resp);
        $this->assertNull(MonitorSportsDegree::find($monitorSportsDegree->id), 'MonitorSportsDegree should not exist in DB');
    }
}
