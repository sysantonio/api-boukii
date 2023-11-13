<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\Monitor;

class MonitorApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_monitor()
    {
        $monitor = Monitor::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/monitors', $monitor
        );

        $this->assertApiResponse($monitor);
    }

    /**
     * @test
     */
    public function test_read_monitor()
    {
        $monitor = Monitor::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/monitors/'.$monitor->id
        );

        $this->assertApiResponse($monitor->toArray());
    }

    /**
     * @test
     */
    public function test_update_monitor()
    {
        $monitor = Monitor::factory()->create();
        $editedMonitor = Monitor::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/monitors/'.$monitor->id,
            $editedMonitor
        );

        $this->assertApiResponse($editedMonitor);
    }

    /**
     * @test
     */
    public function test_delete_monitor()
    {
        $monitor = Monitor::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/monitors/'.$monitor->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/monitors/'.$monitor->id
        );

        $this->response->assertStatus(404);
    }
}
