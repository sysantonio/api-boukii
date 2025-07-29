<?php

namespace Tests\Unit;

use App\V5\Guards\SeasonPermissionGuard;
use App\V5\Modules\Auth\Services\AuthV5Service;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SeasonPermissionGuardTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_request_is_allowed_when_permissions_exist(): void
    {
        $service = Mockery::mock(AuthV5Service::class);
        $service->shouldReceive('checkSeasonPermissions')
            ->once()
            ->with(1, 5)
            ->andReturn(['view schools']);

        $guard = new SeasonPermissionGuard($service);

        $request = Request::create('/test', 'GET', ['season_id' => 5]);
        $user = new \stdClass();
        $user->id = 1;
        $request->setUserResolver(fn () => $user);

        $response = $guard->handle($request, function () {
            return response('ok', 200);
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_request_is_denied_when_no_permissions(): void
    {
        $service = Mockery::mock(AuthV5Service::class);
        $service->shouldReceive('checkSeasonPermissions')
            ->once()
            ->with(1, 5)
            ->andReturn([]);

        $guard = new SeasonPermissionGuard($service);

        $request = Request::create('/test', 'GET', ['season_id' => 5]);
        $user = new \stdClass();
        $user->id = 1;
        $request->setUserResolver(fn () => $user);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Forbidden');
        $guard->handle($request, function () {
            return response('ok', 200);
        });
    }
}
