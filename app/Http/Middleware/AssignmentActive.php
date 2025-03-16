<?php

namespace App\Http\Middleware;

use Closure;
use App\Assignment;
use App\Course;
use Auth;

class AssignmentActive
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
        $assignment = Assignment::select('active','course_id')->find($request->aid);
        $course = Course::find($request->cid);
        if ($assignment->course_id != $request->cid)  //Make sure assignment belongs to this course.
            abort(404, 'Assignment not in this course');
        if (!$assignment->active && Auth::user()->instructor != 1 && !in_array(Auth::user()->id, $course->assistants()))
            abort(403, 'Unauthorized action.');
        return $next($request);
    }
}
