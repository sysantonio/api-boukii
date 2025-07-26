<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\BookingDraft;

class SmartBookingApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /** @test */
    public function test_create_draft()
    {
        $payload = BookingDraft::factory()->make()->toArray();

        $this->response = $this->json('POST', '/api/bookings/drafts', $payload);

        $this->response->assertStatus(200);
        $this->response->assertJson(['success' => true]);
    }

    /** @test */
    public function test_validate_step()
    {
        $payload = [
            'step' => 1,
            'data' => ['foo' => 'bar'],
            'context' => ['sessionId' => 'abc'],
        ];

        $this->response = $this->json('POST', '/api/bookings/validate-step', $payload);

        $this->response->assertStatus(200);
        $this->response->assertJson(['success' => true]);
    }

    /** @test */
    public function test_smart_create_booking()
    {
        $this->response = $this->json('POST', '/api/bookings/smart-create', []);

        $this->response->assertStatus(200);
        $this->response->assertJson(['success' => true]);
    }
}
