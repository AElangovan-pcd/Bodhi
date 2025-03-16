<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use View;
use Auth;
use App\Course;
use App\Hand;
use App\User;

class HandsController extends Controller
{
    public function hands_for_course($course_id){
        $view = View::make('instructor.SubViews.HandsForCourse');

        $course = Course::findOrFail($course_id);

        $view->hands = $course->hands->sortBy(function($hand) {
            return $hand->created_at;
        });
        return $view;
    }

    public function raise_hand($course_id) {
        $user = Auth::user();
        $hand = Hand::where('course_id', '=', $course_id)
            ->where('user_id', '=', $user->id)->first();

        if ($hand != null) {
            return "Your hand is already up.";
        }

        $hand = new Hand;
        $hand->user_id = $user->id;
        $hand->course_id = $course_id;
        $hand->save();

        //Broadcast the event on the private course channel
        $course = \App\Course::find($course_id);
        broadcast(new \App\Events\HandRaised($user, $course));

        return "...";
    }

    public function dismiss_hand($course_id, $user_id) {
        $hand = Hand::where('course_id', '=', $course_id)
            ->where('user_id', '=', $user_id)->first();
        $hands = Hand::where('course_id','=', $course_id)->get();
        if ($hand != null) {
            $hand->delete();
            $course=Course::find($course_id);
            broadcast(new \App\Events\HandLowered($user_id, $course, $hands));
        }
    }

    public function lower_hand($course_id) {
        $user_id = Auth::id();
        $this->dismiss_hand($course_id,$user_id);
    }

    public function place_in_line($course_id) {
        $user = Auth::user();

        $my_hand = Hand::where('course_id', '=', $course_id)->where('user_id', '=', $user->id)->first();

        if ($my_hand == null)  //Hand is not raised.
            return "0";

        $course = Course::findorfail($course_id);

        $hands = $course->hands->sortBy(function($hand) {
            return $hand->created_at;
        });

        $position = 0;

        foreach($hands as $i => $hand) {
            if ($hand == $my_hand)
                $position = $i + 1;
        }

        if ($position == 0)
            return 0;

        if ($position % 10 == 1)
            return $position."st";
        else if ($position % 10 == 2)
            return $position."nd";
        else if ($position % 10 == 3)
            return $position."rd";
        else
            return $position."th";
    }
}
