<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use View;
use Auth;
use App\Course;
use App\Assignment;
use Illuminate\Support\Facades\Input;
use Redirect;
use App\Seat;
use Illuminate\Support\Facades\Storage;
use App\Classroom;
use App\Poll;
use App\User;
use App\Schedule;
use Exception;
use Hash;
use Log;
use Carbon\Carbon;
use App\GradeGroup;

class CourseController extends Controller
{
    public function check_schedules() {
        $tasks = Schedule::where('enabled',1)->where('completed',0)->where('time','<=', Carbon::now())->get();
        foreach($tasks as $task) {
            if ($task->type == "info") {
                InfoController::run_task($task);
            }
            elseif($task->type == "assignment") {
                AssignmentController::run_task($task);
            }
            else {
                $task->completed = 1;
                $task->save();
                Log::debug('Task '.$task->id.'has unrecognized type. Marked as complete.');
            }
        }
    }

    //TODO Refactor student and course landing functions to use a single function, pointing to views and passing data conditionally
    //TODO Refactor to use more functions within

    public function merged_landing_student($cid) {
        return $this->merged_landing($cid);
    }

    public function merged_landing_instructor($cid) {
        return $this->merged_landing($cid, true);
    }

    private function merged_landing($cid, $instructor = false) {
        $user = Auth::user();

        $course = Course::
            when($instructor, function($query) use ($user) { //Instructor
                $query->with(['assignments' => function ($q) use($user) {
                    $q//->where('active',1)
                    ->with('linked_parent_assignment.course')
                    ->withCount('linked_assignments')

                    ->withCount('ungraded_results')
                    ->withCount('deferred_results')

                    //Progress Tracking
                    ->withCount(['results' => function($q) use($user) {
                        return $q->where('user_id',$user->id);}])
                    ->withCount(['questions' => function($q) {
                        return $q->where('type','!=',4);
                    }])
                    ->with(['results' => function($q) use($user) {
                        return $q->select('results.id','user_id','question_id','earned','status')
                            ->where('user_id',$user->id)
                            ;}])
                    ->with(['questions:questions.id,assignment_id,max_points']);
                }])
                    //End Progress Tracking

                    ->with(['inactive_assignments' => function ($q) {
                        $q->with('linked_parent_assignment.course')
                            ->withCount('linked_assignments');
                    }])
                    ->with('users')
                    ->with('seats')
                    ->with('linked_parent_course','linked_courses','link_requests_outgoing.parent_course','link_requests_incoming.child_course')
                ;
            }, function($query) use($user) {  //Student
                $query->with(['assignments' => function ($q) use($user) {
                    $q->where('active',1)->select('id','course_id','active','order','name','type','disabled','closes_at')
                        //Progress Tracking
                        ->withCount(['results' => function($q) use($user) {
                            return $q->where('user_id',$user->id);}])
                        ->withCount(['questions' => function($q) {
                            return $q->where('type','!=',4);
                        }])
                        ->with(['results' => function($q) use($user) {
                            return $q->select('results.id','user_id','question_id','earned','status')
                                ->where('user_id',$user->id);
                                ;}])
                        ->with(['questions:questions.id,assignment_id,max_points'])
                        ->with(['extension' => function ($q) use ($user) {
                            return $q->where('user_id', $user->id);
                        }])
                    ;
                }]);
                    //End Progress Tracking

            })
            //All users
            ->with(['reviews' => function($q) {
                $q->whereRaw('state > 0')
                    ->select('id','course_id','name','state');
            }])
            ->with(['folders' => function($q) {
                $q->where('visible',true)
                    ->with(['course_files' => function($query) {
                        return $query->where('visible',true)->select(['id','name','folder_id','extension'])->orderBy('order');
                    }])->select(['id','course_id','name','order'])->orderBy('order');
            }])
            ->with(['linked_folders' => function($q) { $q->where('visible',1);}])
            ->with(['infos' => function($q) {
                $q->where('visible',true)->orderBy('order')
                    //->whereHas('info_quizzes',function($q) {$q->where('closed','>=', Carbon::now());})
                    ->with(['info_quizzes' => function($query) {
                        $query->where('visible',1)
                            ->select(['id','info_id','description','order','state','closed'])
                            ->orderBy('order')
                            ->with(['info_quiz_questions' => function($query2) {
                                $query2->select(['id','info_quiz_id','description','choices','type','order','points','answer'])->orderBy('order')
                                    ->with(['info_quiz_answers' => function($query3) {
                                        $query3->where('user_id',Auth::id())->orderBy('CREATED_AT', 'desc');
                                    }]);
                            }]);
                    }]);
            }])
            ->findOrFail($cid);


        foreach($course->assignments as $key => $assignment) {
            //Don't report scores for quizzes
            if($assignment->type == 2) {
                unset($assignment->results);
                unset($assignment->questions);
                unset($assignment->questions_count);
            }
            else {
                //Drop results that are deferred
                foreach($assignment->results as $key => $result) {
                    if($result->status == 3) {
                        unset($result->earned);
                    }
                }
            }
        }

        $course->assignments = $course->assignments->each(function($assignment, $key) use ($course) {
            if($assignment->type == 2) {
                $assignment->results = [];
                $course->assignments[$key] = $assignment;
            }
            else {
                $assignment->results = $assignment->results->filter(function ($result, $key2) {
                    return $result->status == 1 || $result->status == null;
                });
            }
        });


        //Merge parent files with files
        $course->folders = $course->linked_folders->merge($course->folders);

        //Lists of files and assignments for linking
        $fileList = $this->build_list($course->folders->pluck('course_files')->collapse());
        $assignmentList = $this->build_list($course->assignments);
        $reviewList = $this->build_list($course->reviews);

        //Refactor into model, possibly using accessor for eager loading above
        $course->infos = $this->closed_info_quizzes($course->infos);

        //TODO move action into something done via eager loaded info passed to view
        foreach($course->reviews as $review) {
            $review->action = ReviewController::review_action($review);
        }

        //TODO refactor
        $forum = ForumController::forum_details($cid);

        //TODO refactor
        $hasGrades = false;
        if(GradeGroup::where('course_id',$cid)->where('visible',1)->count() > 0)
            $hasGrades = true;

        //TODO Refactor to use a single view page with conditionals in blade to avoid having to duplicate updates
        $view = $instructor ? View::make('instructor.course.landing') : View::make('student.course.landing');
        $view->course = $course;
        $view->user = $user;

        $data = array(
            'reviews'   => $course->reviews,
            'forum'    => $forum,
            'folders'  => $course->folders,
            'infos'    => $course->infos,
            'fileList' => $fileList,
            'assignmentList' => $assignmentList,
            'reviewList' => $reviewList,
            'hasGrades' => $hasGrades,
            'loadedTime' => Carbon::now()->toDateTimeString(),
        );

        if($instructor) {
            $linkableCourses = Course::where('linkable',1)->where('id',"!=",$cid)->where('active',1)->get();
            $level_data = [
                'course' => $course,

                'assignments' => $course->assignments,

                'students' => $course->users,
                'linkableCourses' => $linkableCourses,
            ];
        }
        else {
            $LA = in_array(Auth::user()->id, $course->assistants());
            $LA_privs = $course->assistant_privs;
            if($LA && $LA_privs['edit'])
                $data['inactive'] = $course->inactive_assignments; //TODO Refactor

            $level_data = [
                'course' => $course->only('id','description','progress_display'),
                'assignments' => $course->assignments,
                'LA'        => $LA,
                'LA_privs'   => $LA_privs,
            ];
        }
        $data = array_merge($data, $level_data);

        $view->data = json_encode($data);

        return $view;
    }

    private function build_list($collection) {
        return $collection->map(function($item, $key) {
            return [$item->name => $item->id];
        })->collapse();
    }

    private function closed_info_quizzes($infos) {
        foreach($infos as $info) {
            foreach($info->info_quizzes as $quiz) {
                if($quiz->closed >= Carbon::now()) {
                    foreach($quiz->info_quiz_questions as $q) {
                        unset($q->answer);
                        foreach($q->info_quiz_answers as $a) {
                            unset($a->earned);
                        }
                    }
                }

            }
        }
        return $infos;
    }

    //Student course landing page
    public function landing($id) {
        $view = View::make('student.course.landing');
        $course = Course::findOrFail($id);
        $view->course = $course;
        $assignments = $course->assignments()
            ->where('active',1)
            ->orderBy('order')
            ->select('id','active','order','name','type')
            ->get();
        /*$assignments = $course->assignments()->where('active',1)->orderBy('order')
            ->with(['results' => function($q) {
                $q->where('user_id',Auth::id());
            }])
            ->get(); */
        $reviews = $course->reviews()->whereRaw('state > 0')->get();
        $inactive = $course->assignments()->where('active',0)->orderBy('order')->get();

        foreach($reviews as $review) {
            $review->action = ReviewController::review_action($review);
        }

        $assignments->transform(function($i) {  //Remove info the student shouldn't get
            unset($i->versions);
            unset($i->creator_id);
            return $i;
        });

        $forum = ForumController::forum_details($id);

        $folders = $course->folders()->where('visible',true)
            ->with(['course_files' => function($query) {
                return $query->where('visible',true)->select(['id','name','folder_id','extension'])->orderBy('order');
            }])->select(['id','name'])->orderBy('order')->get();

        $infos = $course->infos()->where('visible',true)->orderBy('order')
            //->whereHas('info_quizzes',function($q) {$q->where('closed','>=', Carbon::now());})
            ->with(['info_quizzes' => function($query) {
                $query->where('visible',1)
                    ->select(['id','info_id','description','order','state','closed'])
                    ->orderBy('order')
                    ->with(['info_quiz_questions' => function($query2) {
                        $query2->select(['id','info_quiz_id','description','choices','type','order','points','answer'])->orderBy('order')
                            ->with(['info_quiz_answers' => function($query3) {
                                $query3->where('user_id',Auth::id())->orderBy('CREATED_AT', 'desc');
                            }]);
                    }]);
            }])
            ->get();

        foreach($infos as $info) {
            foreach($info->info_quizzes as $quiz) {
                if($quiz->closed >= Carbon::now()) {
                    foreach($quiz->info_quiz_questions as $q) {
                        unset($q->answer);
                        foreach($q->info_quiz_answers as $a) {
                            unset($a->earned);
                        }
                    }
                }

            }
        }

        $fileList = [];
        foreach($folders as $folder) {
            foreach($folder->course_files as $file) {
                $fileList[$file->name] = $file->id;
            }
        }

        $assignmentList = [];
        foreach($assignments as $assignment) {
            $assignmentList[$assignment->name] = $assignment->id;
        }

        $reviewList = [];
        foreach($reviews as $review) {
            $reviewList[$review->name] = $review->id;
        }

        $hasGrades = false;
        if(GradeGroup::where('course_id',$id)->where('visible',1)->count() > 0)
            $hasGrades = true;

        $LA = in_array(Auth::user()->id, $course->assistants());
        $LA_privs = $course->assistant_privs;

        $view->user = Auth::user();
        $view->course = $course;
        $data = array(
            'assignments' => $assignments,
            'forum'    => $forum,
            'reviews'   => $reviews,
            'LA'        => $LA,
            'LA_privs'   => $LA_privs,
            'folders'   => $folders,
            'infos'     => $infos,
            'fileList'  => $fileList,
            'assignmentList' => $assignmentList,
            'reviewList' => $reviewList,
            'hasGrades' => $hasGrades,
        );
        if($LA && $LA_privs['edit'])
            $data['inactive'] = $assignments = $inactive;

        $view->data = json_encode($data);
        return $view;
    }

    //Instructor course landing page
    public function instructor_landing($id) {
        $view = View::make('instructor.course.landing');
        $course = Course::findOrFail($id);
        $view->course = $course;
        $assignments = $course->assignments()
            ->withCount(['results as ungraded_written' => function($q) {
                $q->where('status',0);
            }])->get();

        $active = $assignments->where('active',1);
        $inactive = $assignments->where('active',0);

        $students = $course->users;//->reject(function($s) { return $s->instructor; });

        $reviews = $course->reviews()->whereRaw('state > 0')->get();

        foreach($students as $student) {
            $student->seat = $student->seat_for_course($id);  //TODO Check on eager loading as part of pivot
        }

        $forum = ForumController::forum_details($id);

        $folders = $course->folders()->where('visible',true)
            ->with(['course_files' => function($query) {
                return $query->where('visible',true)->select(['id','name','folder_id','extension'])->orderBy('order');
            }])->select(['id','name'])->orderBy('order')->get();

        $fileList = [];
        foreach($folders as $folder) {
            foreach($folder->course_files as $file) {
                $fileList[$file->name] = $file->id;
            }
        }

        $assignmentList = [];
        foreach($active as $assignment) {
            $assignmentList[$assignment->name] = $assignment->id;
        }

        $reviewList = [];
        foreach($reviews as $review) {
            $reviewList[$review->name] = $review->id;
        }

        $infos = $course->infos()->where('visible',true)->orderBy('order')
            //->whereHas('info_quizzes',function($q) {$q->where('closed','>=', Carbon::now());})
            ->with(['info_quizzes' => function($query) {
                $query->where('visible',1)
                    ->select(['id','info_id','description','order','state','closed'])
                    ->orderBy('order')
                    ->with(['info_quiz_questions' => function($query2) {
                        $query2->select(['id','info_quiz_id','description','choices','type','order','points','answer'])->orderBy('order')
                            ->with(['info_quiz_answers' => function($query3) {
                                $query3->where('user_id',Auth::id())->orderBy('CREATED_AT', 'desc');
                            }]);
                    }]);
            }])
            ->get();

        foreach($infos as $info) {
            foreach($info->info_quizzes as $quiz) {
                if($quiz->closed >= Carbon::now()) {
                    foreach($quiz->info_quiz_questions as $q) {
                        unset($q->answer);
                        foreach($q->info_quiz_answers as $a) {
                            unset($a->earned);
                        }
                    }
                }

            }
        }

        $data = array(
            'course' => $course,
            'active' => $active,
            'inactive' => $inactive,
            'students' => $students,
            'forum'    => $forum,
            'folders'  => $folders,
            'infos'    => $infos,
            'fileList' => $fileList,
            'assignmentList' => $assignmentList,
            'reviewList' => $reviewList,
        );
        $view->data = json_encode($data);
        return $view;
    }

    public function assignment_list($cid) {
        $course = Course::find($cid);
        $assignments = Assignment::where('course_id',$course->id)->withCount('linked_assignments')->orderBy('order')->get();

        $view = View::make('instructor.assignments.assignments');
        $data = array(
            'course' => $course,
            'assignments' => $assignments,
        );
        $view->course = $course;
        $view->data = json_encode($data);
        return $view;
    }

    public function save_assignment_list($cid, Request $request) {
        $assignments = json_decode($request->input('assignments'));
        \Debugbar::info($assignments);
        try {
            foreach ($assignments as $a) {
                Assignment::find($a->id)->update(["name" => $a->name, "closes_at" => $a->closes_at]);
            }
            return ['status' => 'success', 'assignments' => Assignment::where('course_id', $cid)->orderBy('order')->get()];
        }
        catch(Exception $e) {
            return ['status' => 'error', 'error' => $e->getMessage()];
        }
    }

    public function update_details($cid, Request $request) {
        try {
            $course = Course::find($cid);
            $updates = json_decode($request->input('course'));
            $course->name = $updates->name;
            $course->key = $updates->key;
            $course->progress_display = $updates->progress_display ?? false;
            if(isset($updates->new_description)) {
                if ($updates->new_description == null || $updates->new_description == '<p><br></p>' || $updates->new_description == '') {
                    $course->description = null;
                } else {
                    $course->description = $updates->new_description;
                }
            }
            $course->save();
            return ['status' => 'success', 'course' => $course];
        }
        catch(\Exception $e) {
            report($e);
            return ['status' => 'error', 'msg' => $e->getMessage()];
        }

    }

    public function activate_course($id) {
        $course = Course::find($id);
        $course->active = 1;
        $course->save();
        return Redirect::back()->with('status','Course activated');
    }

    public function deactivate_course($id) {
        $course = Course::find($id);
        $course->active = 0;
        $course->save();
        return Redirect::back()->with('status','Course deactivated');
    }

    public function archive($id) {
        Course::where('id', $id)->update(['archived' => 1]);
        return Redirect::back()->with('status','Course archived');
    }

    public function unArchive($id) {
        Course::where('id', $id)->update(['archived' => 0]);
        return Redirect::back()->with('status','Course unArchived');
    }

    public function join_courses_page() {
        $view = View::make('joinCourses');
        $courseByOwner = Course::coursesByOwnerLastName();
        $alphabet = [];
        for ($i= 0; $i < 26; $i++) {
            $alphabet[] = chr(ord('A') + $i);
        }
        $view->alpha_list = $courseByOwner["list"];
        $view->alphas = $courseByOwner["alphas"];
        $view->alphabet = $alphabet;
        return $view;
    }

    public function join_course_page($cid) {
        $view = View::make('joinCourse');
        $course = Course::find($cid);
        $view->course = $course;
        return $view;
    }

    public function join_course($cid) {
        $key = Input::get('key');
        $seat = Input::get('seat');
        $course = Course::find($cid);
        if(!$course->active)
            abort(403,'Course inactive.  You are unable to join.');
        if($key != $course->key)
            return Redirect::back()->with('error',"Invalid course key");
        $user = Auth::user();
        $user->courses()->syncWithoutDetaching([$course->id => ['seat' => $seat]]);
        return Redirect::to('home')->with('status',"Successfully joined course.");
    }

    public function change_seat($cid) {
        $seat = Input::get('new_seat');
        $user = Auth::user();
        $user->courses()->syncWithoutDetaching([$cid => ['seat' => $seat]]);
        return Redirect::back()->with('status','Seat changed.');
    }

    public function change_student_seat($cid, $sid) {
        $seat = Input::get('seat');
        $user = User::find($sid);
        $user->courses()->syncWithoutDetaching([$cid => ['seat' => $seat]]);

        return Redirect::back()->with('status','Seat changed.');
    }

    public function change_student_multiplier($cid, $sid) {
        $multiplier = Input::get('multiplier');
        $user = User::find($sid);
        $user->courses()->syncWithoutDetaching([$cid => ['multiplier' => $multiplier]]);

        return Redirect::back()->with('status','Multiplier changed.');
    }

    public function reset_student_password($cid, $sid) {
        $user = User::find($sid);
        if(!$user->courses->contains($cid))
            return Redirect::back()->with('error','Invalid student/course combination.');
        $password = str_random(5);
        $user->password = Hash::make($password);
        $user->save();
        return Redirect::back()->with('status','Please inform student '.$user->firstname .' '. $user->lastname.' of the new password: '.$password);

    }

    public function drop_student($cid,$sid) {
        $user = User::find($sid);
        $user->courses()->detach($cid);
        return Redirect::to('instructor/course/'.$cid.'/landing')->with('status',"Dropped ".$user->firstname." ".$user->lastname." from course.");
    }

    public function manage_assistants($cid) {
        $view = View::make('instructor.course.manageAssistants');
        $course = Course::find($cid);
        $assistant_ids = $course->assistants();
        $assistants = [];
        foreach($assistant_ids as $aid) {
            $assistants[] = User::find($aid);
        }
        $data = array(
            'assistants' => $assistants,
            'course'    => $course,
        );
        $view->data = json_encode($data);
        $view->course = $course;
        $view->user = Auth::user();
        return $view;
    }

    public function add_assistant($cid) {
        $email = Input::get('email');
        $user = User::where('email',$email)->first();
        if($user == null)
            return Redirect::back()->with('error','No user with email '.$email.' found.');
        $course = Course::find($cid);
        if($course->assistants() != null)
            $assistants = $course->assistants();
        else
            $assistants = [];
        array_push($assistants, $user->id);
        $course->assistants = implode("|", $assistants);
        $course->save();
        return Redirect::back()->with('status','User '.$email.' promoted to Learning Assistant.');
    }

    public function revoke_assistant($cid, $id) {
        $user = User::find($id);
        $course = Course::find($cid);
        $assistants = $course->assistants();
        if (($key = array_search($id, $assistants)) !== false) {
            unset($assistants[$key]);
        }
        $course->assistants = implode("|", $assistants);
        $course->save();

        return Redirect::back()->with('status','User '.$user->email.' Learning Assistant privileges revoked.');
    }

    public function toggle_assistant_edit($cid) {
        $course = Course::find($cid);

        try {
            $privs = $course->assistant_privs;
            $privs['edit'] = !$privs['edit'];
            $course->assistant_privs = $privs;
        }
        catch (\Exception $e) {
            $course->assistant_privs = [
                'edit'  => true,
            ];
            return Redirect::back()->with('status', $e->getMessage());
        }


        $course->save();

        return Redirect::back()->with('status', 'Assistant edit privilege changed to '.($course->assistant_privs['edit'] == true ? 'on.' : 'off.'));
    }

    public function drop_course($cid) {
        $user = Auth::user();
        $user->courses()->detach($cid);
        return Redirect::to('home')->with('status',"Dropped course.");
    }

    public function create_course_page() {
        $view = View::make('instructor.course.createCourse');
        return $view;
    }

    public function create_course() {
        $name = Input::get('name');
        $key = Input::get('key');
        $user = Auth::user();
        $course = new Course;
        $course->name = $name;
        $course->key = $key;
        $course->active = 0;
        $course->owner = Auth::id();
        $course->save();
        $user->courses()->attach($course->id);
        return Redirect::to('instructor/home')->with('status','Course Created');
    }

    public function duplicate_course($id){
        $old_course = Course::with('assignments')
            ->with('seats')
            ->with('polls')
            ->with('infos')
            ->with('folders.course_files')
            ->find($id);

        try
        {
            $course = $old_course->replicate();
            $course->name = $old_course->name . " - duplicate";
            $course->owner = Auth::id();
            $course->active = 0;
            $course->archived = 0;
            $course->save();
            $course->users()->sync(array($course->owner));

            //Duplicate assignments
            $assignments = $old_course->assignments;
            foreach($assignments as $assignment)
                AssignmentController::duplicate_assignment($course->id, $assignment->id, false);

            //Duplicate seats
            $old_seats = $old_course->seats;
            foreach ($old_seats as $os) {
                $seat = $os->replicate();
                $seat->course_id = $course->id;
                $seat->save();
            }

            //Duplicate polls
            $old_polls = $old_course->polls;
            foreach($old_polls as $op) {
                $poll = $op->replicate();
                $poll->course_id = $course->id;
                $poll->save();
            }

            //Duplicate info
            $old_infos = $old_course->infos;
            foreach($old_infos as $oi) {
                $info = $oi->replicate();
                $info->course_id = $course->id;
                $info->save();
                foreach($oi->info_quizzes as $oq) {
                    $quiz = $oq->replicate();
                    $quiz->info_id = $info->id;
                    $quiz->save();
                    foreach($oq->info_quiz_questions as $oquest) {
                        $question = $oquest->replicate();
                        $question->info_quiz_id = $quiz->id;
                        $question->save();
                    }
                }
            }

            //Duplicate files
            $old_folders = $old_course->folders;
            foreach($old_folders as $ofold) {
                $folder = $ofold->replicate();
                $folder->course_id = $course->id;
                $folder->save();
                foreach($ofold->course_files as $of) {
                    $file = $of->replicate();
                    $file->folder_id = $folder->id;
                    $file->course_id = $course->id;
                    $file->location = 'files'.'/'.$course->id.'/'.substr($file->location,strrpos($file->location,'/')+1,-1);
                    $file->save();
                    try {
                        Storage::copy($of->location,$file->location);
                    }
                    catch(\Exception $e) {
                        log($e);
                    }
                }
            }

        }
        catch (Exception $ex) {
            return Redirect::to('instructor/home')->with('error','Error duplicating assignment. '. $ex->getMessage());
        }

        return Redirect::to('instructor/home')->with('status','Course Duplicated');
    }

    public function classroom_layout($cid) {
        $course = Course::find($cid);
        $view = View::make('instructor.course.classroomLayout');
        $students = $course->users->reject(function($s) { return $s->instructor; });
        $seats = $course->seats;
        $classrooms = Classroom::where('user_id',Auth::id())->get()->reject(function($c) { return ($c->name == 'default_classroom.png' || $c->name == '48_studio_default.png' || $c->name == '64_studio_default.png' ); });

        $data = array(
            'course' => $course,
            'students' => $students,
            'seats' => $seats,
            'classrooms' => $classrooms,
        );

        $view->data = json_encode($data);
        $view->course = $course;
        return $view;

    }

    public function save_layout($cid) {
        $new_seats = Input::get('seats');

        //Delete the old seats
        $old_seats = Seat::where('course_id',$cid)->get();
        foreach($old_seats as $os)
            $os->delete();

        $updated_seats = array();
        foreach($new_seats as $n) {
            $seat = new Seat;
            $seat->course_id = $cid;
            $seat->name = $n["name"];
            $seat->x = $n["x"];
            $seat->y = $n["y"];
            $seat->save();
            array_push($updated_seats,$seat);
        }
        return json_encode(["updated" => $updated_seats]);
    }

    public function upload_layout_image(Request $request, $cid) {
        $file = $request->file('layoutImage');
        $filename = $file->getClientOriginalName();
        $name = $request->get('name');
        if($name=="")
            $name = $filename;
        //Put the file in storage.
        Storage::put('classrooms/'.Auth::id().'/'.$filename,file_get_contents($file));

        //Save the file location in the database.
        $course = Course::find($cid);
        $classroom = new Classroom;
        $classroom->name = $name;
        $classroom->filename = Auth::id().'/'.$filename;
        $classroom->user_id = Auth::id();
        $classroom->save();
        $course->classroom_id = $classroom->id;
        $course->save();
        //todo Save the file to resources.  Associate with classroom_layout.

        return Redirect::back();
    }

    public function load_template($cid) {
        $filename = Input::get("filename");
        $course = Course::find($cid);
        //TODO put the names of all the templates into a class global array
        if($filename == 'default_classroom.png' || $filename == '48_studio_default.png' || $filename == '64_studio_default.png')
            $classroom = Classroom::whereIn('filename',['default_classroom.png','48_studio_default.png','64_studio_default.png'])->first();
        else
            $classroom = $course->classroom();
        if($classroom == null) {
            $classroom = new Classroom();
            $classroom->user_id = Auth::id();
            $classroom->name = $filename;
        }
        $classroom->filename = $filename;
        $classroom->save();
        $course->classroom_id = $classroom->id;
        $course->save();

        return "Saved classroom as " . $classroom->filename;
    }

    public function remove_template($cid) {
        $id = Input::get("id");
        $classroom = Classroom::find($id);
        $filename = $classroom->filename;
        $classroom->delete();
        /*$path=storage_path().'/app/classrooms/'.$filename;
        if (file_exists($path)) {
            unlink($path);
        }*/
        Storage::delete('classrooms/'.$filename);
        $classrooms = Classroom::where('user_id',Auth::id())->get()->reject(function($c) { return ($c->name == 'default_classroom.png' || $c->name == '48_studio_default.png' || $c->name == '64_studio_default.png' ); });
        return json_encode($classrooms);
    }

    public function get_image($cid) {
        $course = Course::find($cid);

        try {
            if($course->classroom()->filename == '48_studio_default.png')
                $path=public_path().'/img/48_studio_default.png';
            elseif($course->classroom()->filename == '64_studio_default.png')
                $path=public_path().'/img/64_studio_default.png';
            else
                return Storage::get('classrooms/'.$course->classroom()->filename);

            $response = response()->file($path);
        }
        catch (Exception $e) {
            $path=public_path().'/img/default_classroom.png';
            $response = response()->file($path);
        }
        return $response;
    }

    public function update_assignment_order($cid) {
        $active = Input::get("active");
        $inactive = Input::get("inactive");
        if($active != null)
            for($i=1; $i<=count($active); $i++)
                Assignment::where('id',$active[$i-1])->update(['order'=>$i]);
        if($inactive != null)
            for($i=1; $i<=count($inactive); $i++)
                Assignment::where('id',$inactive[$i-1])->update(['order'=>$i]);

        return "Order updated.";
    }

    public function update_assignment_states($id, Request $request) {
        $action = $request->input('action');
        $ids = $request->input('selected');
        if($action == 'activate' || $action == 'deactivate')
            $field = 'active';
        else if ($action == 'enable' || $action == 'disable')
            $field = 'disabled';
        else
            return ['status' => 'fail', 'msg' => 'Bad action:'.$action];
        if($action == 'activate' || $action == 'disable')
            $state = 1;
        else if($action == 'enable' || $action == 'deactivate')
            $state = 0;
        try {
            $assignments = Assignment::where('course_id', $id)->whereIn('id', $ids)
                ->update([$field => $state]);
            return ['status' => 'success', 'assignments' => $assignments];
        }
        catch(Exception $e) {
            return ['status' => 'fail', 'msg' => $e];
        }
    }

    public function delete_course($cid) {
        $course = Course::find($cid);
        $user = Auth::user();
        if($course->owner != $user->id && !$user->admin)
            abort(403, 'Unauthorized action.');
        try {
            $course->delete();
        }
        catch(Exception $ex) {
            return Redirect::to('instructor/home')->with('error','Error deleting course. '. $ex->getMessage());
        }
        return Redirect::to('instructor/home')->with('status','Course Deleted');
    }

    public function get_student_details($cid, $sid) {
        $student = User::where('id',$sid)->with(['courses' => function($query) use($cid){
            $query->where('course_id', $cid)->first();
        }])->first();
        $course = Course::find($cid);
        //TODO get all student scores

        $data = array(
            'student' => $student,
        );
        $view = View::make('instructor.course.studentDetails');
        $view->course = $course;
        $view->user = Auth::user();
        $view->data = json_encode($data);
        return $view;
    }
}
