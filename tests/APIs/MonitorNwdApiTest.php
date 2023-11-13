<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\MonitorNwd;

class MonitorNwdApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_monitor_nwd()
    {
        $monitorNwd = MonitorNwd::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/monitor-nwds', $monitorNwd
        );

        $this->assertApiResponse($monitorNwd);
    }

    /**
     * @test
     */
    public function test_read_monitor_nwd()
    {
        $monitorNwd = MonitorNwd::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/monitor-nwds/'.$monitorNwd->id
        );

        $this->assertApiResponse($monitorNwd->toArray());
    }

    /**
     * @test
     */
    public function test_update_monitor_nwd()
    {
        $monitorNwd = MonitorNwd::factory()->create();
        $editedMonitorNwd = MonitorNwd::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/monitor-nwds/'.$monitorNwd->id,
            $editedMonitorNwd
        );

        $this->assertApiResponse($editedMonitorNwd);
    }

    /**
     * @test
     */
    public function test_delete_monitor_nwd()
    {
        $monitorNwd = MonitorNwd::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/monitor-nwds/'.$monitorNwd->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/monitor-nwds/'.$monitorNwd->id
        );

        $this->response->assertStatus(404);
    }
}
