<?php

namespace Tests\Feature\V5;

use App\V5\Modules\Booking\Models\Booking;
use App\Models\Client;
use App\Models\Course;
use App\Models\Monitor;
use App\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

/**
 * V5 Booking Controller Test Suite
 * 
 * Comprehensive tests for booking API endpoints
 */
class BookingControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $user;
    private $school;
    private $client;
    private $course;
    private $monitor;
    private $seasonId = 1;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user and authenticate
        $this->user = \App\Models\User::factory()->create();
        Sanctum::actingAs($this->user);

        // Create test data
        $this->school = School::factory()->create();
        $this->client = Client::factory()->create(['school_id' => $this->school->id]);
        $this->course = Course::factory()->create(['school_id' => $this->school->id]);
        $this->monitor = Monitor::factory()->create();
    }

    /** @test */
    public function can_create_booking_with_valid_data()
    {
        $bookingData = [
            'type' => 'course',
            'client_id' => $this->client->id,
            'course_id' => $this->course->id,
            'monitor_id' => $this->monitor->id,
            'start_date' => now()->addDays(7)->format('Y-m-d'),
            'end_date' => now()->addDays(7)->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '12:00',
            'meeting_point' => 'Main Lodge',
            'participants' => [
                [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'date_of_birth' => '1990-01-01',
                    'level' => 'Intermedio',
                    'emergency_contact' => [
                        'name' => 'Jane Doe',
                        'phone' => '+1234567890',
                        'relationship' => 'Spouse'
                    ]
                ]
            ],
            'extras' => [
                [
                    'extra_type' => 'insurance',
                    'name' => 'Basic Insurance',
                    'unit_price' => 10.00,
                    'quantity' => 1
                ]
            ],
            'equipment' => [
                [
                    'equipment_type' => 'skis',
                    'name' => 'Beginner Skis',
                    'participant_name' => 'John Doe',
                    'participant_index' => 0,
                    'daily_rate' => 15.00,
                    'rental_days' => 1
                ]
            ]
        ];

        $response = $this->postJson('/api/v5/bookings', $bookingData, [
            'X-Season-ID' => $this->seasonId,
            'X-School-ID' => $this->school->id
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'booking' => [
                            'id',
                            'booking_reference',
                            'type',
                            'status',
                            'client',
                            'course',
                            'monitor',
                            'schedule',
                            'participants',
                            'pricing',
                            'extras',
                            'equipment',
                            'timestamps'
                        ]
                    ],
                    'message',
                    'timestamp'
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'booking' => [
                            'type' => 'course',
                            'status' => 'pending',
                            'participant_count' => 1
                        ]
                    ]
                ]);

        $this->assertDatabaseHas('v5_bookings', [
            'client_id' => $this->client->id,
            'course_id' => $this->course->id,
            'type' => 'course',
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function can_retrieve_booking_by_id()
    {
        $booking = Booking::factory()->create([
            'season_id' => $this->seasonId,
            'school_id' => $this->school->id,
            'client_id' => $this->client->id,
            'type' => 'course'
        ]);

        $response = $this->getJson("/api/v5/bookings/{$booking->id}", [
            'X-Season-ID' => $this->seasonId,
            'X-School-ID' => $this->school->id
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'booking' => [
                            'id' => $booking->id,
                            'type' => 'course'
                        ]
                    ]
                ]);
    }

    /** @test */
    public function can_update_booking()
    {
        $booking = Booking::factory()->create([
            'season_id' => $this->seasonId,
            'school_id' => $this->school->id,
            'client_id' => $this->client->id,
            'status' => 'pending'
        ]);

        $updateData = [
            'meeting_point' => 'Updated Meeting Point',
            'notes' => 'Updated notes for booking'
        ];

        $response = $this->putJson("/api/v5/bookings/{$booking->id}", $updateData, [
            'X-Season-ID' => $this->seasonId,
            'X-School-ID' => $this->school->id
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'booking' => [
                            'id' => $booking->id,
                            'schedule' => [
                                'meeting_point' => 'Updated Meeting Point'
                            ],
                            'notes' => 'Updated notes for booking'
                        ]
                    ]
                ]);

        $this->assertDatabaseHas('v5_bookings', [
            'id' => $booking->id,
            'meeting_point' => 'Updated Meeting Point',
            'notes' => 'Updated notes for booking'
        ]);
    }

    /** @test */
    public function can_update_booking_status()
    {
        $booking = Booking::factory()->create([
            'season_id' => $this->seasonId,
            'school_id' => $this->school->id,
            'client_id' => $this->client->id,
            'status' => 'pending'
        ]);

        $response = $this->patchJson("/api/v5/bookings/{$booking->id}/status", [
            'status' => 'confirmed',
            'reason' => 'Payment confirmed'
        ], [
            'X-Season-ID' => $this->seasonId,
            'X-School-ID' => $this->school->id
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'booking' => [
                            'id' => $booking->id,
                            'status' => 'confirmed'
                        ]
                    ]
                ]);

        $this->assertDatabaseHas('v5_bookings', [
            'id' => $booking->id,
            'status' => 'confirmed'
        ]);

        $this->assertDatabaseMissing('v5_bookings', [
            'id' => $booking->id,
            'confirmed_at' => null
        ]);
    }

    /** @test */
    public function can_delete_booking()
    {
        $booking = Booking::factory()->create([
            'season_id' => $this->seasonId,
            'school_id' => $this->school->id,
            'client_id' => $this->client->id,
            'status' => 'pending'
        ]);

        $response = $this->deleteJson("/api/v5/bookings/{$booking->id}", [
            'reason' => 'Client cancellation'
        ], [
            'X-Season-ID' => $this->seasonId,
            'X-School-ID' => $this->school->id
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true
                ]);

        $this->assertSoftDeleted('v5_bookings', [
            'id' => $booking->id
        ]);
    }

    /** @test */
    public function can_get_bookings_list_with_filters()
    {
        // Create test bookings
        $bookings = Booking::factory()->count(5)->create([
            'season_id' => $this->seasonId,
            'school_id' => $this->school->id,
            'client_id' => $this->client->id
        ]);

        $response = $this->getJson('/api/v5/bookings', [
            'X-Season-ID' => $this->seasonId,
            'X-School-ID' => $this->school->id
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'bookings',
                        'pagination' => [
                            'current_page',
                            'per_page',
                            'total',
                            'last_page'
                        ]
                    ]
                ]);
    }

    /** @test */
    public function can_search_bookings()
    {
        $booking = Booking::factory()->create([
            'season_id' => $this->seasonId,
            'school_id' => $this->school->id,
            'client_id' => $this->client->id,
            'notes' => 'Special booking with unique identifier'
        ]);

        $response = $this->getJson('/api/v5/bookings/search?q=unique', [
            'X-Season-ID' => $this->seasonId,
            'X-School-ID' => $this->school->id
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'bookings',
                        'query',
                        'results_count'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'query' => 'unique',
                        'results_count' => 1
                    ]
                ]);
    }

    /** @test */
    public function can_get_booking_statistics()
    {
        // Create test bookings with different statuses
        Booking::factory()->create([
            'season_id' => $this->seasonId,
            'school_id' => $this->school->id,
            'status' => 'pending'
        ]);
        Booking::factory()->create([
            'season_id' => $this->seasonId,
            'school_id' => $this->school->id,
            'status' => 'confirmed'
        ]);

        $response = $this->getJson('/api/v5/bookings/stats', [
            'X-Season-ID' => $this->seasonId,
            'X-School-ID' => $this->school->id
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'stats' => [
                            'total_bookings',
                            'pending_bookings',
                            'confirmed_bookings',
                            'paid_bookings',
                            'completed_bookings',
                            'cancelled_bookings'
                        ]
                    ]
                ]);
    }

    /** @test */
    public function validates_required_fields_on_create()
    {
        $response = $this->postJson('/api/v5/bookings', [], [
            'X-Season-ID' => $this->seasonId,
            'X-School-ID' => $this->school->id
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['type', 'client_id', 'start_date', 'participants']);
    }

    /** @test */
    public function validates_invalid_booking_type()
    {
        $bookingData = [
            'type' => 'invalid_type',
            'client_id' => $this->client->id,
            'start_date' => now()->addDays(7)->format('Y-m-d'),
            'participants' => [
                [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'date_of_birth' => '1990-01-01',
                    'level' => 'Intermedio',
                    'emergency_contact' => [
                        'name' => 'Jane Doe',
                        'phone' => '+1234567890',
                        'relationship' => 'Spouse'
                    ]
                ]
            ]
        ];

        $response = $this->postJson('/api/v5/bookings', $bookingData, [
            'X-Season-ID' => $this->seasonId,
            'X-School-ID' => $this->school->id
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['type']);
    }

    /** @test */
    public function validates_past_date_booking()
    {
        $bookingData = [
            'type' => 'course',
            'client_id' => $this->client->id,
            'start_date' => now()->subDays(1)->format('Y-m-d'),
            'participants' => [
                [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'date_of_birth' => '1990-01-01',
                    'level' => 'Intermedio',
                    'emergency_contact' => [
                        'name' => 'Jane Doe',
                        'phone' => '+1234567890',
                        'relationship' => 'Spouse'
                    ]
                ]
            ]
        ];

        $response = $this->postJson('/api/v5/bookings', $bookingData, [
            'X-Season-ID' => $this->seasonId,
            'X-School-ID' => $this->school->id
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['start_date']);
    }

    /** @test */
    public function requires_season_and_school_context()
    {
        $bookingData = [
            'type' => 'course',
            'client_id' => $this->client->id,
            'start_date' => now()->addDays(7)->format('Y-m-d'),
            'participants' => [
                [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'date_of_birth' => '1990-01-01',
                    'level' => 'Intermedio',
                    'emergency_contact' => [
                        'name' => 'Jane Doe',
                        'phone' => '+1234567890',
                        'relationship' => 'Spouse'
                    ]
                ]
            ]
        ];

        // Test without headers
        $response = $this->postJson('/api/v5/bookings', $bookingData);

        $response->assertStatus(400); // Or whatever your middleware returns
    }

    /** @test */
    public function cannot_access_booking_from_different_season_or_school()
    {
        $booking = Booking::factory()->create([
            'season_id' => 999, // Different season
            'school_id' => 999, // Different school
            'client_id' => $this->client->id
        ]);

        $response = $this->getJson("/api/v5/bookings/{$booking->id}", [
            'X-Season-ID' => $this->seasonId,
            'X-School-ID' => $this->school->id
        ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function cannot_update_completed_booking()
    {
        $booking = Booking::factory()->create([
            'season_id' => $this->seasonId,
            'school_id' => $this->school->id,
            'client_id' => $this->client->id,
            'status' => 'completed'
        ]);

        $response = $this->putJson("/api/v5/bookings/{$booking->id}", [
            'notes' => 'Trying to update completed booking'
        ], [
            'X-Season-ID' => $this->seasonId,
            'X-School-ID' => $this->school->id
        ]);

        $response->assertStatus(400); // BookingStatusException
    }

    /** @test */
    public function validates_invalid_status_transition()
    {
        $booking = Booking::factory()->create([
            'season_id' => $this->seasonId,
            'school_id' => $this->school->id,
            'client_id' => $this->client->id,
            'status' => 'pending'
        ]);

        $response = $this->patchJson("/api/v5/bookings/{$booking->id}/status", [
            'status' => 'completed' // Invalid transition from pending to completed
        ], [
            'X-Season-ID' => $this->seasonId,
            'X-School-ID' => $this->school->id
        ]);

        $response->assertStatus(400); // BookingStatusException
    }
}