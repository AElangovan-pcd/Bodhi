<?php

namespace App\Http\Middleware;

use Closure;
use App\Course;
use Auth;

class CourseAssistantEdit
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
        if(!$course->assistant_privs['edit'] && Auth::user()->instructor != 1)
            abort(403,'Unauthorized action.');
        return $next($request);
    }
}
