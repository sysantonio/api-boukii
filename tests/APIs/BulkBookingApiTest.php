<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\ApiTestTrait;
use Tests\TestCase;

class BulkBookingApiTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions, WithoutMiddleware;

    /** @test */
    public function test_bulk_operations()
    {
        $payload = [
            'operations' => [
                [
                    'type' => 'cancel',
                    'bookingIds' => [1, 2],
                    'parameters' => [],
                    'conditions' => ['skipIfStarted' => true],
                ],
            ],
            'options' => [
                'parallel' => false,
                'rollbackOnError' => true,
                'generateReport' => false,
            ],
        ];

        $this->response = $this->json('POST', '/api/bookings/bulk-operations', $payload);
        $this->response->assertStatus(200);
        $this->response->assertJson(['success' => true]);
    }

    /** @test */
    public function test_duplicate_smart_booking()
    {
        $bookingId = 1;

        $payload = [
            'modifications' => [
                'clientId' => 1,
                'participantCount' => 2,
            ],
            'options' => [
                'optimizeForNewDate' => true,
                'suggestBestSlots' => false,
                'applyCurrentPricing' => true,
                'copyNotes' => true,
            ],
        ];

        $this->response = $this->json('POST', '/api/bookings/'.$bookingId.'/duplicate-smart', $payload);
        $this->response->assertStatus(200);
        $this->response->assertJson(['success' => true]);
    }
}
