<?php

namespace Tests\Unit\V5;

use Tests\TestCase;
use App\Http\Middleware\V5\ContextMiddleware;
use App\Models\User;
use App\Models\School;
use App\Models\Season;
use App\V5\Models\UserSeasonRole;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Mockery as m;

class ContextMiddlewareTest extends TestCase
{
    protected ContextMiddleware $middleware;
    protected User $user;
    protected School $school;
    protected Season $season;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->middleware = new ContextMiddleware();
        
        // Create test data without database
        $this->user = m::mock(User::class);
        $this->school = m::mock(School::class);
        $this->season = m::mock(Season::class);
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
        });

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(401, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Authentication required', $data['message']);
        $this->assertEquals('UNAUTHORIZED', $data['error_code']);
    }

    /** @test */
    public function it_requires_school_context()
    {
        $token = m::mock(PersonalAccessToken::class);
        $token->shouldReceive('getAttribute')->with('context_data')->andReturn('{}');
        
        $this->user->shouldReceive('currentAccessToken')->andReturn($token);
        $this->user->shouldReceive('hasRole')->with('superadmin')->andReturn(false);

        Auth::shouldReceive('guard')
            ->with('api_v5')
            ->andReturnSelf();
        Auth::shouldReceive('user')
            ->andReturn($this->user);

        $request = Request::create('/test');
        
        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['test' => 'passed']);
        });

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(403, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('School context is required', $data['message']);
        $this->assertEquals('FORBIDDEN', $data['error_code']);
    }

    /** @test */
    public function it_validates_school_exists()
    {
        $token = m::mock(PersonalAccessToken::class);
        $token->shouldReceive('getAttribute')->with('context_data')->andReturn('{"school_id": 999}');
        
        $this->user->shouldReceive('currentAccessToken')->andReturn($token);
        $this->user->shouldReceive('hasRole')->with('superadmin')->andReturn(false);

        Auth::shouldReceive('guard')
            ->with('api_v5')
            ->andReturnSelf();
        Auth::shouldReceive('user')
            ->andReturn($this->user);

        // Mock School::where() chain
        $schoolQuery = m::mock();
        $schoolQuery->shouldReceive('first')->andReturn(null);
        School::shouldReceive('where')->with('id', 999)->andReturn($schoolQuery);

        $request = Request::create('/test');
        
        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['test' => 'passed']);
        });

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(403, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('School not found or inactive', $data['message']);
    }

    /** @test */
    public function it_validates_user_school_access()
    {
        $token = m::mock(PersonalAccessToken::class);
        $token->shouldReceive('getAttribute')->with('context_data')->andReturn('{"school_id": 1}');
        
        $this->user->shouldReceive('currentAccessToken')->andReturn($token);
        $this->user->shouldReceive('hasRole')->with('superadmin')->andReturn(false);
        $this->user->shouldReceive('schools')->andReturnSelf();
        $this->user->shouldReceive('where')->with('schools.id', 1)->andReturnSelf();
        $this->user->shouldReceive('exists')->andReturn(false);
        $this->user->id = 1;

        $this->school->id = 1;
        $this->school->owner_id = 2; // Different user

        Auth::shouldReceive('guard')
            ->with('api_v5')
            ->andReturnSelf();
        Auth::shouldReceive('user')
            ->andReturn($this->user);

        // Mock School::where() chain
        $schoolQuery = m::mock();
        $schoolQuery->shouldReceive('first')->andReturn($this->school);
        School::shouldReceive('where')->with('id', 1)->andReturn($schoolQuery);

        $request = Request::create('/test');
        
        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['test' => 'passed']);
        });

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(403, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Access denied to this school', $data['message']);
    }

    /** @test */
    public function it_requires_season_context()
    {
        $token = m::mock(PersonalAccessToken::class);
        $token->shouldReceive('getAttribute')->with('context_data')->andReturn('{"school_id": 1}');
        $token->shouldReceive('getAttribute')->with('season_id')->andReturn(null);
        
        $this->user->shouldReceive('currentAccessToken')->andReturn($token);
        $this->user->shouldReceive('hasRole')->with('superadmin')->andReturn(false);
        $this->user->shouldReceive('schools')->andReturnSelf();
        $this->user->shouldReceive('where')->with('schools.id', 1)->andReturnSelf();
        $this->user->shouldReceive('exists')->andReturn(true);
        $this->user->id = 1;

        $this->school->id = 1;
        $this->school->name = 'Test School';

        Auth::shouldReceive('guard')
            ->with('api_v5')
            ->andReturnSelf();
        Auth::shouldReceive('user')
            ->andReturn($this->user);

        // Mock School::where() chain
        $schoolQuery = m::mock();
        $schoolQuery->shouldReceive('first')->andReturn($this->school);
        School::shouldReceive('where')->with('id', 1)->andReturn($schoolQuery);

        $request = Request::create('/test');
        
        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['test' => 'passed']);
        });

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(403, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Season context is required', $data['message']);
    }

    /** @test */
    public function it_passes_with_valid_context()
    {
        $token = m::mock(PersonalAccessToken::class);
        $token->shouldReceive('getAttribute')->with('context_data')->andReturn('{"school_id": 1, "season_id": 1}');
        $token->shouldReceive('getAttribute')->with('season_id')->andReturn(1);
        
        $this->user->shouldReceive('currentAccessToken')->andReturn($token);
        $this->user->shouldReceive('hasRole')->with('superadmin')->andReturn(false);
        $this->user->shouldReceive('schools')->andReturnSelf();
        $this->user->shouldReceive('where')->with('schools.id', 1)->andReturnSelf();
        $this->user->shouldReceive('exists')->andReturn(true);
        $this->user->id = 1;

        $this->school->id = 1;
        $this->school->name = 'Test School';
        $this->season->id = 1;
        $this->season->name = 'Test Season';

        Auth::shouldReceive('guard')
            ->with('api_v5')
            ->andReturnSelf();
        Auth::shouldReceive('user')
            ->andReturn($this->user);

        // Mock School::where() chain
        $schoolQuery = m::mock();
        $schoolQuery->shouldReceive('first')->andReturn($this->school);
        School::shouldReceive('where')->with('id', 1)->andReturn($schoolQuery);

        // Mock Season::where() chain
        $seasonQuery = m::mock();
        $seasonQuery->shouldReceive('where')->with('school_id', 1)->andReturnSelf();
        $seasonQuery->shouldReceive('where')->with('is_active', true)->andReturnSelf();
        $seasonQuery->shouldReceive('first')->andReturn($this->season);
        Season::shouldReceive('where')->with('id', 1)->andReturn($seasonQuery);

        // Mock UserSeasonRole::where() chain
        $userSeasonQuery = m::mock();
        $userSeasonQuery->shouldReceive('where')->with('season_id', 1)->andReturnSelf();
        $userSeasonQuery->shouldReceive('exists')->andReturn(true);
        UserSeasonRole::shouldReceive('where')->with('user_id', 1)->andReturn($userSeasonQuery);

        $request = Request::create('/test');
        
        $response = $this->middleware->handle($request, function ($req) {
            // Verify context was injected
            $this->assertEquals(1, $req->get('context_school_id'));
            $this->assertEquals(1, $req->get('context_season_id'));
            $this->assertEquals($this->school, $req->get('context_school'));
            $this->assertEquals($this->season, $req->get('context_season'));
            
            return response()->json(['test' => 'passed']);
        });

        $this->assertEquals(200, $response->getStatusCode());
        
        // Verify headers were added
        $this->assertEquals('1', $response->headers->get('X-School-Context'));
        $this->assertEquals('Test School', $response->headers->get('X-School-Name'));
        $this->assertEquals('1', $response->headers->get('X-Season-Context'));
        $this->assertEquals('Test Season', $response->headers->get('X-Season-Name'));
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('passed', $data['test']);
    }

    /** @test */
    public function it_allows_superadmin_access()
    {
        $token = m::mock(PersonalAccessToken::class);
        $token->shouldReceive('getAttribute')->with('context_data')->andReturn('{"school_id": 1, "season_id": 1}');
        $token->shouldReceive('getAttribute')->with('season_id')->andReturn(1);
        
        $this->user->shouldReceive('currentAccessToken')->andReturn($token);
        $this->user->shouldReceive('hasRole')->with('superadmin')->andReturn(true);
        $this->user->id = 1;

        $this->school->id = 1;
        $this->school->name = 'Test School';
        $this->season->id = 1;
        $this->season->name = 'Test Season';

        Auth::shouldReceive('guard')
            ->with('api_v5')
            ->andReturnSelf();
        Auth::shouldReceive('user')
            ->andReturn($this->user);

        // Mock School::where() chain
        $schoolQuery = m::mock();
        $schoolQuery->shouldReceive('first')->andReturn($this->school);
        School::shouldReceive('where')->with('id', 1)->andReturn($schoolQuery);

        // Mock Season::where() chain
        $seasonQuery = m::mock();
        $seasonQuery->shouldReceive('where')->with('school_id', 1)->andReturnSelf();
        $seasonQuery->shouldReceive('where')->with('is_active', true)->andReturnSelf();
        $seasonQuery->shouldReceive('first')->andReturn($this->season);
        Season::shouldReceive('where')->with('id', 1)->andReturn($seasonQuery);

        $request = Request::create('/test');
        
        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['test' => 'passed']);
        });

        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('passed', $data['test']);
    }

    /** @test */
    public function it_extracts_school_id_from_headers()
    {
        $token = m::mock(PersonalAccessToken::class);
        $token->shouldReceive('getAttribute')->with('context_data')->andReturn('{}');
        $token->shouldReceive('getAttribute')->with('season_id')->andReturn(1);
        
        $this->user->shouldReceive('currentAccessToken')->andReturn($token);
        $this->user->shouldReceive('hasRole')->with('superadmin')->andReturn(false);
        $this->user->shouldReceive('schools')->andReturnSelf();
        $this->user->shouldReceive('where')->with('schools.id', 1)->andReturnSelf();
        $this->user->shouldReceive('exists')->andReturn(true);
        $this->user->id = 1;

        $this->school->id = 1;
        $this->school->name = 'Test School';
        $this->season->id = 1;
        $this->season->name = 'Test Season';

        Auth::shouldReceive('guard')
            ->with('api_v5')
            ->andReturnSelf();
        Auth::shouldReceive('user')
            ->andReturn($this->user);

        // Mock School::where() chain
        $schoolQuery = m::mock();
        $schoolQuery->shouldReceive('first')->andReturn($this->school);
        School::shouldReceive('where')->with('id', 1)->andReturn($schoolQuery);

        // Mock Season::where() chain
        $seasonQuery = m::mock();
        $seasonQuery->shouldReceive('where')->with('school_id', 1)->andReturnSelf();
        $seasonQuery->shouldReceive('where')->with('is_active', true)->andReturnSelf();
        $seasonQuery->shouldReceive('first')->andReturn($this->season);
        Season::shouldReceive('where')->with('id', 1)->andReturn($seasonQuery);

        // Mock UserSeasonRole::where() chain
        $userSeasonQuery = m::mock();
        $userSeasonQuery->shouldReceive('where')->with('season_id', 1)->andReturnSelf();
        $userSeasonQuery->shouldReceive('exists')->andReturn(true);
        UserSeasonRole::shouldReceive('where')->with('user_id', 1)->andReturn($userSeasonQuery);

        $request = Request::create('/test');
        $request->headers->set('X-School-ID', '1');
        
        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['test' => 'passed']);
        });

        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('passed', $data['test']);
    }

    protected function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }
}