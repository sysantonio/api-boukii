<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\EvaluationFile;

class EvaluationFileApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_evaluation_file()
    {
        $evaluationFile = EvaluationFile::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/evaluation-files', $evaluationFile
        );

        $this->assertApiResponse($evaluationFile);
    }

    /**
     * @test
     */
    public function test_read_evaluation_file()
    {
        $evaluationFile = EvaluationFile::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/evaluation-files/'.$evaluationFile->id
        );

        $this->assertApiResponse($evaluationFile->toArray());
    }

    /**
     * @test
     */
    public function test_update_evaluation_file()
    {
        $evaluationFile = EvaluationFile::factory()->create();
        $editedEvaluationFile = EvaluationFile::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/evaluation-files/'.$evaluationFile->id,
            $editedEvaluationFile
        );

        $this->assertApiResponse($editedEvaluationFile);
    }

    /**
     * @test
     */
    public function test_delete_evaluation_file()
    {
        $evaluationFile = EvaluationFile::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/evaluation-files/'.$evaluationFile->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/evaluation-files/'.$evaluationFile->id
        );

        $this->response->assertStatus(404);
    }
}
