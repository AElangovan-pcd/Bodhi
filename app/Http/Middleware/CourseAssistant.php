<?php

namespace App\Http\Middleware;

use Closure;
use App\Course;
use Auth;

class CourseAssistant
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
        $course = Course::find($request->cid);
        if (!in_array(Auth::user()->id, $course->assistants()) && Auth::user()->instructor != 1)
            abort(403, 'Unauthorized action.');
        return $next($request);
    }
}
