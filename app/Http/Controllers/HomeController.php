<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Course;
use View;
use Auth;
use Session;
use Hash;
use Illuminate\Support\Facades\Input;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('verified');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        if($user->instructor == 1)
            return redirect('instructor/home')->with('status',Session::get('status'));

        $view = View::make('home');
        $courses = $user->courses->where('active',1);
        $view->courses = $courses;
        $view->user = $user;
        return $view;
    }

    public function instructor_index() {
        $user = Auth::user();

        $view = View::make('instructor.home');
        $courses = $user->courses;
        $active = $courses->where('active',1);
        $inactive = $courses->where('active',0);

        $data = array(
            'active' => $active,
            'inactive' => $inactive,
            'user_id' => $user->id,
        );
        $view->data = json_encode($data);
        $view->courses = $courses;
        $view->user = $user;
        return $view;
    }

    public function showChangePasswordForm(){
        $view = View::make('auth.passwords.changePassword');
        return $view;
    }

    public function changePassword(Request $request){
        if (!(Hash::check($request->get('current-password'), Auth::user()->password))) {
            // The passwords matches
            return redirect()->back()->with("error","Your current password does not match the password you provided. Please try again.");
        }
        if(strcmp($request->get('current-password'), $request->get('new-password')) == 0){
            //Current password and new password are same
            return redirect()->back()->with("error","New Password cannot be same as your current password. Please choose a different password.");
        }
        $validatedData = $request->validate([
            'current-password' => 'required',
            'new-password' => 'required|string|min:6|confirmed',
        ]);
        //Change Password
        $user = Auth::user();
        $user->password = bcrypt($request->get('new-password'));
        $user->save();
        return redirect()->back()->with("success","Password changed successfully !");
    }

    public function changeName() {
        $firstname = Input::get('new_firstname');
        $lastname = Input::get('new_lastname');
        $user = Auth::user();

        if(strlen($firstname)==0 || strlen($lastname)== 0)
            return redirect()->back()->with("error","You cannot leave either name field blank.");

        $user->firstname = $firstname;
        $user->lastname = $lastname;
        $user->save();

        return redirect()->back()->with('success','Name updated successfully to '.$user->firstname.' '.$user->lastname.'.');
    }
}
