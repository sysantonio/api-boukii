<?php

namespace Tests\Repositories;

use App\Models\Mail;
use App\Repositories\MailRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class MailRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected MailRepository $mailRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->mailRepo = app(MailRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_mail()
    {
        $mail = Mail::factory()->make()->toArray();

        $createdMail = $this->mailRepo->create($mail);

        $createdMail = $createdMail->toArray();
        $this->assertArrayHasKey('id', $createdMail);
        $this->assertNotNull($createdMail['id'], 'Created Mail must have id specified');
        $this->assertNotNull(Mail::find($createdMail['id']), 'Mail with given id must be in DB');
        $this->assertModelData($mail, $createdMail);
    }

    /**
     * @test read
     */
    public function test_read_mail()
    {
        $mail = Mail::factory()->create();

        $dbMail = $this->mailRepo->find($mail->id);

        $dbMail = $dbMail->toArray();
        $this->assertModelData($mail->toArray(), $dbMail);
    }

    /**
     * @test update
     */
    public function test_update_mail()
    {
        $mail = Mail::factory()->create();
        $fakeMail = Mail::factory()->make()->toArray();

        $updatedMail = $this->mailRepo->update($fakeMail, $mail->id);

        $this->assertModelData($fakeMail, $updatedMail->toArray());
        $dbMail = $this->mailRepo->find($mail->id);
        $this->assertModelData($fakeMail, $dbMail->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_mail()
    {
        $mail = Mail::factory()->create();

        $resp = $this->mailRepo->delete($mail->id);

        $this->assertTrue($resp);
        $this->assertNull(Mail::find($mail->id), 'Mail should not exist in DB');
    }
}
