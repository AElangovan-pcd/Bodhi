<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class Instructor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if (Auth::user()->instructor != 1)
        {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
