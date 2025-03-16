<?php

namespace App\Http\Middleware;

use Closure;
use App\Forum;

class CourseForum
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
        if (Forum::select('course_id')->find($request->fid)->course_id != $request->cid)  //Make sure assignment belongs to this course.
            abort(404, 'Forum not in this course');
        return $next($request);
    }
}
