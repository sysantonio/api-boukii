<?php

namespace Tests\Repositories;

use App\Models\BookingUserExtra;
use App\Repositories\BookingUserExtraRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class BookingUserExtraRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected BookingUserExtraRepository $bookingUserExtraRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->bookingUserExtraRepo = app(BookingUserExtraRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_booking_user_extra()
    {
        $bookingUserExtra = BookingUserExtra::factory()->make()->toArray();

        $createdBookingUserExtra = $this->bookingUserExtraRepo->create($bookingUserExtra);

        $createdBookingUserExtra = $createdBookingUserExtra->toArray();
        $this->assertArrayHasKey('id', $createdBookingUserExtra);
        $this->assertNotNull($createdBookingUserExtra['id'], 'Created BookingUserExtra must have id specified');
        $this->assertNotNull(BookingUserExtra::find($createdBookingUserExtra['id']), 'BookingUserExtra with given id must be in DB');
        $this->assertModelData($bookingUserExtra, $createdBookingUserExtra);
    }

    /**
     * @test read
     */
    public function test_read_booking_user_extra()
    {
        $bookingUserExtra = BookingUserExtra::factory()->create();

        $dbBookingUserExtra = $this->bookingUserExtraRepo->find($bookingUserExtra->id);

        $dbBookingUserExtra = $dbBookingUserExtra->toArray();
        $this->assertModelData($bookingUserExtra->toArray(), $dbBookingUserExtra);
    }

    /**
     * @test update
     */
    public function test_update_booking_user_extra()
    {
        $bookingUserExtra = BookingUserExtra::factory()->create();
        $fakeBookingUserExtra = BookingUserExtra::factory()->make()->toArray();

        $updatedBookingUserExtra = $this->bookingUserExtraRepo->update($fakeBookingUserExtra, $bookingUserExtra->id);

        $this->assertModelData($fakeBookingUserExtra, $updatedBookingUserExtra->toArray());
        $dbBookingUserExtra = $this->bookingUserExtraRepo->find($bookingUserExtra->id);
        $this->assertModelData($fakeBookingUserExtra, $dbBookingUserExtra->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_booking_user_extra()
    {
        $bookingUserExtra = BookingUserExtra::factory()->create();

        $resp = $this->bookingUserExtraRepo->delete($bookingUserExtra->id);

        $this->assertTrue($resp);
        $this->assertNull(BookingUserExtra::find($bookingUserExtra->id), 'BookingUserExtra should not exist in DB');
    }
}
