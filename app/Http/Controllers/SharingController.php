<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use View;
use App\Assignment;
use App\Variable;
use Illuminate\Support\Facades\Input;
use Exception;
use App\Course;
use Illuminate\Support\Facades\DB;
use Response;

class SharingController extends Controller
{

    public function get_student_share_view($cid, $aid) {
        $view = View::make('student.share');
        $view->instructor = Auth::user()->instructor;
        
        $assignment = Assignment::with(['questions.variables.answers','results'])->find($aid);
        $variables = $assignment->variables->where('shared',1);
        \Debugbar::info($assignment->results);

        $course = Course::with('students')->find($cid);
        $test = [];
        $rows = array_fill(0,count($course->students),
            array_fill(0,count($assignment->variables->where('shared')),""));

        $i = 0;
        $shared_vars = [];
        foreach($assignment->questions as $q) {
            foreach($q->variables->where('shared') as $key => $v) {
                array_push($shared_vars, $v);
                //$test[$key]['variable'] = $v;
                foreach($v->answers as $a) {
                    $result = $assignment->results->where('question_id',$a->question_id)->where('user_id',$a->user_id)->first();
                    if($result != null && $result->earned == $q->max_points) {
                        $userIndex = $course->students->search(function($user) use($a) {return $user->id === $a->user_id;});
                        if($userIndex !== false)
                            $rows[$userIndex][$i] = $a->submission;
                    }
                }
                $i++;
            }
        }

        $rows_filtered = [];
        //Remove empty rows
        foreach($rows as $key => $row) {
            if(!empty(array_filter($row)))
                $rows_filtered[] = $row;
        }

        //Randomize the array to avoid giving away identity.  Better alternative is probably to sort by first shared value.
        shuffle($rows_filtered);

        $data = array(
            'assignment_name' => $assignment->name,
            'variables' => $shared_vars,
            'rows' => $rows_filtered,
            'temp' => $rows
        );
        $view->data = json_encode($data);
        $view->user = Auth::user();
        $view->course = Course::find($cid);
        $view->assignment_id = $assignment->id;
        return $view;
    }

    public function share_variable($a_id) {
        $var = Variable::find(Input::get('variable_id'));
        $var->shared = !$var->shared;

        $var->save();
        return Response::json([
            'status' => 'success',
            'variable' => $var,
        ],200);

        //TODO add sockets

    }
}
