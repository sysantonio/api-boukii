<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

use App\Models\{User, School, SchoolUser, Language, Monitor, MonitorsSchool, Station, Course, CourseDate, Client, Booking, BookingUser};
use Carbon\Carbon;

class PlannerApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    private function prepareData()
    {
        $user = User::factory()->create();
        $school = School::factory()->create();
        SchoolUser::factory()->create(['user_id' => $user->id, 'school_id' => $school->id]);

        $langs = Language::factory()->count(2)->create();
        $monitor1 = Monitor::factory()->create([
            'language1_id' => $langs[0]->id,
            'active_school' => $school->id,
        ]);
        $monitor2 = Monitor::factory()->create([
            'language1_id' => $langs[1]->id,
            'active_school' => $school->id,
        ]);
        MonitorsSchool::factory()->create(['monitor_id' => $monitor1->id, 'school_id' => $school->id]);
        MonitorsSchool::factory()->create(['monitor_id' => $monitor2->id, 'school_id' => $school->id]);

        return [$user, $school, $monitor1, $monitor2, $langs];
    }

    /** @test */
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
            'date_end'   => '2025-01-15',
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

    /** @test */
    public function it_gets_planner_without_language_filter()
    {
        [$user, $school] = $this->prepareData();
        $this->actingAs($user);

        $response = $this->json('GET', '/api/admin/getPlanner', [
            'date_start' => Carbon::today()->toDateString(),
            'date_end'   => Carbon::today()->toDateString(),
        ]);

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    /** @test */
    public function it_filters_planner_by_languages()
    {
        [$user, $school, $monitor1, $monitor2, $langs] = $this->prepareData();
        $this->actingAs($user);

        $response = $this->json('GET', '/api/admin/getPlanner', [
            'date_start' => Carbon::today()->toDateString(),
            'date_end'   => Carbon::today()->toDateString(),
            'languages'  => $langs[0]->id,
        ]);

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertArrayHasKey($monitor1->id, $data);
    }
}
