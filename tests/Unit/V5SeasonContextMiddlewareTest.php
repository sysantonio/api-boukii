<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\V5\Middleware\SeasonContextMiddleware;
use App\V5\Modules\Season\Services\SeasonService;
use App\V5\Models\Season;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class V5SeasonContextMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected SeasonService $seasonService;
    protected SeasonContextMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seasonService = Mockery::mock(SeasonService::class);
        $this->middleware = new SeasonContextMiddleware($this->seasonService);
    }

    public function test_passes_through_when_season_id_provided()
    {
        $request = Request::create('/test', 'GET', ['season_id' => 1]);
        
        $season = new Season(['id' => 1, 'name' => 'Test Season']);
        $this->seasonService->shouldReceive('find')->with(1)->andReturn($season);

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_auto_detects_season_from_school_id()
    {
        $request = Request::create('/test', 'GET', ['school_id' => 1]);
        
        $season = new Season(['id' => 2, 'name' => 'Current Season']);
        $this->seasonService->shouldReceive('getCurrentSeason')->with(1)->andReturn($season);

        $this->middleware->handle($request, function ($req) {
            $this->assertEquals(2, $req->get('season_id'));
            return response()->json(['success' => true]);
        });
    }

    public function test_returns_error_for_invalid_season_id()
    {
        $request = Request::create('/test', 'GET', ['season_id' => 999]);
        
        $this->seasonService->shouldReceive('find')->with(999)->andReturn(null);

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertStringContainsString('Invalid season_id', $response->getContent());
    }

    public function test_returns_error_when_no_active_season_for_school()
    {
        $request = Request::create('/test', 'GET', ['school_id' => 1]);
        
        $this->seasonService->shouldReceive('getCurrentSeason')->with(1)->andReturn(null);

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertStringContainsString('No active season found', $response->getContent());
    }

    public function test_requires_season_context_for_protected_routes()
    {
        $request = Request::create('/api/v5/schools', 'GET');

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertStringContainsString('Season context required', $response->getContent());
    }

    public function test_handles_exceptions_gracefully()
    {
        $request = Request::create('/test', 'GET', ['season_id' => 1]);
        
        $this->seasonService->shouldReceive('find')->andThrow(new \Exception('Database error'));

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertStringContainsString('Season context validation failed', $response->getContent());
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}