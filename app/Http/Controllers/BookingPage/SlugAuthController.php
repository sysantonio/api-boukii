<?php

namespace App\Http\Controllers\BookingPage;

use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request;

class SlugAuthController extends AppBaseController
{

    protected $school;

    function __construct(Request $request)
    {
        $this->middleware(function($request, $next) {

            // Assignment to use in controllers that extend this
            $this->school = $request->attributes->get('school');

            return $next($request);

        });
    }

}
