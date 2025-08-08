<?php

namespace Tests\Feature\V5;

use App\Models\User;
use App\Models\School;
use App\Models\Season;
use App\V5\Models\UserSeasonRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private School $school;
    private Season $season;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->school = School::factory()->create();

        $this->user = User::factory()->create();
        $this->user->schools()->attach($this->school->id);

        $this->season = Season::factory()->create([
            'school_id' => $this->school->id,
            'is_active' => true,
            'name' => 'Test Season 2025'
        ]);

        UserSeasonRole::create([
            'user_id' => $this->user->id,
            'season_id' => $this->season->id,
            'role' => 'admin'
        ]);

        Sanctum::actingAs($this->user, ['*'], 'api_v5');
    }

    public function test_dashboard_stats_endpoint_returns_success()
    {
        $response = $this->getJson('/api/v5/dashboard/stats', [
            'X-Season-Id' => $this->season->id,
            'X-School-Id' => $this->school->id
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'bookings' => [
                            'total',
                            'pending',
                            'confirmed',
                            'cancelled',
                            'todayCount',
                            'weeklyGrowth',
                            'todayRevenue',
                            'pendingPayments'
                        ],
                        'clients' => [
                            'total',
                            'active',
                            'newThisMonth',
                            'vipClients',
                            'averageAge',
                            'topNationalities'
                        ],
                        'revenue' => [
                            'thisMonth',
                            'lastMonth',
                            'growth',
                            'pending',
                            'dailyAverage',
                            'topPaymentMethod',
                            'totalThisSeason'
                        ],
                        'courses' => [
                            'active',
                            'upcoming',
                            'completedThisWeek',
                            'totalCapacity',
                            'occupancyRate',
                            'averageRating'
                        ],
                        'monitors' => [
                            'total',
                            'active',
                            'available',
                            'onLeave',
                            'newThisMonth',
                            'averageRating',
                            'hoursWorkedThisWeek'
                        ],
                        'weather',
                        'salesChannels',
                        'dailySessions',
                        'todayReservations'
                    ],
                    'message'
                ]);
    }

    public function test_dashboard_alerts_endpoint_returns_success()
    {
        $response = $this->getJson('/api/v5/dashboard/alerts', [
            'X-Season-Id' => $this->season->id,
            'X-School-Id' => $this->school->id
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'type',
                            'title',
                            'message',
                            'timestamp',
                            'priority'
                        ]
                    ],
                    'message'
                ]);
    }

    public function test_dashboard_recent_activity_endpoint_returns_success()
    {
        $response = $this->getJson('/api/v5/dashboard/recent-activity?limit=5', [
            'X-Season-Id' => $this->season->id,
            'X-School-Id' => $this->school->id
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'type',
                            'title',
                            'description',
                            'timestamp',
                            'status'
                        ]
                    ],
                    'message'
                ]);
    }

    public function test_dashboard_daily_sessions_endpoint_returns_success()
    {
        $response = $this->getJson('/api/v5/dashboard/daily-sessions', [
            'X-Season-Id' => $this->season->id,
            'X-School-Id' => $this->school->id
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'date',
                            'morningSlots',
                            'afternoonSlots',
                            'totalSessions',
                            'occupancy'
                        ]
                    ],
                    'message'
                ]);
    }

    public function test_dashboard_endpoints_require_authentication()
    {
        $response = $this->getJson('/api/v5/dashboard/stats');
        $response->assertStatus(401);

        $response = $this->getJson('/api/v5/dashboard/alerts');
        $response->assertStatus(401);

        $response = $this->getJson('/api/v5/dashboard/recent-activity');
        $response->assertStatus(401);
    }

    public function test_season_context_validation()
    {
        // Test without school/season context
        $response = $this->getJson('/api/v5/dashboard/stats');
        $response->assertStatus(403);

        // Test with invalid season
        $response = $this->getJson('/api/v5/dashboard/stats', [
            'X-School-Id' => $this->school->id,
            'X-Season-Id' => 99999
        ]);
        $response->assertStatus(403);
    }
}