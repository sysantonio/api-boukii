<?php

namespace Tests\Repositories;

use App\Models\Evaluation;
use App\Repositories\EvaluationRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class EvaluationRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected EvaluationRepository $evaluationRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->evaluationRepo = app(EvaluationRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_evaluation()
    {
        $evaluation = Evaluation::factory()->make()->toArray();

        $createdEvaluation = $this->evaluationRepo->create($evaluation);

        $createdEvaluation = $createdEvaluation->toArray();
        $this->assertArrayHasKey('id', $createdEvaluation);
        $this->assertNotNull($createdEvaluation['id'], 'Created Evaluation must have id specified');
        $this->assertNotNull(Evaluation::find($createdEvaluation['id']), 'Evaluation with given id must be in DB');
        $this->assertModelData($evaluation, $createdEvaluation);
    }

    /**
     * @test read
     */
    public function test_read_evaluation()
    {
        $evaluation = Evaluation::factory()->create();

        $dbEvaluation = $this->evaluationRepo->find($evaluation->id);

        $dbEvaluation = $dbEvaluation->toArray();
        $this->assertModelData($evaluation->toArray(), $dbEvaluation);
    }

    /**
     * @test update
     */
    public function test_update_evaluation()
    {
        $evaluation = Evaluation::factory()->create();
        $fakeEvaluation = Evaluation::factory()->make()->toArray();

        $updatedEvaluation = $this->evaluationRepo->update($fakeEvaluation, $evaluation->id);

        $this->assertModelData($fakeEvaluation, $updatedEvaluation->toArray());
        $dbEvaluation = $this->evaluationRepo->find($evaluation->id);
        $this->assertModelData($fakeEvaluation, $dbEvaluation->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_evaluation()
    {
        $evaluation = Evaluation::factory()->create();

        $resp = $this->evaluationRepo->delete($evaluation->id);

        $this->assertTrue($resp);
        $this->assertNull(Evaluation::find($evaluation->id), 'Evaluation should not exist in DB');
    }
}
