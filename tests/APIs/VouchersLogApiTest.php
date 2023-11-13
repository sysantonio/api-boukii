<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\VouchersLog;

class VouchersLogApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_vouchers_log()
    {
        $vouchersLog = VouchersLog::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/vouchers-logs', $vouchersLog
        );

        $this->assertApiResponse($vouchersLog);
    }

    /**
     * @test
     */
    public function test_read_vouchers_log()
    {
        $vouchersLog = VouchersLog::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/vouchers-logs/'.$vouchersLog->id
        );

        $this->assertApiResponse($vouchersLog->toArray());
    }

    /**
     * @test
     */
    public function test_update_vouchers_log()
    {
        $vouchersLog = VouchersLog::factory()->create();
        $editedVouchersLog = VouchersLog::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/vouchers-logs/'.$vouchersLog->id,
            $editedVouchersLog
        );

        $this->assertApiResponse($editedVouchersLog);
    }

    /**
     * @test
     */
    public function test_delete_vouchers_log()
    {
        $vouchersLog = VouchersLog::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/vouchers-logs/'.$vouchersLog->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/vouchers-logs/'.$vouchersLog->id
        );

        $this->response->assertStatus(404);
    }
}
