<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

use App\Models\School;

class BookingPage
{

    /**
     * Check if request contains the "slug" of an active School, or goodbye.
     *
     * @param Request $request
     */
    public function handle(Request $request, Closure $next)
    {

        $slug = trim($request->headers->get('slug', ''));

        $maybeSchool = (strlen($slug) > 0) ? School::where('slug', $slug)->where('Active', 1)->first() : null;

        if(!$maybeSchool) {
            return response( 'Wrong slug, no school', 404);
        }

        $request->attributes->set('school', $maybeSchool);

        return $next($request);

    }

}
