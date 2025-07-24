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

    /**
     * @test
     */
    public function test_search_available_monitors()
    {
        $school = \App\Models\School::factory()->create();
        $sport = \App\Models\Sport::factory()->create();
        $degree = \App\Models\Degree::factory()->create(['degree_order' => 1]);

        $monitor = Monitor::factory()->create();
        \App\Models\MonitorsSchool::factory()->create([
            'monitor_id' => $monitor->id,
            'school_id' => $school->id,
            'active_school' => 1,
        ]);

        $monitorSport = \App\Models\MonitorSportsDegree::factory()->create([
            'monitor_id' => $monitor->id,
            'sport_id' => $sport->id,
            'school_id' => $school->id,
            'degree_id' => $degree->id,
            'allow_adults' => true,
        ]);

        \App\Models\MonitorSportAuthorizedDegree::factory()->create([
            'monitor_sport_id' => $monitorSport->id,
            'degree_id' => $degree->id,
            'school_id' => $school->id,
        ]);

        $payload = [
            'sportId' => $sport->id,
            'minimumDegreeId' => $degree->id,
            'date' => now()->toDateString(),
            'startTime' => '10:00',
            'endTime' => '11:00',
            'clientIds' => [],
        ];

        $this->response = $this->json('POST', '/api/admin/monitors/available', $payload);
        $this->response->assertStatus(200);
        $this->response->assertJsonFragment(['id' => $monitor->id]);
    }
}
