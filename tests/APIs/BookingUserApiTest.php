<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\BookingUser;

class BookingUserApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_booking_user()
    {
        $bookingUser = BookingUser::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/booking-users', $bookingUser
        );

        $this->assertApiResponse($bookingUser);
    }

    /**
     * @test
     */
    public function test_read_booking_user()
    {
        $bookingUser = BookingUser::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/booking-users/'.$bookingUser->id
        );

        $this->assertApiResponse($bookingUser->toArray());
    }

    /**
     * @test
     */
    public function test_update_booking_user()
    {
        $bookingUser = BookingUser::factory()->create();
        $editedBookingUser = BookingUser::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/booking-users/'.$bookingUser->id,
            $editedBookingUser
        );

        $this->assertApiResponse($editedBookingUser);
    }

    /**
     * @test
     */
    public function test_delete_booking_user()
    {
        $bookingUser = BookingUser::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/booking-users/'.$bookingUser->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/booking-users/'.$bookingUser->id
        );

        $this->response->assertStatus(404);
    }
}
