<?php

namespace Tests\APIs;

use App\Models\Booking;
use App\Models\BookingUser;
use App\Models\Payment;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AnalyticsApiTest extends TestCase
{
    use WithoutMiddleware, DatabaseTransactions;

    /** @test */
    public function test_booking_metrics_endpoint()
    {
        $booking = Booking::factory()->create(['price_total' => 100]);
        BookingUser::factory()->count(2)->create(['booking_id' => $booking->id]);
        Payment::factory()->create([
            'booking_id' => $booking->id,
            'school_id' => $booking->school_id,
            'amount' => 100,
            'status' => 'paid',
        ]);

        $this->response = $this->json('GET', '/api/bookings/' . $booking->id . '/metrics');
        $this->response->assertStatus(200);
        $this->response->assertJsonStructure(['success', 'data' => ['performance', 'financial', 'satisfaction', 'operational'], 'message']);
    }

    /** @test */
    public function test_booking_profitability_endpoint()
    {
        $booking = Booking::factory()->create(['price_total' => 100]);
        Payment::factory()->create([
            'booking_id' => $booking->id,
            'school_id' => $booking->school_id,
            'amount' => 100,
            'status' => 'paid',
        ]);

        $this->response = $this->json('GET', '/api/bookings/' . $booking->id . '/profitability');
        $this->response->assertStatus(200);
        $this->response->assertJsonStructure(['success', 'data' => ['revenue', 'profit', 'marginPercentage', 'costBreakdown'], 'message']);
    }

    /** @test */
    public function test_optimization_suggestions_endpoint()
    {
        $this->response = $this->json('GET', '/api/analytics/optimization-suggestions');
        $this->response->assertStatus(200);
        $this->response->assertJsonStructure(['success', 'data' => ['suggestions'], 'message']);
    }
}
