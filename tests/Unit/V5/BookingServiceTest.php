<?php

namespace Tests\Unit\V5;

use App\V5\Modules\Booking\Services\BookingService;
use App\V5\Modules\Booking\Services\BookingPriceCalculatorService;
use App\V5\Modules\Booking\Services\BookingAvailabilityService;
use App\V5\Modules\Booking\Services\BookingWorkflowService;
use App\V5\Modules\Booking\Repositories\BookingRepository;
use App\V5\Modules\Booking\Repositories\BookingExtraRepository;
use App\V5\Modules\Booking\Repositories\BookingEquipmentRepository;
use App\V5\Modules\Booking\Repositories\BookingPaymentRepository;
use App\V5\Modules\Booking\Models\Booking;
use App\V5\Exceptions\BookingValidationException;
use App\V5\Exceptions\BookingNotFoundException;
use Tests\TestCase;
use Mockery;

/**
 * V5 Booking Service Unit Tests
 */
class BookingServiceTest extends TestCase
{
    private $bookingService;
    private $bookingRepository;
    private $extraRepository;
    private $equipmentRepository;
    private $paymentRepository;
    private $priceCalculator;
    private $availabilityService;
    private $workflowService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bookingRepository = Mockery::mock(BookingRepository::class);
        $this->extraRepository = Mockery::mock(BookingExtraRepository::class);
        $this->equipmentRepository = Mockery::mock(BookingEquipmentRepository::class);
        $this->paymentRepository = Mockery::mock(BookingPaymentRepository::class);
        $this->priceCalculator = Mockery::mock(BookingPriceCalculatorService::class);
        $this->availabilityService = Mockery::mock(BookingAvailabilityService::class);
        $this->workflowService = Mockery::mock(BookingWorkflowService::class);

        $this->bookingService = new BookingService(
            $this->bookingRepository,
            $this->extraRepository,
            $this->equipmentRepository,
            $this->paymentRepository,
            $this->priceCalculator,
            $this->availabilityService,
            $this->workflowService
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function can_create_booking_with_valid_data()
    {
        $seasonId = 1;
        $schoolId = 1;
        $bookingData = [
            'type' => 'course',
            'client_id' => 1,
            'course_id' => 1,
            'start_date' => '2024-12-25',
            'participants' => [
                [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'date_of_birth' => '1990-01-01',
                    'level' => 'Intermedio'
                ]
            ]
        ];

        // Mock availability check
        $this->availabilityService->shouldReceive('checkAvailability')
            ->once()
            ->with($seasonId, $schoolId, $bookingData)
            ->andReturn(['available' => true]);

        // Mock price calculation
        $pricingData = [
            'base_price' => 100.00,
            'extras_price' => 0.00,
            'equipment_price' => 0.00,
            'insurance_price' => 0.00,
            'tax_amount' => 21.00,
            'discount_amount' => 0.00,
            'total_price' => 121.00,
            'currency' => 'EUR'
        ];
        
        $this->priceCalculator->shouldReceive('calculateBookingPrice')
            ->once()
            ->with($bookingData, $seasonId, $schoolId)
            ->andReturn($pricingData);

        // Mock booking creation
        $mockBooking = Mockery::mock(Booking::class);
        $mockBooking->shouldReceive('load')
            ->once()
            ->with(['client', 'course', 'monitor', 'extras', 'equipment'])
            ->andReturnSelf();

        $expectedBookingData = array_merge($bookingData, [
            'season_id' => $seasonId,
            'school_id' => $schoolId,
            'status' => Booking::STATUS_PENDING,
            'base_price' => 100.00,
            'total_price' => 121.00,
            'currency' => 'EUR',
            'has_insurance' => false,
            'has_equipment' => false,
        ]);

        $this->bookingRepository->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) use ($expectedBookingData) {
                return $data['type'] === $expectedBookingData['type'] &&
                       $data['client_id'] === $expectedBookingData['client_id'] &&
                       $data['season_id'] === $expectedBookingData['season_id'] &&
                       $data['status'] === $expectedBookingData['status'];
            }))
            ->andReturn($mockBooking);

        $result = $this->bookingService->createBooking($bookingData, $seasonId, $schoolId);

        $this->assertSame($mockBooking, $result);
    }

    /** @test */
    public function throws_validation_exception_for_missing_required_fields()
    {
        $seasonId = 1;
        $schoolId = 1;
        $invalidData = [
            // Missing required fields: type, client_id, start_date
            'notes' => 'Some notes'
        ];

        $this->expectException(BookingValidationException::class);
        $this->expectExceptionMessage('Field type is required');

        $this->bookingService->createBooking($invalidData, $seasonId, $schoolId);
    }

    /** @test */
    public function throws_validation_exception_for_invalid_booking_type()
    {
        $seasonId = 1;
        $schoolId = 1;
        $invalidData = [
            'type' => 'invalid_type',
            'client_id' => 1,
            'start_date' => '2024-12-25',
            'participants' => [['first_name' => 'John', 'last_name' => 'Doe']]
        ];

        $this->expectException(BookingValidationException::class);
        $this->expectExceptionMessage('Invalid booking type');

        $this->bookingService->createBooking($invalidData, $seasonId, $schoolId);
    }

    /** @test */
    public function throws_validation_exception_for_past_date()
    {
        $seasonId = 1;
        $schoolId = 1;
        $invalidData = [
            'type' => 'course',
            'client_id' => 1,
            'start_date' => '2020-01-01', // Past date
            'participants' => [['first_name' => 'John', 'last_name' => 'Doe']]
        ];

        $this->expectException(BookingValidationException::class);
        $this->expectExceptionMessage('Booking start date cannot be in the past');

        $this->bookingService->createBooking($invalidData, $seasonId, $schoolId);
    }

    /** @test */
    public function throws_validation_exception_when_not_available()
    {
        $seasonId = 1;
        $schoolId = 1;
        $bookingData = [
            'type' => 'course',
            'client_id' => 1,
            'course_id' => 1,
            'start_date' => '2024-12-25',
            'participants' => [['first_name' => 'John', 'last_name' => 'Doe']]
        ];

        // Mock unavailable
        $this->availabilityService->shouldReceive('checkAvailability')
            ->once()
            ->with($seasonId, $schoolId, $bookingData)
            ->andReturn(['available' => false]);

        $this->expectException(BookingValidationException::class);
        $this->expectExceptionMessage('Selected time slot is not available');

        $this->bookingService->createBooking($bookingData, $seasonId, $schoolId);
    }

    /** @test */
    public function can_find_booking_by_id()
    {
        $bookingId = 1;
        $seasonId = 1;
        $schoolId = 1;

        $mockBooking = Mockery::mock(Booking::class);
        
        $this->bookingRepository->shouldReceive('findById')
            ->once()
            ->with($bookingId, $seasonId, $schoolId)
            ->andReturn($mockBooking);

        $result = $this->bookingService->findBookingById($bookingId, $seasonId, $schoolId);

        $this->assertSame($mockBooking, $result);
    }

    /** @test */
    public function throws_not_found_exception_when_booking_not_exists()
    {
        $bookingId = 999;
        $seasonId = 1;
        $schoolId = 1;

        $this->bookingRepository->shouldReceive('findById')
            ->once()
            ->with($bookingId, $seasonId, $schoolId)
            ->andReturn(null);

        $this->expectException(BookingNotFoundException::class);
        $this->expectExceptionMessage("Booking not found with ID: {$bookingId}");

        $this->bookingService->findBookingById($bookingId, $seasonId, $schoolId);
    }

    /** @test */
    public function can_update_booking_status()
    {
        $bookingId = 1;
        $seasonId = 1;
        $schoolId = 1;
        $newStatus = Booking::STATUS_CONFIRMED;

        $mockBooking = Mockery::mock(Booking::class);
        $mockBooking->shouldReceive('canTransitionTo')
            ->once()
            ->with($newStatus)
            ->andReturn(true);

        $this->bookingRepository->shouldReceive('findById')
            ->once()
            ->with($bookingId, $seasonId, $schoolId)
            ->andReturn($mockBooking);

        $this->workflowService->shouldReceive('changeStatus')
            ->once()
            ->with($mockBooking, $newStatus, null)
            ->andReturn($mockBooking);

        $result = $this->bookingService->updateBookingStatus(
            $bookingId, 
            $newStatus, 
            $seasonId, 
            $schoolId
        );

        $this->assertSame($mockBooking, $result);
    }

    /** @test */
    public function can_get_booking_statistics()
    {
        $seasonId = 1;
        $schoolId = 1;
        $expectedStats = [
            'total_bookings' => 10,
            'pending_bookings' => 2,
            'confirmed_bookings' => 5,
            'completed_bookings' => 3
        ];

        $this->bookingRepository->shouldReceive('getBookingStats')
            ->once()
            ->with($seasonId, $schoolId)
            ->andReturn($expectedStats);

        $result = $this->bookingService->getBookingStats($seasonId, $schoolId);

        $this->assertEquals($expectedStats, $result);
    }

    /** @test */
    public function can_search_bookings()
    {
        $query = 'test search';
        $seasonId = 1;
        $schoolId = 1;
        $limit = 20;

        $mockCollection = collect([]);

        $this->bookingRepository->shouldReceive('searchBookings')
            ->once()
            ->with($query, $seasonId, $schoolId, $limit)
            ->andReturn($mockCollection);

        $result = $this->bookingService->searchBookings($query, $seasonId, $schoolId, $limit);

        $this->assertSame($mockCollection, $result);
    }

    /** @test */
    public function can_delete_booking()
    {
        $bookingId = 1;
        $seasonId = 1;
        $schoolId = 1;
        $reason = 'Test deletion';

        $mockBooking = Mockery::mock(Booking::class);
        $mockBooking->status = Booking::STATUS_PENDING; // Can be deleted

        $this->bookingRepository->shouldReceive('findById')
            ->once()
            ->with($bookingId, $seasonId, $schoolId)
            ->andReturn($mockBooking);

        $this->bookingRepository->shouldReceive('delete')
            ->once()
            ->with($mockBooking)
            ->andReturn(true);

        $result = $this->bookingService->deleteBooking($bookingId, $seasonId, $schoolId, $reason);

        $this->assertTrue($result);
    }

    /** @test */
    public function creates_booking_with_extras_and_equipment()
    {
        $seasonId = 1;
        $schoolId = 1;
        $bookingData = [
            'type' => 'course',
            'client_id' => 1,
            'start_date' => '2024-12-25',
            'participants' => [['first_name' => 'John', 'last_name' => 'Doe']],
            'extras' => [
                ['extra_type' => 'insurance', 'name' => 'Basic Insurance', 'unit_price' => 10.00, 'quantity' => 1]
            ],
            'equipment' => [
                ['equipment_type' => 'skis', 'name' => 'Beginner Skis', 'daily_rate' => 15.00, 'rental_days' => 1]
            ]
        ];

        // Mock availability check
        $this->availabilityService->shouldReceive('checkAvailability')
            ->once()
            ->andReturn(['available' => true]);

        // Mock price calculation
        $this->priceCalculator->shouldReceive('calculateBookingPrice')
            ->once()
            ->andReturn([
                'base_price' => 100.00,
                'extras_price' => 10.00,
                'equipment_price' => 15.00,
                'insurance_price' => 0.00,
                'tax_amount' => 26.25,
                'discount_amount' => 0.00,
                'total_price' => 151.25,
                'currency' => 'EUR'
            ]);

        // Mock booking creation
        $mockBooking = Mockery::mock(Booking::class);
        $mockBooking->id = 1;
        $mockBooking->shouldReceive('load')->andReturnSelf();

        $this->bookingRepository->shouldReceive('create')
            ->once()
            ->andReturn($mockBooking);

        // Mock extras creation
        $this->extraRepository->shouldReceive('bulkCreateForBooking')
            ->once()
            ->with(1, $bookingData['extras']);

        // Mock equipment creation
        $this->equipmentRepository->shouldReceive('bulkCreateForBooking')
            ->once()
            ->with(1, $bookingData['equipment']);

        $result = $this->bookingService->createBooking($bookingData, $seasonId, $schoolId);

        $this->assertSame($mockBooking, $result);
    }
}