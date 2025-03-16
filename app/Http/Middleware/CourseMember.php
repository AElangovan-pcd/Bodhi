<?php

namespace App\Http\Middleware;

use Closure;
use \App\Course;
use Auth;

class CourseMember
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
        $course = Course::select('id','active')->find($request->cid);
        $user = Auth::user();
        if($course == null || (!$course->active && !$user->instructor && $user->admin != 1))
            return redirect('/')->with('error','Course not found.');

        if (!$user->courses->contains($request->cid) && $user->admin != 1)
            return redirect('/join/'.$request->cid);


        return $next($request);
    }
}
