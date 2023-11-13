<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\TaskCheck;

class TaskCheckApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_task_check()
    {
        $taskCheck = TaskCheck::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/task-checks', $taskCheck
        );

        $this->assertApiResponse($taskCheck);
    }

    /**
     * @test
     */
    public function test_read_task_check()
    {
        $taskCheck = TaskCheck::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/task-checks/'.$taskCheck->id
        );

        $this->assertApiResponse($taskCheck->toArray());
    }

    /**
     * @test
     */
    public function test_update_task_check()
    {
        $taskCheck = TaskCheck::factory()->create();
        $editedTaskCheck = TaskCheck::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/task-checks/'.$taskCheck->id,
            $editedTaskCheck
        );

        $this->assertApiResponse($editedTaskCheck);
    }

    /**
     * @test
     */
    public function test_delete_task_check()
    {
        $taskCheck = TaskCheck::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/task-checks/'.$taskCheck->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/task-checks/'.$taskCheck->id
        );

        $this->response->assertStatus(404);
    }
}
