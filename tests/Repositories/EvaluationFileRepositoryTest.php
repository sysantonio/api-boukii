<?php

namespace Tests\Repositories;

use App\Models\EvaluationFile;
use App\Repositories\EvaluationFileRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class EvaluationFileRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected EvaluationFileRepository $evaluationFileRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->evaluationFileRepo = app(EvaluationFileRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_evaluation_file()
    {
        $evaluationFile = EvaluationFile::factory()->make()->toArray();

        $createdEvaluationFile = $this->evaluationFileRepo->create($evaluationFile);

        $createdEvaluationFile = $createdEvaluationFile->toArray();
        $this->assertArrayHasKey('id', $createdEvaluationFile);
        $this->assertNotNull($createdEvaluationFile['id'], 'Created EvaluationFile must have id specified');
        $this->assertNotNull(EvaluationFile::find($createdEvaluationFile['id']), 'EvaluationFile with given id must be in DB');
        $this->assertModelData($evaluationFile, $createdEvaluationFile);
    }

    /**
     * @test read
     */
    public function test_read_evaluation_file()
    {
        $evaluationFile = EvaluationFile::factory()->create();

        $dbEvaluationFile = $this->evaluationFileRepo->find($evaluationFile->id);

        $dbEvaluationFile = $dbEvaluationFile->toArray();
        $this->assertModelData($evaluationFile->toArray(), $dbEvaluationFile);
    }

    /**
     * @test update
     */
    public function test_update_evaluation_file()
    {
        $evaluationFile = EvaluationFile::factory()->create();
        $fakeEvaluationFile = EvaluationFile::factory()->make()->toArray();

        $updatedEvaluationFile = $this->evaluationFileRepo->update($fakeEvaluationFile, $evaluationFile->id);

        $this->assertModelData($fakeEvaluationFile, $updatedEvaluationFile->toArray());
        $dbEvaluationFile = $this->evaluationFileRepo->find($evaluationFile->id);
        $this->assertModelData($fakeEvaluationFile, $dbEvaluationFile->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_evaluation_file()
    {
        $evaluationFile = EvaluationFile::factory()->create();

        $resp = $this->evaluationFileRepo->delete($evaluationFile->id);

        $this->assertTrue($resp);
        $this->assertNull(EvaluationFile::find($evaluationFile->id), 'EvaluationFile should not exist in DB');
    }
}
