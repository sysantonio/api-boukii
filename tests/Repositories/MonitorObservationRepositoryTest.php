<?php

namespace Tests\Repositories;

use App\Models\MonitorObservation;
use App\Repositories\MonitorObservationRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class MonitorObservationRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected MonitorObservationRepository $monitorObservationRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->monitorObservationRepo = app(MonitorObservationRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_monitor_observation()
    {
        $monitorObservation = MonitorObservation::factory()->make()->toArray();

        $createdMonitorObservation = $this->monitorObservationRepo->create($monitorObservation);

        $createdMonitorObservation = $createdMonitorObservation->toArray();
        $this->assertArrayHasKey('id', $createdMonitorObservation);
        $this->assertNotNull($createdMonitorObservation['id'], 'Created MonitorObservation must have id specified');
        $this->assertNotNull(MonitorObservation::find($createdMonitorObservation['id']), 'MonitorObservation with given id must be in DB');
        $this->assertModelData($monitorObservation, $createdMonitorObservation);
    }

    /**
     * @test read
     */
    public function test_read_monitor_observation()
    {
        $monitorObservation = MonitorObservation::factory()->create();

        $dbMonitorObservation = $this->monitorObservationRepo->find($monitorObservation->id);

        $dbMonitorObservation = $dbMonitorObservation->toArray();
        $this->assertModelData($monitorObservation->toArray(), $dbMonitorObservation);
    }

    /**
     * @test update
     */
    public function test_update_monitor_observation()
    {
        $monitorObservation = MonitorObservation::factory()->create();
        $fakeMonitorObservation = MonitorObservation::factory()->make()->toArray();

        $updatedMonitorObservation = $this->monitorObservationRepo->update($fakeMonitorObservation, $monitorObservation->id);

        $this->assertModelData($fakeMonitorObservation, $updatedMonitorObservation->toArray());
        $dbMonitorObservation = $this->monitorObservationRepo->find($monitorObservation->id);
        $this->assertModelData($fakeMonitorObservation, $dbMonitorObservation->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_monitor_observation()
    {
        $monitorObservation = MonitorObservation::factory()->create();

        $resp = $this->monitorObservationRepo->delete($monitorObservation->id);

        $this->assertTrue($resp);
        $this->assertNull(MonitorObservation::find($monitorObservation->id), 'MonitorObservation should not exist in DB');
    }
}
