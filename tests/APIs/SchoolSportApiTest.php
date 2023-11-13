<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\SchoolSport;

class SchoolSportApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_school_sport()
    {
        $schoolSport = SchoolSport::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/school-sports', $schoolSport
        );

        $this->assertApiResponse($schoolSport);
    }

    /**
     * @test
     */
    public function test_read_school_sport()
    {
        $schoolSport = SchoolSport::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/school-sports/'.$schoolSport->id
        );

        $this->assertApiResponse($schoolSport->toArray());
    }

    /**
     * @test
     */
    public function test_update_school_sport()
    {
        $schoolSport = SchoolSport::factory()->create();
        $editedSchoolSport = SchoolSport::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/school-sports/'.$schoolSport->id,
            $editedSchoolSport
        );

        $this->assertApiResponse($editedSchoolSport);
    }

    /**
     * @test
     */
    public function test_delete_school_sport()
    {
        $schoolSport = SchoolSport::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/school-sports/'.$schoolSport->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/school-sports/'.$schoolSport->id
        );

        $this->response->assertStatus(404);
    }
}
