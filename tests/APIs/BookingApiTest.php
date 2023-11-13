<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\Booking;

class BookingApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_booking()
    {
        $booking = Booking::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/bookings', $booking
        );

        $this->assertApiResponse($booking);
    }

    /**
     * @test
     */
    public function test_read_booking()
    {
        $booking = Booking::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/bookings/'.$booking->id
        );

        $this->assertApiResponse($booking->toArray());
    }

    /**
     * @test
     */
    public function test_update_booking()
    {
        $booking = Booking::factory()->create();
        $editedBooking = Booking::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/bookings/'.$booking->id,
            $editedBooking
        );

        $this->assertApiResponse($editedBooking);
    }

    /**
     * @test
     */
    public function test_delete_booking()
    {
        $booking = Booking::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/bookings/'.$booking->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/bookings/'.$booking->id
        );

        $this->response->assertStatus(404);
    }
}
