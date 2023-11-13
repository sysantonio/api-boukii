<?php

namespace Tests\Repositories;

use App\Models\EvaluationFulfilledGoal;
use App\Repositories\EvaluationFulfilledGoalRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class EvaluationFulfilledGoalRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected EvaluationFulfilledGoalRepository $evaluationFulfilledGoalRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->evaluationFulfilledGoalRepo = app(EvaluationFulfilledGoalRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_evaluation_fulfilled_goal()
    {
        $evaluationFulfilledGoal = EvaluationFulfilledGoal::factory()->make()->toArray();

        $createdEvaluationFulfilledGoal = $this->evaluationFulfilledGoalRepo->create($evaluationFulfilledGoal);

        $createdEvaluationFulfilledGoal = $createdEvaluationFulfilledGoal->toArray();
        $this->assertArrayHasKey('id', $createdEvaluationFulfilledGoal);
        $this->assertNotNull($createdEvaluationFulfilledGoal['id'], 'Created EvaluationFulfilledGoal must have id specified');
        $this->assertNotNull(EvaluationFulfilledGoal::find($createdEvaluationFulfilledGoal['id']), 'EvaluationFulfilledGoal with given id must be in DB');
        $this->assertModelData($evaluationFulfilledGoal, $createdEvaluationFulfilledGoal);
    }

    /**
     * @test read
     */
    public function test_read_evaluation_fulfilled_goal()
    {
        $evaluationFulfilledGoal = EvaluationFulfilledGoal::factory()->create();

        $dbEvaluationFulfilledGoal = $this->evaluationFulfilledGoalRepo->find($evaluationFulfilledGoal->id);

        $dbEvaluationFulfilledGoal = $dbEvaluationFulfilledGoal->toArray();
        $this->assertModelData($evaluationFulfilledGoal->toArray(), $dbEvaluationFulfilledGoal);
    }

    /**
     * @test update
     */
    public function test_update_evaluation_fulfilled_goal()
    {
        $evaluationFulfilledGoal = EvaluationFulfilledGoal::factory()->create();
        $fakeEvaluationFulfilledGoal = EvaluationFulfilledGoal::factory()->make()->toArray();

        $updatedEvaluationFulfilledGoal = $this->evaluationFulfilledGoalRepo->update($fakeEvaluationFulfilledGoal, $evaluationFulfilledGoal->id);

        $this->assertModelData($fakeEvaluationFulfilledGoal, $updatedEvaluationFulfilledGoal->toArray());
        $dbEvaluationFulfilledGoal = $this->evaluationFulfilledGoalRepo->find($evaluationFulfilledGoal->id);
        $this->assertModelData($fakeEvaluationFulfilledGoal, $dbEvaluationFulfilledGoal->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_evaluation_fulfilled_goal()
    {
        $evaluationFulfilledGoal = EvaluationFulfilledGoal::factory()->create();

        $resp = $this->evaluationFulfilledGoalRepo->delete($evaluationFulfilledGoal->id);

        $this->assertTrue($resp);
        $this->assertNull(EvaluationFulfilledGoal::find($evaluationFulfilledGoal->id), 'EvaluationFulfilledGoal should not exist in DB');
    }
}
