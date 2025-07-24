<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\PlannerController;
use App\Models\School;
use App\Models\SchoolUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PlannerCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_planner_endpoint_uses_cache()
    {
        Cache::shouldReceive('remember')
            ->once()
            ->andReturn(['cached' => true]);

        $user = User::factory()->create();
        $school = School::factory()->create();
        SchoolUser::create(['user_id' => $user->id, 'school_id' => $school->id]);

        $request = Request::create('/api/admin/getPlanner', 'GET', [
            'school_id' => $school->id,
            'date_start' => '2024-01-01',
            'date_end' => '2024-01-02',
        ]);
        $request->setUserResolver(fn() => $user);

        $controller = new PlannerController();
        $response = $controller->getPlanner($request);

        $this->assertEquals(['cached' => true], $response->getData(true));
    }
}
