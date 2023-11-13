<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\Degree;

class DegreeApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_degree()
    {
        $degree = Degree::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/degrees', $degree
        );

        $this->assertApiResponse($degree);
    }

    /**
     * @test
     */
    public function test_read_degree()
    {
        $degree = Degree::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/degrees/'.$degree->id
        );

        $this->assertApiResponse($degree->toArray());
    }

    /**
     * @test
     */
    public function test_update_degree()
    {
        $degree = Degree::factory()->create();
        $editedDegree = Degree::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/degrees/'.$degree->id,
            $editedDegree
        );

        $this->assertApiResponse($editedDegree);
    }

    /**
     * @test
     */
    public function test_delete_degree()
    {
        $degree = Degree::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/degrees/'.$degree->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/degrees/'.$degree->id
        );

        $this->response->assertStatus(404);
    }
}
