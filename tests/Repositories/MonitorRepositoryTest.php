<?php

namespace Tests\Repositories;

use App\Models\Monitor;
use App\Repositories\MonitorRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class MonitorRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected MonitorRepository $monitorRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->monitorRepo = app(MonitorRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_monitor()
    {
        $monitor = Monitor::factory()->make()->toArray();

        $createdMonitor = $this->monitorRepo->create($monitor);

        $createdMonitor = $createdMonitor->toArray();
        $this->assertArrayHasKey('id', $createdMonitor);
        $this->assertNotNull($createdMonitor['id'], 'Created Monitor must have id specified');
        $this->assertNotNull(Monitor::find($createdMonitor['id']), 'Monitor with given id must be in DB');
        $this->assertModelData($monitor, $createdMonitor);
    }

    /**
     * @test read
     */
    public function test_read_monitor()
    {
        $monitor = Monitor::factory()->create();

        $dbMonitor = $this->monitorRepo->find($monitor->id);

        $dbMonitor = $dbMonitor->toArray();
        $this->assertModelData($monitor->toArray(), $dbMonitor);
    }

    /**
     * @test update
     */
    public function test_update_monitor()
    {
        $monitor = Monitor::factory()->create();
        $fakeMonitor = Monitor::factory()->make()->toArray();

        $updatedMonitor = $this->monitorRepo->update($fakeMonitor, $monitor->id);

        $this->assertModelData($fakeMonitor, $updatedMonitor->toArray());
        $dbMonitor = $this->monitorRepo->find($monitor->id);
        $this->assertModelData($fakeMonitor, $dbMonitor->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_monitor()
    {
        $monitor = Monitor::factory()->create();

        $resp = $this->monitorRepo->delete($monitor->id);

        $this->assertTrue($resp);
        $this->assertNull(Monitor::find($monitor->id), 'Monitor should not exist in DB');
    }
}
