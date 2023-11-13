<?php

namespace Tests\Repositories;

use App\Models\BookingUser;
use App\Repositories\BookingUserRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class BookingUserRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected BookingUserRepository $bookingUserRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->bookingUserRepo = app(BookingUserRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_booking_user()
    {
        $bookingUser = BookingUser::factory()->make()->toArray();

        $createdBookingUser = $this->bookingUserRepo->create($bookingUser);

        $createdBookingUser = $createdBookingUser->toArray();
        $this->assertArrayHasKey('id', $createdBookingUser);
        $this->assertNotNull($createdBookingUser['id'], 'Created BookingUser must have id specified');
        $this->assertNotNull(BookingUser::find($createdBookingUser['id']), 'BookingUser with given id must be in DB');
        $this->assertModelData($bookingUser, $createdBookingUser);
    }

    /**
     * @test read
     */
    public function test_read_booking_user()
    {
        $bookingUser = BookingUser::factory()->create();

        $dbBookingUser = $this->bookingUserRepo->find($bookingUser->id);

        $dbBookingUser = $dbBookingUser->toArray();
        $this->assertModelData($bookingUser->toArray(), $dbBookingUser);
    }

    /**
     * @test update
     */
    public function test_update_booking_user()
    {
        $bookingUser = BookingUser::factory()->create();
        $fakeBookingUser = BookingUser::factory()->make()->toArray();

        $updatedBookingUser = $this->bookingUserRepo->update($fakeBookingUser, $bookingUser->id);

        $this->assertModelData($fakeBookingUser, $updatedBookingUser->toArray());
        $dbBookingUser = $this->bookingUserRepo->find($bookingUser->id);
        $this->assertModelData($fakeBookingUser, $dbBookingUser->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_booking_user()
    {
        $bookingUser = BookingUser::factory()->create();

        $resp = $this->bookingUserRepo->delete($bookingUser->id);

        $this->assertTrue($resp);
        $this->assertNull(BookingUser::find($bookingUser->id), 'BookingUser should not exist in DB');
    }
}
