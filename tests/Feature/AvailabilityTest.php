<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseDate;
use App\Models\CourseSubgroup;
use App\Models\BookingUser;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

/**
 * @group mysql
 */
class AvailabilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('booking_users');
        Schema::dropIfExists('course_subgroups');
        Schema::dropIfExists('course_dates');
        Schema::dropIfExists('courses');

        Schema::create('courses', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->tinyInteger('course_type');
            $table->boolean('is_flexible')->default(0);
            $table->bigInteger('sport_id')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('course_dates', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->bigInteger('course_id');
            $table->date('date');
            $table->time('hour_start');
            $table->time('hour_end');
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('course_subgroups', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->bigInteger('course_id');
            $table->bigInteger('course_date_id');
            $table->integer('max_participants');
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('booking_users', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->bigInteger('booking_id')->nullable();
            $table->bigInteger('course_id');
            $table->bigInteger('course_date_id');
            $table->bigInteger('course_subgroup_id');
            $table->bigInteger('monitor_id')->nullable();
            $table->date('date');
            $table->time('hour_start');
            $table->time('hour_end');
            $table->integer('status')->default(1);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('booking_users');
        Schema::dropIfExists('course_subgroups');
        Schema::dropIfExists('course_dates');
        Schema::dropIfExists('courses');
        parent::tearDown();
    }

    private function seedData()
    {
        Course::create([
            'id' => 1,
            'course_type' => 1,
            'is_flexible' => false,
            'sport_id' => 1,
        ]);

        CourseDate::create([
            'id' => 1,
            'course_id' => 1,
            'date' => '2024-01-01',
            'hour_start' => '09:00',
            'hour_end' => '11:00',
        ]);

        CourseSubgroup::create([
            'id' => 1,
            'course_id' => 1,
            'course_date_id' => 1,
            'max_participants' => 2,
        ]);

        CourseSubgroup::create([
            'id' => 2,
            'course_id' => 1,
            'course_date_id' => 1,
            'max_participants' => 2,
        ]);

        BookingUser::create([
            'id' => 1,
            'course_id' => 1,
            'course_date_id' => 1,
            'course_subgroup_id' => 1,
            'date' => '2024-01-01',
            'hour_start' => '09:00',
            'hour_end' => '11:00',
            'status' => 1,
        ]);

        BookingUser::create([
            'id' => 2,
            'course_id' => 1,
            'course_date_id' => 1,
            'course_subgroup_id' => 2,
            'date' => '2024-01-01',
            'hour_start' => '09:00',
            'hour_end' => '11:00',
            'status' => 1,
        ]);

        BookingUser::create([
            'id' => 3,
            'course_id' => 1,
            'course_date_id' => 1,
            'course_subgroup_id' => 2,
            'date' => '2024-01-01',
            'hour_start' => '09:00',
            'hour_end' => '11:00',
            'status' => 1,
        ]);
    }

    public function test_availability_matrix(): void
    {
        $this->seedData();

        $response = $this->postJson('/api/availability/matrix', [
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-01',
            'course_id' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.matrix.0.date', '2024-01-01');
        $response->assertJsonPath('data.matrix.0.slots.0.availability.available', 1);
        $response->assertJsonPath('data.summary.totalSlots', 1);
    }

    public function test_realtime_check_detects_conflict(): void
    {
        $this->seedData();

        $response = $this->postJson('/api/availability/realtime-check', [
            'course_id' => 1,
            'dates' => ['2024-01-01'],
            'time_slots' => ['09:30-10:30'],
        ]);

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.conflicts');
    }

    public function test_realtime_check_no_conflict(): void
    {
        $this->seedData();

        $response = $this->postJson('/api/availability/realtime-check', [
            'course_id' => 1,
            'dates' => ['2024-01-01'],
            'time_slots' => ['12:00-13:00'],
        ]);

        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data.conflicts');
    }
}

