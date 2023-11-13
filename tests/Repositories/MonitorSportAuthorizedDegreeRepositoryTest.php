<?php

namespace Tests\Repositories;

use App\Models\MonitorSportAuthorizedDegree;
use App\Repositories\MonitorSportAuthorizedDegreeRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class MonitorSportAuthorizedDegreeRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected MonitorSportAuthorizedDegreeRepository $monitorSportAuthorizedDegreeRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->monitorSportAuthorizedDegreeRepo = app(MonitorSportAuthorizedDegreeRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_monitor_sport_authorized_degree()
    {
        $monitorSportAuthorizedDegree = MonitorSportAuthorizedDegree::factory()->make()->toArray();

        $createdMonitorSportAuthorizedDegree = $this->monitorSportAuthorizedDegreeRepo->create($monitorSportAuthorizedDegree);

        $createdMonitorSportAuthorizedDegree = $createdMonitorSportAuthorizedDegree->toArray();
        $this->assertArrayHasKey('id', $createdMonitorSportAuthorizedDegree);
        $this->assertNotNull($createdMonitorSportAuthorizedDegree['id'], 'Created MonitorSportAuthorizedDegree must have id specified');
        $this->assertNotNull(MonitorSportAuthorizedDegree::find($createdMonitorSportAuthorizedDegree['id']), 'MonitorSportAuthorizedDegree with given id must be in DB');
        $this->assertModelData($monitorSportAuthorizedDegree, $createdMonitorSportAuthorizedDegree);
    }

    /**
     * @test read
     */
    public function test_read_monitor_sport_authorized_degree()
    {
        $monitorSportAuthorizedDegree = MonitorSportAuthorizedDegree::factory()->create();

        $dbMonitorSportAuthorizedDegree = $this->monitorSportAuthorizedDegreeRepo->find($monitorSportAuthorizedDegree->id);

        $dbMonitorSportAuthorizedDegree = $dbMonitorSportAuthorizedDegree->toArray();
        $this->assertModelData($monitorSportAuthorizedDegree->toArray(), $dbMonitorSportAuthorizedDegree);
    }

    /**
     * @test update
     */
    public function test_update_monitor_sport_authorized_degree()
    {
        $monitorSportAuthorizedDegree = MonitorSportAuthorizedDegree::factory()->create();
        $fakeMonitorSportAuthorizedDegree = MonitorSportAuthorizedDegree::factory()->make()->toArray();

        $updatedMonitorSportAuthorizedDegree = $this->monitorSportAuthorizedDegreeRepo->update($fakeMonitorSportAuthorizedDegree, $monitorSportAuthorizedDegree->id);

        $this->assertModelData($fakeMonitorSportAuthorizedDegree, $updatedMonitorSportAuthorizedDegree->toArray());
        $dbMonitorSportAuthorizedDegree = $this->monitorSportAuthorizedDegreeRepo->find($monitorSportAuthorizedDegree->id);
        $this->assertModelData($fakeMonitorSportAuthorizedDegree, $dbMonitorSportAuthorizedDegree->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_monitor_sport_authorized_degree()
    {
        $monitorSportAuthorizedDegree = MonitorSportAuthorizedDegree::factory()->create();

        $resp = $this->monitorSportAuthorizedDegreeRepo->delete($monitorSportAuthorizedDegree->id);

        $this->assertTrue($resp);
        $this->assertNull(MonitorSportAuthorizedDegree::find($monitorSportAuthorizedDegree->id), 'MonitorSportAuthorizedDegree should not exist in DB');
    }
}
