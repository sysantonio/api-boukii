<?php

namespace Tests\Repositories;

use App\Models\Booking;
use App\Repositories\BookingRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class BookingRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected BookingRepository $bookingRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->bookingRepo = app(BookingRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_booking()
    {
        $booking = Booking::factory()->make()->toArray();

        $createdBooking = $this->bookingRepo->create($booking);

        $createdBooking = $createdBooking->toArray();
        $this->assertArrayHasKey('id', $createdBooking);
        $this->assertNotNull($createdBooking['id'], 'Created Booking must have id specified');
        $this->assertNotNull(Booking::find($createdBooking['id']), 'Booking with given id must be in DB');
        $this->assertModelData($booking, $createdBooking);
    }

    /**
     * @test read
     */
    public function test_read_booking()
    {
        $booking = Booking::factory()->create();

        $dbBooking = $this->bookingRepo->find($booking->id);

        $dbBooking = $dbBooking->toArray();
        $this->assertModelData($booking->toArray(), $dbBooking);
    }

    /**
     * @test update
     */
    public function test_update_booking()
    {
        $booking = Booking::factory()->create();
        $fakeBooking = Booking::factory()->make()->toArray();

        $updatedBooking = $this->bookingRepo->update($fakeBooking, $booking->id);

        $this->assertModelData($fakeBooking, $updatedBooking->toArray());
        $dbBooking = $this->bookingRepo->find($booking->id);
        $this->assertModelData($fakeBooking, $dbBooking->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_booking()
    {
        $booking = Booking::factory()->create();

        $resp = $this->bookingRepo->delete($booking->id);

        $this->assertTrue($resp);
        $this->assertNull(Booking::find($booking->id), 'Booking should not exist in DB');
    }
}
