<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\BookingUserExtra;

class BookingUserExtraApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_booking_user_extra()
    {
        $bookingUserExtra = BookingUserExtra::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/booking-user-extras', $bookingUserExtra
        );

        $this->assertApiResponse($bookingUserExtra);
    }

    /**
     * @test
     */
    public function test_read_booking_user_extra()
    {
        $bookingUserExtra = BookingUserExtra::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/booking-user-extras/'.$bookingUserExtra->id
        );

        $this->assertApiResponse($bookingUserExtra->toArray());
    }

    /**
     * @test
     */
    public function test_update_booking_user_extra()
    {
        $bookingUserExtra = BookingUserExtra::factory()->create();
        $editedBookingUserExtra = BookingUserExtra::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/booking-user-extras/'.$bookingUserExtra->id,
            $editedBookingUserExtra
        );

        $this->assertApiResponse($editedBookingUserExtra);
    }

    /**
     * @test
     */
    public function test_delete_booking_user_extra()
    {
        $bookingUserExtra = BookingUserExtra::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/booking-user-extras/'.$bookingUserExtra->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/booking-user-extras/'.$bookingUserExtra->id
        );

        $this->response->assertStatus(404);
    }
}
