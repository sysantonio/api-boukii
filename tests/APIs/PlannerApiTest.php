<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\{User, School, SchoolUser, Language, Monitor, MonitorsSchool};
use Carbon\Carbon;

class PlannerApiTest extends TestCase
{
    use WithoutMiddleware, DatabaseTransactions;

    private function prepareData()
    {
        $user = User::factory()->create();
        $school = School::factory()->create();
        SchoolUser::factory()->create([ 'user_id' => $user->id, 'school_id' => $school->id ]);

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
