<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use App\Models\Language;

class UserRequired
{

    private function validator($user_type = null)
    {

        $validator = Validator::make([
            'user_type' => $user_type
        ], [
            'user_type' => ['nullable'],
        ]);

        $validator->after(function($validator) use ($user_type) {

            if($validator->errors()->isEmpty()) {

                if(!Auth::check()) {
                    return $validator->errors()->add('auth', 'Unauthorized');
                }

                // Validate user
                if(!Auth::user()->validate($validator, (is_null($user_type) ? [] : [$user_type]))->isEmpty()) {
                    //Auth::logout();
                }

            }

        });

        if($validator->fails()) {
            return response()->api(null, $validator->errors()->first(), 401)->throwResponse();
        }

        return $validator;

    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $user_type = null)
    {
        $this->validator($user_type);

        // Set current user's language for messages
        $defaultLocale = config('app.fallback_locale');
        $myUser = \Auth::user();
        $userLang = ($myUser->language1_id) ? Language::find( $myUser->language1_id ) : null;
        $userLocale = $userLang ? $userLang->code : $defaultLocale;
        \App::setLocale($userLocale);

        return $next($request);
    }
}
