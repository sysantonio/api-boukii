<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\MonitorsSchool;

class MonitorsSchoolApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_monitors_school()
    {
        $monitorsSchool = MonitorsSchool::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/monitors-schools', $monitorsSchool
        );

        $this->assertApiResponse($monitorsSchool);
    }

    /**
     * @test
     */
    public function test_read_monitors_school()
    {
        $monitorsSchool = MonitorsSchool::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/monitors-schools/'.$monitorsSchool->id
        );

        $this->assertApiResponse($monitorsSchool->toArray());
    }

    /**
     * @test
     */
    public function test_update_monitors_school()
    {
        $monitorsSchool = MonitorsSchool::factory()->create();
        $editedMonitorsSchool = MonitorsSchool::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/monitors-schools/'.$monitorsSchool->id,
            $editedMonitorsSchool
        );

        $this->assertApiResponse($editedMonitorsSchool);
    }

    /**
     * @test
     */
    public function test_delete_monitors_school()
    {
        $monitorsSchool = MonitorsSchool::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/monitors-schools/'.$monitorsSchool->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/monitors-schools/'.$monitorsSchool->id
        );

        $this->response->assertStatus(404);
    }
}
