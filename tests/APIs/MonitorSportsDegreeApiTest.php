<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\MonitorSportsDegree;

class MonitorSportsDegreeApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_monitor_sports_degree()
    {
        $monitorSportsDegree = MonitorSportsDegree::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/monitor-sports-degrees', $monitorSportsDegree
        );

        $this->assertApiResponse($monitorSportsDegree);
    }

    /**
     * @test
     */
    public function test_read_monitor_sports_degree()
    {
        $monitorSportsDegree = MonitorSportsDegree::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/monitor-sports-degrees/'.$monitorSportsDegree->id
        );

        $this->assertApiResponse($monitorSportsDegree->toArray());
    }

    /**
     * @test
     */
    public function test_update_monitor_sports_degree()
    {
        $monitorSportsDegree = MonitorSportsDegree::factory()->create();
        $editedMonitorSportsDegree = MonitorSportsDegree::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/monitor-sports-degrees/'.$monitorSportsDegree->id,
            $editedMonitorSportsDegree
        );

        $this->assertApiResponse($editedMonitorSportsDegree);
    }

    /**
     * @test
     */
    public function test_delete_monitor_sports_degree()
    {
        $monitorSportsDegree = MonitorSportsDegree::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/monitor-sports-degrees/'.$monitorSportsDegree->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/monitor-sports-degrees/'.$monitorSportsDegree->id
        );

        $this->response->assertStatus(404);
    }
}
