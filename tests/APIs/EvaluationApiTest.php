<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\Evaluation;

class EvaluationApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_evaluation()
    {
        $evaluation = Evaluation::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/evaluations', $evaluation
        );

        $this->assertApiResponse($evaluation);
    }

    /**
     * @test
     */
    public function test_read_evaluation()
    {
        $evaluation = Evaluation::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/evaluations/'.$evaluation->id
        );

        $this->assertApiResponse($evaluation->toArray());
    }

    /**
     * @test
     */
    public function test_update_evaluation()
    {
        $evaluation = Evaluation::factory()->create();
        $editedEvaluation = Evaluation::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/evaluations/'.$evaluation->id,
            $editedEvaluation
        );

        $this->assertApiResponse($editedEvaluation);
    }

    /**
     * @test
     */
    public function test_delete_evaluation()
    {
        $evaluation = Evaluation::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/evaluations/'.$evaluation->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/evaluations/'.$evaluation->id
        );

        $this->response->assertStatus(404);
    }
}
