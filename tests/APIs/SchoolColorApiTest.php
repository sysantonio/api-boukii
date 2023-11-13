<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\SchoolColor;

class SchoolColorApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_school_color()
    {
        $schoolColor = SchoolColor::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/school-colors', $schoolColor
        );

        $this->assertApiResponse($schoolColor);
    }

    /**
     * @test
     */
    public function test_read_school_color()
    {
        $schoolColor = SchoolColor::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/school-colors/'.$schoolColor->id
        );

        $this->assertApiResponse($schoolColor->toArray());
    }

    /**
     * @test
     */
    public function test_update_school_color()
    {
        $schoolColor = SchoolColor::factory()->create();
        $editedSchoolColor = SchoolColor::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/school-colors/'.$schoolColor->id,
            $editedSchoolColor
        );

        $this->assertApiResponse($editedSchoolColor);
    }

    /**
     * @test
     */
    public function test_delete_school_color()
    {
        $schoolColor = SchoolColor::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/school-colors/'.$schoolColor->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/school-colors/'.$schoolColor->id
        );

        $this->response->assertStatus(404);
    }
}
