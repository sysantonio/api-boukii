<?php

namespace Tests\Repositories;

use App\Models\EmailLog;
use App\Repositories\EmailLogRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class EmailLogRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected EmailLogRepository $emailLogRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->emailLogRepo = app(EmailLogRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_email_log()
    {
        $emailLog = EmailLog::factory()->make()->toArray();

        $createdEmailLog = $this->emailLogRepo->create($emailLog);

        $createdEmailLog = $createdEmailLog->toArray();
        $this->assertArrayHasKey('id', $createdEmailLog);
        $this->assertNotNull($createdEmailLog['id'], 'Created EmailLog must have id specified');
        $this->assertNotNull(EmailLog::find($createdEmailLog['id']), 'EmailLog with given id must be in DB');
        $this->assertModelData($emailLog, $createdEmailLog);
    }

    /**
     * @test read
     */
    public function test_read_email_log()
    {
        $emailLog = EmailLog::factory()->create();

        $dbEmailLog = $this->emailLogRepo->find($emailLog->id);

        $dbEmailLog = $dbEmailLog->toArray();
        $this->assertModelData($emailLog->toArray(), $dbEmailLog);
    }

    /**
     * @test update
     */
    public function test_update_email_log()
    {
        $emailLog = EmailLog::factory()->create();
        $fakeEmailLog = EmailLog::factory()->make()->toArray();

        $updatedEmailLog = $this->emailLogRepo->update($fakeEmailLog, $emailLog->id);

        $this->assertModelData($fakeEmailLog, $updatedEmailLog->toArray());
        $dbEmailLog = $this->emailLogRepo->find($emailLog->id);
        $this->assertModelData($fakeEmailLog, $dbEmailLog->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_email_log()
    {
        $emailLog = EmailLog::factory()->create();

        $resp = $this->emailLogRepo->delete($emailLog->id);

        $this->assertTrue($resp);
        $this->assertNull(EmailLog::find($emailLog->id), 'EmailLog should not exist in DB');
    }
}
