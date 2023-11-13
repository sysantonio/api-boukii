<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\MonitorObservation;

class MonitorObservationApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_monitor_observation()
    {
        $monitorObservation = MonitorObservation::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/monitor-observations', $monitorObservation
        );

        $this->assertApiResponse($monitorObservation);
    }

    /**
     * @test
     */
    public function test_read_monitor_observation()
    {
        $monitorObservation = MonitorObservation::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/monitor-observations/'.$monitorObservation->id
        );

        $this->assertApiResponse($monitorObservation->toArray());
    }

    /**
     * @test
     */
    public function test_update_monitor_observation()
    {
        $monitorObservation = MonitorObservation::factory()->create();
        $editedMonitorObservation = MonitorObservation::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/monitor-observations/'.$monitorObservation->id,
            $editedMonitorObservation
        );

        $this->assertApiResponse($editedMonitorObservation);
    }

    /**
     * @test
     */
    public function test_delete_monitor_observation()
    {
        $monitorObservation = MonitorObservation::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/monitor-observations/'.$monitorObservation->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/monitor-observations/'.$monitorObservation->id
        );

        $this->response->assertStatus(404);
    }
}
