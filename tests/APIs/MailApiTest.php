<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\Mail;

class MailApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_mail()
    {
        $mail = Mail::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/mails', $mail
        );

        $this->assertApiResponse($mail);
    }

    /**
     * @test
     */
    public function test_read_mail()
    {
        $mail = Mail::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/mails/'.$mail->id
        );

        $this->assertApiResponse($mail->toArray());
    }

    /**
     * @test
     */
    public function test_update_mail()
    {
        $mail = Mail::factory()->create();
        $editedMail = Mail::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/mails/'.$mail->id,
            $editedMail
        );

        $this->assertApiResponse($editedMail);
    }

    /**
     * @test
     */
    public function test_delete_mail()
    {
        $mail = Mail::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/mails/'.$mail->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/mails/'.$mail->id
        );

        $this->response->assertStatus(404);
    }
}
