<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use View;
use App\User;
use App\Course;
use Auth;
use Redirect;
use Illuminate\Support\Facades\Input;
use Hash;

class AdminController extends Controller
{
    public function manage_instructors() {
        $view = View::make('admin.manageInstructors');
        $instructors = User::where('instructor','1')->get();
        $data = array(
            'instructors' => $instructors,
        );
        $view->data = json_encode($data);
        $view->user = Auth::user();
        return $view;
    }

    public function manage_courses() {
        $view = View::make('admin.manageCourses');
        $courses = Course::with('owner')->get();
        $data = array(
            'courses' => $courses,
        );
        $view->data = json_encode($data);
        $view->user = Auth::user();
        return $view;
    }

    public function add_instructor() {
        $email = Input::get('email');
        $user = User::where('email',$email)->first();
        if($user == null)
            return Redirect::back()->with('error','No user with email '.$email.' found.');
        $user->instructor = 1;
        $user->save();
        return Redirect::back()->with('status','User '.$email.' promoted to instructor.');
    }

    public function revoke_instructor($id) {
        $user = User::find($id);
        $user->instructor = 0;
        $user->save();
        return Redirect::back()->with('status','User '.$user->email.' instructor privileges revoked.');
    }

    public function manage_students() {
        $view = View::make('instructor.manageStudents');
        $alphas = ["All"];

        for ($i= 0; $i < 26; $i++) {
            $alphas[] = chr(ord('A') + $i);
        }

        $view->data = json_encode(
            Array(
                "alpha_section" => $alphas
            )
        );

        return $view;
    }

    public function manage_students_select() {
        $selection = Input::get("selection");

        if ($selection === "All") {
            $students = User::where("instructor",0)->orderBy("lastname")->get();
        }
        else {
            $students = User::where("lastname", "LIKE", $selection."%")->where("instructor",0)->orderBy("lastname")->get();
        }

        return json_encode($students);
    }

    public function reset_student_password($sid) {
        $user = User::find($sid);
        if($user==null)
            return Redirect::back()->with('error','Cannot find student.');
        $password = str_random(5);
        $user->password = Hash::make($password);
        $user->save();
        return Redirect::back()->with('status','Please inform student '.$user->firstname .' '. $user->lastname.' of the new password: '.$password);

    }
}
