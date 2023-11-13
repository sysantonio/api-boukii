<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\School;

class SchoolApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_school()
    {
        $school = School::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/schools', $school
        );

        $this->assertApiResponse($school);
    }

    /**
     * @test
     */
    public function test_read_school()
    {
        $school = School::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/schools/'.$school->id
        );

        $this->assertApiResponse($school->toArray());
    }

    /**
     * @test
     */
    public function test_update_school()
    {
        $school = School::factory()->create();
        $editedSchool = School::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/schools/'.$school->id,
            $editedSchool
        );

        $this->assertApiResponse($editedSchool);
    }

    /**
     * @test
     */
    public function test_delete_school()
    {
        $school = School::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/schools/'.$school->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/schools/'.$school->id
        );

        $this->response->assertStatus(404);
    }
}
