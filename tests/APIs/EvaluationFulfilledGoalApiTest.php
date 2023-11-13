<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\EvaluationFulfilledGoal;

class EvaluationFulfilledGoalApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_evaluation_fulfilled_goal()
    {
        $evaluationFulfilledGoal = EvaluationFulfilledGoal::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/evaluation-fulfilled-goals', $evaluationFulfilledGoal
        );

        $this->assertApiResponse($evaluationFulfilledGoal);
    }

    /**
     * @test
     */
    public function test_read_evaluation_fulfilled_goal()
    {
        $evaluationFulfilledGoal = EvaluationFulfilledGoal::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/evaluation-fulfilled-goals/'.$evaluationFulfilledGoal->id
        );

        $this->assertApiResponse($evaluationFulfilledGoal->toArray());
    }

    /**
     * @test
     */
    public function test_update_evaluation_fulfilled_goal()
    {
        $evaluationFulfilledGoal = EvaluationFulfilledGoal::factory()->create();
        $editedEvaluationFulfilledGoal = EvaluationFulfilledGoal::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/evaluation-fulfilled-goals/'.$evaluationFulfilledGoal->id,
            $editedEvaluationFulfilledGoal
        );

        $this->assertApiResponse($editedEvaluationFulfilledGoal);
    }

    /**
     * @test
     */
    public function test_delete_evaluation_fulfilled_goal()
    {
        $evaluationFulfilledGoal = EvaluationFulfilledGoal::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/evaluation-fulfilled-goals/'.$evaluationFulfilledGoal->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/evaluation-fulfilled-goals/'.$evaluationFulfilledGoal->id
        );

        $this->response->assertStatus(404);
    }
}
