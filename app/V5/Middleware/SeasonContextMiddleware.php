<?php

namespace App\V5\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\V5\Modules\Season\Services\SeasonService;

class SeasonContextMiddleware
{
    protected SeasonService $seasons;

    public function __construct(SeasonService $seasons)
    {
        $this->seasons = $seasons;
    }

    public function handle(Request $request, Closure $next)
    {
        if (!$request->has('season_id') && $request->has('school_id')) {
            $season = $this->seasons->getCurrentSeason($request->get('school_id'));
            if ($season) {
                $request->merge(['season_id' => $season->id]);
            }
        }
        return $next($request);
    }
}
