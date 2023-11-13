<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\EmailLog;

class EmailLogApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_email_log()
    {
        $emailLog = EmailLog::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/email-logs', $emailLog
        );

        $this->assertApiResponse($emailLog);
    }

    /**
     * @test
     */
    public function test_read_email_log()
    {
        $emailLog = EmailLog::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/email-logs/'.$emailLog->id
        );

        $this->assertApiResponse($emailLog->toArray());
    }

    /**
     * @test
     */
    public function test_update_email_log()
    {
        $emailLog = EmailLog::factory()->create();
        $editedEmailLog = EmailLog::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/email-logs/'.$emailLog->id,
            $editedEmailLog
        );

        $this->assertApiResponse($editedEmailLog);
    }

    /**
     * @test
     */
    public function test_delete_email_log()
    {
        $emailLog = EmailLog::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/email-logs/'.$emailLog->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/email-logs/'.$emailLog->id
        );

        $this->response->assertStatus(404);
    }
}
