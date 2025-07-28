<?php

namespace App\V5\Guards;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\V5\Modules\Auth\Services\AuthV5Service;

class SeasonPermissionGuard
{
    protected AuthV5Service $auth;

    public function __construct(AuthV5Service $auth)
    {
        $this->auth = $auth;
    }

    public function handle(Request $request, \Closure $next): Response
    {
        $userId = $request->user()->id ?? 0;
        $seasonId = (int) $request->get('season_id');
        $permissions = $this->auth->checkSeasonPermissions($userId, $seasonId);
        // TODO: check actual permission list
        return $next($request);
    }
}
