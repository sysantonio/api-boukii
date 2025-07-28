<?php

namespace Tests\APIs;

use App\Services\Weather\WeatherProviderInterface;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class AdminDashboardV3ApiTest extends TestCase
{
    use WithoutMiddleware, DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('booking_users');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('schools');

        Schema::create('schools', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->string('name');
            $table->string('description');
            $table->string('slug');
            $table->boolean('active')->default(1);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('courses', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->bigInteger('school_id');
            $table->tinyInteger('course_type');
            $table->boolean('active')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->bigInteger('school_id');
            $table->integer('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('booking_users', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->bigInteger('booking_id');
            $table->bigInteger('client_id')->nullable();
            $table->date('date');
            $table->integer('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->bigInteger('booking_id');
            $table->bigInteger('school_id');
            $table->decimal('amount', 8, 2);
            $table->string('status');
            $table->timestamps();
            $table->softDeletes();
        });

        $mock = Mockery::mock(WeatherProviderInterface::class);
        $mock->shouldReceive('get12HourForecast')
            ->andReturn([['time' => '10:00', 'temperature' => 5, 'icon' => 1]]);
        $mock->shouldReceive('get5DayForecast')
            ->andReturn([
                ['day' => '2024-01-01', 'temperature_min' => 0, 'temperature_max' => 10, 'icon' => 1]
            ]);

        $this->app->instance(WeatherProviderInterface::class, $mock);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('booking_users');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('schools');
        Mockery::close();
        parent::tearDown();
    }

    private function seedSummaryData(): void
    {
        DB::table('schools')->insert([
            'id' => 1,
            'name' => 'School 1',
            'description' => 'desc',
            'slug' => 'school-1',
            'active' => 1,
            'settings' => json_encode([]),
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-01-01 00:00:00',
        ]);

        DB::table('courses')->insert([
            [
                'id' => 1,
                'school_id' => 1,
                'course_type' => 2,
                'active' => 1,
                'created_at' => '2024-01-01 00:00:00',
                'updated_at' => '2024-01-01 00:00:00',
            ],
            [
                'id' => 2,
                'school_id' => 1,
                'course_type' => 2,
                'active' => 1,
                'created_at' => '2024-01-01 00:00:00',
                'updated_at' => '2024-01-01 00:00:00',
            ],
            [
                'id' => 3,
                'school_id' => 1,
                'course_type' => 1,
                'active' => 1,
                'created_at' => '2024-01-01 00:00:00',
                'updated_at' => '2024-01-01 00:00:00',
            ],
        ]);

        DB::table('bookings')->insert([
            'id' => 1,
            'school_id' => 1,
            'status' => 1,
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-01-01 00:00:00',
        ]);

        DB::table('booking_users')->insert([
            'id' => 1,
            'booking_id' => 1,
            'client_id' => 1,
            'date' => '2024-01-01',
            'status' => 1,
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-01-01 00:00:00',
        ]);

        DB::table('payments')->insert([
            'id' => 1,
            'booking_id' => 1,
            'school_id' => 1,
            'amount' => 50.00,
            'status' => 'paid',
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-01-01 00:00:00',
        ]);
    }

    /** @test */
    public function summary_endpoint_returns_expected_structure()
    {
        $this->seedSummaryData();

        $response = $this->getJson('/api/v3/admin/dashboard/summary?school_id=1&date=2024-01-01');

        $response->assertStatus(200);
        $response->assertJsonPath('data.privateCourses', 2);
        $response->assertJsonPath('data.groupCourses', 1);
        $response->assertJsonPath('data.activeReservationsToday', 1);
        $response->assertJsonPath('data.salesToday', 50.0);
    }

    /** @test */
    public function courses_endpoint_returns_expected_structure()
    {
        $this->getJson('/api/v3/admin/dashboard/courses')
            ->assertStatus(200)
            ->assertJson(['message' => 'courses']);
    }

    /** @test */
    public function sales_endpoint_returns_expected_structure()
    {
        $this->getJson('/api/v3/admin/dashboard/sales')
            ->assertStatus(200)
            ->assertJson(['message' => 'sales']);
    }

    /** @test */
    public function reservations_endpoint_returns_expected_structure()
    {
        $this->getJson('/api/v3/admin/dashboard/reservations')
            ->assertStatus(200)
            ->assertJson(['message' => 'reservations']);
    }

    /** @test */
    public function weather_endpoint_returns_expected_structure()
    {
        $this->getJson('/api/v3/admin/dashboard/weather?station_id=1')
            ->assertStatus(200)
            ->assertJson(['message' => 'weather']);
    }
}
