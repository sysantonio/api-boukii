<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\BookingLog;

class BookingLogApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_booking_log()
    {
        $bookingLog = BookingLog::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/booking-logs', $bookingLog
        );

        $this->assertApiResponse($bookingLog);
    }

    /**
     * @test
     */
    public function test_read_booking_log()
    {
        $bookingLog = BookingLog::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/booking-logs/'.$bookingLog->id
        );

        $this->assertApiResponse($bookingLog->toArray());
    }

    /**
     * @test
     */
    public function test_update_booking_log()
    {
        $bookingLog = BookingLog::factory()->create();
        $editedBookingLog = BookingLog::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/booking-logs/'.$bookingLog->id,
            $editedBookingLog
        );

        $this->assertApiResponse($editedBookingLog);
    }

    /**
     * @test
     */
    public function test_delete_booking_log()
    {
        $bookingLog = BookingLog::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/booking-logs/'.$bookingLog->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/booking-logs/'.$bookingLog->id
        );

        $this->response->assertStatus(404);
    }
}
