<?php

namespace Tests\Unit\V5;

use Tests\TestCase;
use App\Http\Middleware\V5\ContextPermissionMiddleware;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Mockery as m;

class ContextPermissionMiddlewareTest extends TestCase
{
    protected ContextPermissionMiddleware $middleware;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->middleware = new ContextPermissionMiddleware();
        $this->user = m::mock(User::class);
    }

    /** @test */
    public function it_requires_authentication()
    {
        Auth::shouldReceive('guard')
            ->with('api_v5')
            ->andReturnSelf();
        Auth::shouldReceive('user')
            ->andReturn(null);

        $request = Request::create('/test');
        
        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['test' => 'passed']);
        }, 'season.admin');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(401, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Authentication required', $data['message']);
        $this->assertEquals('UNAUTHORIZED', $data['error_code']);
    }

    /** @test */
    public function it_allows_global_admin_access()
    {
        $this->user->id = 1;
        
        Auth::shouldReceive('guard')
            ->with('api_v5')
            ->andReturnSelf();
        Auth::shouldReceive('user')
            ->andReturn($this->user);

        Log::shouldReceive('info')->andReturn(null);

        // Mock database query for global permissions
        DB::shouldReceive('table')
            ->with('user_global_roles')
            ->andReturnSelf();
        DB::shouldReceive('where')
            ->with('user_id', 1)
            ->andReturnSelf();
        DB::shouldReceive('where')
            ->with('role', 'superadmin')
            ->andReturnSelf();
        DB::shouldReceive('exists')
            ->andReturn(true);

        $request = Request::create('/test');
        $request->merge(['context_school_id' => 1, 'context_season_id' => 1]);
        
        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['test' => 'passed']);
        }, 'season.admin');

        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('passed', $data['test']);
    }

    /** @test */
    public function it_allows_school_admin_access()
    {
        $this->user->id = 1;
        
        Auth::shouldReceive('guard')
            ->with('api_v5')
            ->andReturnSelf();
        Auth::shouldReceive('user')
            ->andReturn($this->user);

        Log::shouldReceive('info')->andReturn(null);

        // Mock database queries
        // Global permissions check (returns false)
        DB::shouldReceive('table')
            ->with('user_global_roles')
            ->andReturnSelf();
        DB::shouldReceive('where')
            ->with('user_id', 1)
            ->andReturnSelf();
        DB::shouldReceive('where')
            ->with('role', 'superadmin')
            ->andReturnSelf();
        DB::shouldReceive('exists')
            ->andReturn(false);

        // School permissions check (returns true)
        DB::shouldReceive('table')
            ->with('user_school_roles')
            ->andReturnSelf();
        DB::shouldReceive('where')
            ->with('user_id', 1)
            ->andReturnSelf();
        DB::shouldReceive('where')
            ->with('school_id', 1)
            ->andReturnSelf();
        DB::shouldReceive('where')
            ->with('role', 'admin')
            ->andReturnSelf();
        DB::shouldReceive('exists')
            ->andReturn(true);

        $request = Request::create('/test');
        $request->merge(['context_school_id' => 1, 'context_season_id' => 1]);
        
        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['test' => 'passed']);
        }, 'school.admin');

        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('passed', $data['test']);
    }

    /** @test */
    public function it_allows_season_admin_access()
    {
        $this->user->id = 1;
        
        Auth::shouldReceive('guard')
            ->with('api_v5')
            ->andReturnSelf();
        Auth::shouldReceive('user')
            ->andReturn($this->user);

        Log::shouldReceive('info')->andReturn(null);

        // Mock database queries
        // Global permissions check (returns false)
        DB::shouldReceive('table')
            ->with('user_global_roles')
            ->andReturnSelf();
        DB::shouldReceive('where')
            ->with('user_id', 1)
            ->andReturnSelf();
        DB::shouldReceive('where')
            ->with('role', 'superadmin')
            ->andReturnSelf();
        DB::shouldReceive('exists')
            ->andReturn(false);

        // School permissions check (returns false)
        DB::shouldReceive('table')
            ->with('user_school_roles')
            ->andReturnSelf();
        DB::shouldReceive('where')
            ->with('user_id', 1)
            ->andReturnSelf();
        DB::shouldReceive('where')
            ->with('school_id', 1)
            ->andReturnSelf();
        DB::shouldReceive('where')
            ->with('role', 'admin')
            ->andReturnSelf();
        DB::shouldReceive('exists')
            ->andReturn(false);

        // Season permissions check (returns true)
        DB::shouldReceive('table')
            ->with('user_season_roles')
            ->andReturnSelf();
        DB::shouldReceive('where')
            ->with('user_id', 1)
            ->andReturnSelf();
        DB::shouldReceive('where')
            ->with('season_id', 1)
            ->andReturnSelf();
        DB::shouldReceive('where')
            ->with('role', 'admin')
            ->andReturnSelf();
        DB::shouldReceive('exists')
            ->andReturn(true);

        $request = Request::create('/test');
        $request->merge(['context_school_id' => 1, 'context_season_id' => 1]);
        
        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['test' => 'passed']);
        }, 'season.admin');

        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('passed', $data['test']);
    }

    /** @test */
    public function it_denies_access_without_permissions()
    {
        $this->user->id = 1;
        
        Auth::shouldReceive('guard')
            ->with('api_v5')
            ->andReturnSelf();
        Auth::shouldReceive('user')
            ->andReturn($this->user);

        Log::shouldReceive('info')->andReturn(null);

        // Mock database queries - all return false
        DB::shouldReceive('table')
            ->with('user_global_roles')
            ->andReturnSelf();
        DB::shouldReceive('table')
            ->with('user_school_roles')
            ->andReturnSelf();
        DB::shouldReceive('table')
            ->with('user_season_roles')
            ->andReturnSelf();
        DB::shouldReceive('where')
            ->andReturnSelf();
        DB::shouldReceive('exists')
            ->andReturn(false);

        $request = Request::create('/test');
        $request->merge(['context_school_id' => 1, 'context_season_id' => 1]);
        
        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['test' => 'passed']);
        }, 'season.admin');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(403, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Insufficient permissions', $data['message']);
        $this->assertEquals('FORBIDDEN', $data['error_code']);
    }

    /** @test */
    public function it_handles_multiple_permissions()
    {
        $this->user->id = 1;
        
        Auth::shouldReceive('guard')
            ->with('api_v5')
            ->andReturnSelf();
        Auth::shouldReceive('user')
            ->andReturn($this->user);

        Log::shouldReceive('info')->andReturn(null);

        // Mock database queries - user has season.view but not season.admin
        DB::shouldReceive('table')
            ->with('user_global_roles')
            ->andReturnSelf();
        DB::shouldReceive('table')
            ->with('user_school_roles')
            ->andReturnSelf();
        DB::shouldReceive('table')
            ->with('user_season_roles')
            ->andReturnSelf();
        DB::shouldReceive('where')
            ->with('user_id', 1)
            ->andReturnSelf();
        DB::shouldReceive('where')
            ->with('school_id', 1)
            ->andReturnSelf();
        DB::shouldReceive('where')
            ->with('season_id', 1)
            ->andReturnSelf();
        DB::shouldReceive('where')
            ->with('role', 'superadmin')
            ->andReturnSelf();
        DB::shouldReceive('where')
            ->with('role', 'admin')
            ->andReturnSelf();
        DB::shouldReceive('where')
            ->with('role', 'viewer')
            ->andReturnSelf();
        DB::shouldReceive('exists')
            ->andReturn(false, false, false, false, false, true); // Only viewer role exists

        $request = Request::create('/test');
        $request->merge(['context_school_id' => 1, 'context_season_id' => 1]);
        
        // Should pass for view permission
        $response1 = $this->middleware->handle($request, function ($req) {
            return response()->json(['test' => 'passed']);
        }, 'season.view');

        $this->assertEquals(200, $response1->getStatusCode());

        // Should fail for admin permission
        $response2 = $this->middleware->handle($request, function ($req) {
            return response()->json(['test' => 'passed']);
        }, 'season.admin');

        $this->assertEquals(403, $response2->getStatusCode());
    }

    /** @test */
    public function it_validates_permission_constants()
    {
        // Test that permission constants are defined correctly
        $this->assertEquals('global.admin', ContextPermissionMiddleware::GLOBAL_ADMIN);
        $this->assertEquals('school.admin', ContextPermissionMiddleware::SCHOOL_ADMIN);
        $this->assertEquals('season.admin', ContextPermissionMiddleware::SEASON_ADMIN);
        $this->assertEquals('booking.create', ContextPermissionMiddleware::BOOKING_CREATE);
        $this->assertEquals('client.read', ContextPermissionMiddleware::CLIENT_READ);
        $this->assertEquals('monitor.create', ContextPermissionMiddleware::MONITOR_CREATE);
        $this->assertEquals('course.update', ContextPermissionMiddleware::COURSE_UPDATE);
    }

    /** @test */
    public function it_handles_missing_context()
    {
        $this->user->id = 1;
        
        Auth::shouldReceive('guard')
            ->with('api_v5')
            ->andReturnSelf();
        Auth::shouldReceive('user')
            ->andReturn($this->user);

        Log::shouldReceive('info')->andReturn(null);

        $request = Request::create('/test');
        // No context merged - should fail
        
        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['test' => 'passed']);
        }, 'season.admin');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('School or season context missing', $data['message']);
        $this->assertEquals('MISSING_CONTEXT', $data['error_code']);
    }

    protected function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }
}