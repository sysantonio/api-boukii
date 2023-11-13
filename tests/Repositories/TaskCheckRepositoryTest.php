<?php

namespace Tests\Repositories;

use App\Models\TaskCheck;
use App\Repositories\TaskCheckRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class TaskCheckRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected TaskCheckRepository $taskCheckRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->taskCheckRepo = app(TaskCheckRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_task_check()
    {
        $taskCheck = TaskCheck::factory()->make()->toArray();

        $createdTaskCheck = $this->taskCheckRepo->create($taskCheck);

        $createdTaskCheck = $createdTaskCheck->toArray();
        $this->assertArrayHasKey('id', $createdTaskCheck);
        $this->assertNotNull($createdTaskCheck['id'], 'Created TaskCheck must have id specified');
        $this->assertNotNull(TaskCheck::find($createdTaskCheck['id']), 'TaskCheck with given id must be in DB');
        $this->assertModelData($taskCheck, $createdTaskCheck);
    }

    /**
     * @test read
     */
    public function test_read_task_check()
    {
        $taskCheck = TaskCheck::factory()->create();

        $dbTaskCheck = $this->taskCheckRepo->find($taskCheck->id);

        $dbTaskCheck = $dbTaskCheck->toArray();
        $this->assertModelData($taskCheck->toArray(), $dbTaskCheck);
    }

    /**
     * @test update
     */
    public function test_update_task_check()
    {
        $taskCheck = TaskCheck::factory()->create();
        $fakeTaskCheck = TaskCheck::factory()->make()->toArray();

        $updatedTaskCheck = $this->taskCheckRepo->update($fakeTaskCheck, $taskCheck->id);

        $this->assertModelData($fakeTaskCheck, $updatedTaskCheck->toArray());
        $dbTaskCheck = $this->taskCheckRepo->find($taskCheck->id);
        $this->assertModelData($fakeTaskCheck, $dbTaskCheck->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_task_check()
    {
        $taskCheck = TaskCheck::factory()->create();

        $resp = $this->taskCheckRepo->delete($taskCheck->id);

        $this->assertTrue($resp);
        $this->assertNull(TaskCheck::find($taskCheck->id), 'TaskCheck should not exist in DB');
    }
}
