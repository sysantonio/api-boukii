<?php

namespace Tests\Repositories;

use App\Models\BookingLog;
use App\Repositories\BookingLogRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class BookingLogRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected BookingLogRepository $bookingLogRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->bookingLogRepo = app(BookingLogRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_booking_log()
    {
        $bookingLog = BookingLog::factory()->make()->toArray();

        $createdBookingLog = $this->bookingLogRepo->create($bookingLog);

        $createdBookingLog = $createdBookingLog->toArray();
        $this->assertArrayHasKey('id', $createdBookingLog);
        $this->assertNotNull($createdBookingLog['id'], 'Created BookingLog must have id specified');
        $this->assertNotNull(BookingLog::find($createdBookingLog['id']), 'BookingLog with given id must be in DB');
        $this->assertModelData($bookingLog, $createdBookingLog);
    }

    /**
     * @test read
     */
    public function test_read_booking_log()
    {
        $bookingLog = BookingLog::factory()->create();

        $dbBookingLog = $this->bookingLogRepo->find($bookingLog->id);

        $dbBookingLog = $dbBookingLog->toArray();
        $this->assertModelData($bookingLog->toArray(), $dbBookingLog);
    }

    /**
     * @test update
     */
    public function test_update_booking_log()
    {
        $bookingLog = BookingLog::factory()->create();
        $fakeBookingLog = BookingLog::factory()->make()->toArray();

        $updatedBookingLog = $this->bookingLogRepo->update($fakeBookingLog, $bookingLog->id);

        $this->assertModelData($fakeBookingLog, $updatedBookingLog->toArray());
        $dbBookingLog = $this->bookingLogRepo->find($bookingLog->id);
        $this->assertModelData($fakeBookingLog, $dbBookingLog->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_booking_log()
    {
        $bookingLog = BookingLog::factory()->create();

        $resp = $this->bookingLogRepo->delete($bookingLog->id);

        $this->assertTrue($resp);
        $this->assertNull(BookingLog::find($bookingLog->id), 'BookingLog should not exist in DB');
    }
}
