<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

use App\Models\User;
use App\Models\School;
use App\Models\SchoolUser;
use App\Models\Language;
use App\Models\Monitor;
use App\Models\MonitorsSchool;
use App\Models\Station;
use App\Models\Course;
use App\Models\CourseDate;
use App\Models\Client;
use App\Models\Booking;
use App\Models\BookingUser;

class PlannerApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_planner_bookings_include_user_id()
    {
        $school = School::factory()->create();
        $user = User::factory()->create();
        SchoolUser::factory()->create([
            'school_id' => $school->id,
            'user_id' => $user->id,
        ]);

        Language::factory()->count(3)->create();
        $monitor = Monitor::factory()->create(['active_school' => $school->id]);
        MonitorsSchool::factory()->create([
            'monitor_id' => $monitor->id,
            'school_id' => $school->id,
            'active_school' => 1,
        ]);

        $station = Station::factory()->create();
        $course = Course::factory()->create([
            'school_id' => $school->id,
            'station_id' => $station->id,
            'course_type' => 2,
        ]);
        $courseDate = CourseDate::factory()->create([
            'course_id' => $course->id,
            'date' => '2025-01-15',
        ]);

        $client = Client::factory()->create();

        $booking = Booking::factory()->create([
            'school_id' => $school->id,
            'client_main_id' => $client->id,
            'user_id' => $user->id,
            'status' => 1,
        ]);

        BookingUser::factory()->create([
            'school_id' => $school->id,
            'booking_id' => $booking->id,
            'client_id' => $client->id,
            'course_id' => $course->id,
            'course_date_id' => $courseDate->id,
            'monitor_id' => $monitor->id,
            'date' => '2025-01-15',
            'status' => 1,
        ]);

        $this->actingAs($user);

        $this->response = $this->json('GET', '/api/admin/getPlanner', [
            'date_start' => '2025-01-15',
            'date_end' => '2025-01-15',
        ]);

        $this->assertApiSuccess();
        $data = $this->response->json('data');
        foreach ($data as $monitorData) {
            foreach ($monitorData['bookings'] as $group) {
                foreach ($group as $bookingItem) {
                    $this->assertArrayHasKey('user_id', $bookingItem);
                }
            }
        }
    }
}
