<?php

namespace Tests\Repositories;

use App\Models\Task;
use App\Repositories\TaskRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class TaskRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected TaskRepository $taskRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->taskRepo = app(TaskRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_task()
    {
        $task = Task::factory()->make()->toArray();

        $createdTask = $this->taskRepo->create($task);

        $createdTask = $createdTask->toArray();
        $this->assertArrayHasKey('id', $createdTask);
        $this->assertNotNull($createdTask['id'], 'Created Task must have id specified');
        $this->assertNotNull(Task::find($createdTask['id']), 'Task with given id must be in DB');
        $this->assertModelData($task, $createdTask);
    }

    /**
     * @test read
     */
    public function test_read_task()
    {
        $task = Task::factory()->create();

        $dbTask = $this->taskRepo->find($task->id);

        $dbTask = $dbTask->toArray();
        $this->assertModelData($task->toArray(), $dbTask);
    }

    /**
     * @test update
     */
    public function test_update_task()
    {
        $task = Task::factory()->create();
        $fakeTask = Task::factory()->make()->toArray();

        $updatedTask = $this->taskRepo->update($fakeTask, $task->id);

        $this->assertModelData($fakeTask, $updatedTask->toArray());
        $dbTask = $this->taskRepo->find($task->id);
        $this->assertModelData($fakeTask, $dbTask->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_task()
    {
        $task = Task::factory()->create();

        $resp = $this->taskRepo->delete($task->id);

        $this->assertTrue($resp);
        $this->assertNull(Task::find($task->id), 'Task should not exist in DB');
    }
}
