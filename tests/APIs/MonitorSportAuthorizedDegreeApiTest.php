<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\MonitorSportAuthorizedDegree;

class MonitorSportAuthorizedDegreeApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_monitor_sport_authorized_degree()
    {
        $monitorSportAuthorizedDegree = MonitorSportAuthorizedDegree::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/monitor-sport-authorized-degrees', $monitorSportAuthorizedDegree
        );

        $this->assertApiResponse($monitorSportAuthorizedDegree);
    }

    /**
     * @test
     */
    public function test_read_monitor_sport_authorized_degree()
    {
        $monitorSportAuthorizedDegree = MonitorSportAuthorizedDegree::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/monitor-sport-authorized-degrees/'.$monitorSportAuthorizedDegree->id
        );

        $this->assertApiResponse($monitorSportAuthorizedDegree->toArray());
    }

    /**
     * @test
     */
    public function test_update_monitor_sport_authorized_degree()
    {
        $monitorSportAuthorizedDegree = MonitorSportAuthorizedDegree::factory()->create();
        $editedMonitorSportAuthorizedDegree = MonitorSportAuthorizedDegree::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/monitor-sport-authorized-degrees/'.$monitorSportAuthorizedDegree->id,
            $editedMonitorSportAuthorizedDegree
        );

        $this->assertApiResponse($editedMonitorSportAuthorizedDegree);
    }

    /**
     * @test
     */
    public function test_delete_monitor_sport_authorized_degree()
    {
        $monitorSportAuthorizedDegree = MonitorSportAuthorizedDegree::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/monitor-sport-authorized-degrees/'.$monitorSportAuthorizedDegree->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/monitor-sport-authorized-degrees/'.$monitorSportAuthorizedDegree->id
        );

        $this->response->assertStatus(404);
    }
}
