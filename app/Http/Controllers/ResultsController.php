<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Course;
use App\Assignment;
use App\Answer;
use View;
use Auth;

class ResultsController extends Controller
{

    public function lazy_results($cid, $aid) {
        return $this->course_assignment_results($cid, $aid, ["lazy" => true]);
    }

    public function course_assignment_results($cid, $aid, $options =[]) {
        if(isset($options['linked']) && $options['linked'])
            $linked = true;
        else
            $linked = false;

        $lazy = $options['lazy'] ?? false;

        $view = View::make('instructor.results.new');
        if($linked) {
            $linked_aids = Assignment::select('id','parent_assignment_id')->where('parent_assignment_id',$aid)->get()
                ->pluck('id')->toArray();
        }
        $course = Course::with('seats')->find($cid);
        if($linked) {
            $linked_students = Course::where('parent_course_id',$cid)
                /*->with(['users' => function($query) use($linked_aids) {
                    $query->with(['cached_answer' => function($q) use($linked_aids) {
                        return $q->whereIn('assignment_id', $linked_aids);
                    }]);
                }])*/
                ->with('users')
                ->get()->pluck('users')->collapse();
        }

        $students = $course->users()->with(['cached_answer' => function ($q) use ($aid) {
            return $q->where('assignment_id', $aid)->latest();
        }])->get();

        if($linked)
            $students = $students->merge($linked_students);
        $student_ids = $students->pluck('id')->toArray();

        $assignment = Assignment::
        with(['questions' => function($q) use($student_ids, $lazy) {
            $include = ['variables','responses'
                ,'results' => function ($qr) use($student_ids) {return $qr->whereIn('user_id', $student_ids);}
            ];
            if(!$lazy)
                $include['answers'] = function ($qa) use($student_ids) {return $qa->whereIn('user_id', $student_ids);};
            return $q
                ->with($include);
            /*->with(['variables','responses'
                ,'answers' => function ($qa) use($student_ids) {return $qa->whereIn('user_id', $student_ids);}
                ,'results' => function ($qr) use($student_ids) {return $qr->whereIn('user_id', $student_ids);}
            ]);*/
        }])
            /*->withCount(['answers' => function ($qa) use($student_ids) {return $qa->whereIn('user_id', $student_ids);}])
            ->withCount(['writtenAnswers' => function ($qa) use($student_ids) {return $qa->whereIn('user_id', $student_ids);}])*/
            ->withCount('linked_assignments')
            ->with('extensions')
            ->with('quiz_jobs.user')
            ->find($aid);

        $linked_assignments = [];
        if($linked) {
            $linked_assignments = Assignment::
            with(['questions' => function($q) use($student_ids) {
                return $q->where('type','!=',4) //Don't include info blocks
                ->with(['variables','responses'
                    //,'answers' => function ($qa) use($student_ids) {return $qa->whereIn('user_id', $student_ids);}
                    ,'results' => function ($qr) use($student_ids) {return $qr->whereIn('user_id', $student_ids);} //->select('id','user_id','question_id','earned');
                ]);
            }])
                ->with('extensions')
                ->with('quiz_jobs.user')
                ->whereIn('id',$linked_aids)->get();
        }

        $assignment_total = 0;
        foreach ($assignment->questions as $q) {
            $q->results = $q->results->keyBy('user_id');
            $assignment_total += $q->max_points;
        }


        $data = array(
            "linked" => $linked,
            "lazy" => $lazy,
            "linked_assignments" => $linked_assignments,
            "assignment" => $assignment,
            "students" => $students,
            "assignment_total" => $assignment_total,
            "seats" => $course->seats,
            "selected_view" => 'points', //TODO get from user prefs
            "selected_q" => 'assignment',
            "show_incorrect" => 'true',
            "wq" => 0,
            "batch" => true,
        );
        if($lazy)
            $data["wq"] = -1;
        $view->course = $course;
        $view->assignment = $assignment;
        $view->instructor = Auth::user()->instructor;
        $view->data = json_encode($data);
        return $view;
    }

    public function linked_assignment_results($cid, $aid) {
        return $this->course_assignment_results($cid, $aid, ["linked" => true]);
    }

    public function load_answers($cid, $aid, Request $request) {
        $answers = Answer::where('question_id', $request->question_id)->get();
        return ['answers' => $answers];
    }

    public function load_all_answers($cid, $aid) {
        //$qlist = Assignment::with('questions:id,assignment_id')->find($aid)->questions->pluck('id');
        $qlist = Assignment::with(['questions' => function($q) {
            $q->where('type','!=',2)->select('id','assignment_id');  //Don't get written answers.
        }])
            ->find($aid)->questions->pluck('id');
        $answers = Answer::whereIn('question_id',$qlist)->get();

        return ['status' => 'success', 'answers' => $answers];
    }

    public function load_student_answers($cid, $aid, Request $request) {
        $qlist = Assignment::with('questions:id,assignment_id')->find($aid)->questions->pluck('id');
        $answers = Answer::whereIn('question_id',$qlist)->where('user_id',$request->student_id)->get();

        return ['status' => 'success', 'answers' => $answers];
    }

    public function course_totals($cid) {
        $course = Course::with('students:users.id,firstname,lastname,email')->find($cid);

        $student_ids = $course->students->pluck('id')->toArray();

        $course->assignments = $course->assignments()->select('id','course_id','name')
            ->with(['results' => function($q) use($student_ids) {
                return $q->select('results.id','user_id','question_id','earned')
                    ->whereIn('user_id',$student_ids);}])
            ->with(['questions:questions.id,assignment_id,max_points'])
        ->get();

        $data = array(
            'course' => $course,
        );

        $view = View::make('instructor.course.courseTotalsSimple');
        $view->course = $course;
        $view->data = json_encode($data);
        return $view;
    }
}
