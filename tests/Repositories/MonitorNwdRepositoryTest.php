<?php

namespace Tests\Repositories;

use App\Models\MonitorNwd;
use App\Repositories\MonitorNwdRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class MonitorNwdRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected MonitorNwdRepository $monitorNwdRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->monitorNwdRepo = app(MonitorNwdRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_monitor_nwd()
    {
        $monitorNwd = MonitorNwd::factory()->make()->toArray();

        $createdMonitorNwd = $this->monitorNwdRepo->create($monitorNwd);

        $createdMonitorNwd = $createdMonitorNwd->toArray();
        $this->assertArrayHasKey('id', $createdMonitorNwd);
        $this->assertNotNull($createdMonitorNwd['id'], 'Created MonitorNwd must have id specified');
        $this->assertNotNull(MonitorNwd::find($createdMonitorNwd['id']), 'MonitorNwd with given id must be in DB');
        $this->assertModelData($monitorNwd, $createdMonitorNwd);
    }

    /**
     * @test read
     */
    public function test_read_monitor_nwd()
    {
        $monitorNwd = MonitorNwd::factory()->create();

        $dbMonitorNwd = $this->monitorNwdRepo->find($monitorNwd->id);

        $dbMonitorNwd = $dbMonitorNwd->toArray();
        $this->assertModelData($monitorNwd->toArray(), $dbMonitorNwd);
    }

    /**
     * @test update
     */
    public function test_update_monitor_nwd()
    {
        $monitorNwd = MonitorNwd::factory()->create();
        $fakeMonitorNwd = MonitorNwd::factory()->make()->toArray();

        $updatedMonitorNwd = $this->monitorNwdRepo->update($fakeMonitorNwd, $monitorNwd->id);

        $this->assertModelData($fakeMonitorNwd, $updatedMonitorNwd->toArray());
        $dbMonitorNwd = $this->monitorNwdRepo->find($monitorNwd->id);
        $this->assertModelData($fakeMonitorNwd, $dbMonitorNwd->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_monitor_nwd()
    {
        $monitorNwd = MonitorNwd::factory()->create();

        $resp = $this->monitorNwdRepo->delete($monitorNwd->id);

        $this->assertTrue($resp);
        $this->assertNull(MonitorNwd::find($monitorNwd->id), 'MonitorNwd should not exist in DB');
    }
}
