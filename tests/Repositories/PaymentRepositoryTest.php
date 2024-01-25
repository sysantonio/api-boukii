<?php

namespace Tests\Repositories;

use App\Models\Payment;
use App\Repositories\PaymentRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class PaymentRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected PaymentRepository $paymentRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->paymentRepo = app(PaymentRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_payment()
    {
        $payment = Payment::factory()->make()->toArray();

        $createdPayment = $this->paymentRepo->create($payment);

        $createdPayment = $createdPayment->toArray();
        $this->assertArrayHasKey('id', $createdPayment);
        $this->assertNotNull($createdPayment['id'], 'Created Payment must have id specified');
        $this->assertNotNull(Payment::find($createdPayment['id']), 'Payment with given id must be in DB');
        $this->assertModelData($payment, $createdPayment);
    }

    /**
     * @test read
     */
    public function test_read_payment()
    {
        $payment = Payment::factory()->create();

        $dbPayment = $this->paymentRepo->find($payment->id);

        $dbPayment = $dbPayment->toArray();
        $this->assertModelData($payment->toArray(), $dbPayment);
    }

    /**
     * @test update
     */
    public function test_update_payment()
    {
        $payment = Payment::factory()->create();
        $fakePayment = Payment::factory()->make()->toArray();

        $updatedPayment = $this->paymentRepo->update($fakePayment, $payment->id);

        $this->assertModelData($fakePayment, $updatedPayment->toArray());
        $dbPayment = $this->paymentRepo->find($payment->id);
        $this->assertModelData($fakePayment, $dbPayment->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_payment()
    {
        $payment = Payment::factory()->create();

        $resp = $this->paymentRepo->delete($payment->id);

        $this->assertTrue($resp);
        $this->assertNull(Payment::find($payment->id), 'Payment should not exist in DB');
    }
}
