<?php

namespace App\Http\Controllers;

use App\CachedAnswer;
use App\Library\XMLSerializer;
use App\QuizJob;
use Request;
use Illuminate\Support\Facades\Input;
use View;
use Auth;
use App\Course;
use App\User;
use App\Assignment;
use App\Question;
use App\Condition;
use App\Variable;
use App\InterVariable;
use App\ProfResponse;
use App\Answer;
use App\Result;
use Exception;
use App\Library\Parser;
use App\Library\EvaluateMethods;
use App\CachedAnswer as Cached;
//use Mews\Purifier\Facades\Purifier;
use Stevebauman\Purify\Facades\Purify as Purifier;
use Redirect;
use Maatwebsite\Excel\Facades\Excel;
//use File;
use Response;
use App\Events\QuestionAnswered;
use App\Library\Evaluator;
use App\Schedule;
use Carbon\Carbon;
use Log;
use App\Extension;

class AssignmentController extends Controller
{
    public $restful = true;
    const DECIMAL_VARIABLE = 0;
    const COLUMN_VARIABLE = 1;
    const STRING_VARIABLE = 2;
    const COMPUTED_VARIABLE = 3;
    const SELECTION_VARIABLE = 4;

    const STANDARD_QUESTION = 1;
    const SHORT_ANSWER_QUESTION = 2;
    const SIMPLE_QUESTION = 3;
    const UNANSWERED_QUESTION = 4;
    const SIMPLE_TEXT_QUESTION = 5;
    const MOLECULE_QUESTION = 6;
    const MULTIPLE_CHOICE_QUESTION = 7;
    const REACTION_QUESTION = 8;

    const PERCENT = 0;
    const RANGE = 1;

    public function get_assignment_view($id, $aid)
    {
        $user = Auth::user();
        if (!Assignment::find($aid))
            return "Assignment not found";

        //TODO select only relevant columns from assignment (don't get info)
        $assignment = Assignment::find($aid);

        $view = View::make('assignments.AssignmentView');

        $view->instructor = Auth::user()->instructor;
        $view->user = $user;
        $view->course = $assignment->course;
        $view->assignment = $assignment;

        $questions = $this->get_assignment_questions($assignment, $user);

        $qdata = array(
            //"assignment" => $assignment,
            "questions" => $questions,
            'fileList'  => $assignment->course->file_list(),
            'assignmentList' => $assignment->course->assignment_list(),
            'test' => Carbon::now()->toDateTimeString(),
        );

        if($assignment->type == 2 && $assignment->check_quiz_existence($user)) {
            $qdata['quiz_controls'] = $assignment->quiz_controls($user);
            $qdata['pages'] = $assignment->quiz_page_progress($assignment, $user);
        }

        if(!Auth::user()->instructor) {  //Don't let students see quiz buildout
            $options = $assignment->options;
            unset($options['quiz']);
            $assignment->options = $options;
        }

        $qdata['assignment'] = $assignment;

        $view->qdata = json_encode($qdata);

        return $view;
    }

    public function generate_quizzes($cid, $aid, $missingOnly = false) {
        $assignment = Assignment::find($aid);
        $status = $assignment->generate_quizzes($missingOnly);
        return back()->with($status['status'], $status['msg']);
    }

    public function generate_quizzes_including_linked($cid, $aid, $missingOnly = false) {
        $assignment = Assignment::with('linked_assignments')->find($aid);
        $status = [];
        $status[] = $assignment->generate_quizzes($missingOnly);

        foreach($assignment->linked_assignments as $a) {
            $status[] = $a->generate_quizzes($missingOnly);
        }
        $status =  $this->compile_status($status);
        return back()->with($status['status'], $status['msg']);
    }

    public function generate_missing_quizzes($cid, $aid) {
        return $this->generate_quizzes($cid, $aid, true);
    }

    public function generate_missing_quizzes_including_linked($cid, $aid) {
        return $this->generate_quizzes_including_linked($cid, $aid, true);
    }

    public function generate_quiz_for_student($cid, $aid, $sid) {
        $assignment = Assignment::find($aid);
        $student = User::find($sid);
        try {
            $assignment->generate_quiz_job_for_student($sid);
            return Redirect::back()->with('status',"Quiz generated for ".$student->firstname." ".$student->lastname.".");
        }
        catch(\Exception $e) {
            report($e);
            return back()->with('error','Failed to generate quizzes: '.$e->getMessage());
        }
    }

    public function update_quiz_timings($cid, $aid) {
        $assignment = Assignment::find($aid);
        $status = $assignment->update_timings();
        return back()->with($status['status'], $status['msg']);
    }

    public function update_quiz_timings_including_linked($cid, $aid) {
        $assignment = Assignment::with('linked_assignments')->find($aid);

        $status = [];
        $status[] = $assignment->update_timings();

        foreach($assignment->linked_assignments as $linked) {
            $status[] = $linked->update_timings();
        }
        $status =  $this->compile_status($status);
        return back()->with($status['status'], $status['msg']);
    }

    public function update_quiz_settings($cid, $aid, \Illuminate\Http\Request $request) {
        $options = json_decode($request->input('options'));
        $assignment = Assignment::with('linked_assignments')->find($aid);
        //return $assignment->update_settings($options);
        $returnVal = $assignment->update_settings($options);
        //TODO update return information based on whether linked saving worked.
        foreach($assignment->linked_assignments as $linked) {
            $linked->update_settings($this->set_assignment_options($options, true, $linked->id));
        }
        return $returnVal;
    }

    public function update_student_quiz_detail($cid, $aid, \Illuminate\Http\Request $request) {
        $job = json_decode($request->input('job'));
        \Debugbar::info($request);
        //$quiz = QuizJob::where('assignment_id', $aid)->where('user_id', $job->user_id)->first();
        $quiz = QuizJob::find($job->id);
        return $quiz->updateTiming($job);
    }

    public function update_batch_quiz_detail($cid, $aid, \Illuminate\Http\Request $request) {
        $jobs = json_decode($request->input('jobs'));
        $detail = json_decode($request->input('detail'));
        \Debugbar::info($request);
        $job_ids = array_column($jobs,'id');

        $update = QuizJob::whereIn('id',$job_ids)->update([
            "allowed_start" => Carbon::createFromFormat('Y-m-d H:i', $detail->allowed_start),
            "allowed_end" => Carbon::createFromFormat('Y-m-d H:i', $detail->allowed_end),
        ]);

        $quiz_jobs = QuizJob::whereIn('id',$job_ids)->get();
        return ["status" => 'success', 'quiz_jobs' => $quiz_jobs, 'message' => "Updated " . $update . "jobs.", 'batch' => true];
    }

    public function update_student_quiz_status($cid, $aid, \Illuminate\Http\Request $request) {
        $job = json_decode($request->input('job'));
        $status = json_decode($request->input('status'));
        //$quiz = QuizJob::where('assignment_id', $aid)->where('user_id', $job->user_id)->first();
        $quiz = QuizJob::find($job->id);
        return $quiz->updateStatus($status);;
    }

    public function update_student_quiz_page_status($cid, $aid, \Illuminate\Http\Request $request) {

        $job = json_decode($request->input('job'));
        $page = json_decode($request->input('page'));
        $status = json_decode($request->input('status'));
        //$quiz = QuizJob::where('assignment_id', $aid)->where('user_id', $job->user_id)->first();
        $quiz = QuizJob::find($job->id);
        return $quiz->updatePageStatus($page, $status);
    }

    public function quiz_next_piece($cid, $aid) {
        $assignment = Assignment::find($aid);
        return $assignment->quiz_next_piece(Auth::id());
    }

    public function allow_quiz_review($cid, $aid) {
        $status = $this->update_quiz_review_state($cid, $aid, 1);
        return back()->with($status['status'], $status['msg']);
    }

    public function disallow_quiz_review($cid, $aid) {
        $status = $this->update_quiz_review_state($cid, $aid, 0);
        return back()->with($status['status'], $status['msg']);
    }

    public function allow_quiz_review_including_linked($cid, $aid) {
        $status = $this->update_quiz_review_state_including_linked($cid, $aid, 1);
        return back()->with($status['status'], $status['msg']);
    }

    public function disallow_quiz_review_including_linked($cid, $aid) {
        $status = $this->update_quiz_review_state_including_linked($cid, $aid, 0);
        return back()->with($status['status'], $status['msg']);
    }

    private function update_quiz_review_state($cid, $aid, $review_state) {
        try {
            QuizJob::where('assignment_id',$aid)->update(['review_state'=>$review_state]);
            $msg = $review_state == 1 ? 'Quiz Jobs Changed to Allow Review' : 'Quiz Jobs Changed to Disallow Review';
            return ["status" => 'status', 'msg' => $msg, 'aid' => $aid, 'cid' => $cid];
        }
        catch(\Exception $e) {
            report($e);
            return ["status" => 'error', 'msg' => 'Failed to update quiz job statuses: '.$e->getMessage(), 'aid' => $aid, 'cid' => $cid];
        }
    }

    private function update_quiz_review_state_including_linked($cid, $aid, $review_state) {
        $status = [];
        $status[] = $this->update_quiz_review_state($cid, $aid, $review_state);
        $linked_assignments = Assignment::where('parent_assignment_id', $aid)->get();

        foreach($linked_assignments as $linked) {
            $status[] = $this->update_quiz_review_state($linked->course_id, $linked->id, $review_state);
        }

        return $this->compile_status($status);
    }

    private function compile_status($status_array, $successVal = 'status') {
        $returnVal = ['status' => $successVal, 'msg' => ''];

        foreach($status_array as $key => $status) {
            if($status['status'] == 'error')
                $returnVal['status'] = 'error';
            if($key > 0)
                $returnVal['msg'] .= '<br/>';
            $returnVal['msg'] .= $status['msg'];
            if(count($status_array) > 1) {
                $returnVal['msg'] .= ' (' . 'Course ID: ' . $status['cid'];
                if(isset($status['aid']))
                    $returnVal['msg'] .= ', Assignment ID: ' . $status['aid'];
                $returnVal['msg'] .= ')';
            }
        }
        return $returnVal;
    }

    public function toggle_quiz_review_for_student($cid, $aid, \Illuminate\Http\Request $request) {
        try {
            $job = QuizJob::find(json_decode($request->input('job'))->id);
            $job->review_state = $job->review_state == 1 ? 0 : 1;
            $job->save();
            return ["status" => 'success', 'quiz_job' => $job];
        }
        catch(\Exception $e) {
            report($e);
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function release_deferred_feedback($cid, $aid) {
        $status = $this->update_feedback_state_for_assignment($cid, $aid, 'release');
        return back()->with($status['status'], $status['msg']);
    }

    public function redefer_feedback($cid, $aid) {
        $status = $this->update_feedback_state_for_assignment($cid, $aid, 'defer');
        return back()->with($status['status'], $status['msg']);
    }

    public function release_deferred_feedback_including_linked($cid, $aid) {
        $status = $this->update_feedback_state_including_linked($cid, $aid, 'release');
        return back()->with($status['status'], $status['msg']);
    }

    public function redefer_feedback_including_linked($cid, $aid) {
        $status = $this->update_feedback_state_including_linked($cid, $aid, 'defer');
        return back()->with($status['status'], $status['msg']);
    }

    private function update_feedback_state_including_linked($cid, $aid, $action) {
        $status = [];
        $status[] = $this->update_feedback_state_for_assignment($cid, $aid, $action);
        $linked_assignments = Assignment::where('parent_assignment_id', $aid)->get();

        foreach($linked_assignments as $linked) {
            $status[] = $this->update_feedback_state_for_assignment($linked->course_id, $linked->id, $action);
        }

        return $this->compile_status($status);
    }

    //TODO this has been partly migrated to the assignment model for deferral, but not implemented here.
    private function update_feedback_state_for_assignment($cid, $aid, $action) {
        if($action == 'release') {
            $initial = 3;
            $final = 1;
        }
        else if($action == 'defer') {
            $initial = 1;
            $final = 3;
        }
        else
            return ['status' => 'error', 'msg' => 'Invalid action supplied.', 'aid' => $aid, 'cid' => $cid];

        try {
            //If it's a quiz or the action is to release, apply to everything
            if(Assignment::where('id',$aid)->pluck('type')->first() == 2 || 'action' == 'release')
                $count=Assignment::find($aid)->unorderedResults()->where('status',$initial)->update(['status' => $final]);
            //Otherwise, only redefer feedback for results that belong to questions marked as deferred.
            else
                $count=Assignment::find($aid)->unorderedResults()->whereHas('question', function($q) {$q->where('deferred',1);})->where('status',$initial)->update(['status' => $final]);
            $actionMsg = $action == 'release' ? 'released' : 'redeferred';
            return ['status' => 'status', 'msg' => "Feedback $actionMsg for ".$count." results.", 'aid' => $aid, 'cid' => $cid];
        }
        catch(\Exception $e) {
            report($e);
            return ['status' => 'error','msg' => 'Failure during feedback redeferral: '.$e->getMessage()];
        }

    }

    public function update_deferred_state_for_student($cid, $aid, \Illuminate\Http\Request $request) {
        try {
            $job = QuizJob::find(json_decode($request->input('job'))->id);
            $count=Assignment::find($aid)->unorderedResults()->where('status',$request->old_status)->where('user_id',$job->user_id)->update(['status' => $request->new_status]);
            return ["status" => 'success', 'quiz_job' => $job, 'message' => "Feedback ". ($request->new_status == 1 ? "released" : "deferred") ." for ".$count." results. Reload results to see updated state on points page."];
        }
        catch(\Exception $e) {
            report($e);
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function get_assignment_questions($assignment, $user, $preview = false, $instructorPreview = false) {
        if($assignment->type == 2 && !$preview)
            $questions = $assignment->unorderedQuestions();  //If it's a quiz and not a preview, ignore the orderBy 'order' in the Assignment model
        else
            $questions = $assignment->questions();

        //Should switch conditional to query builder "when" statement
        if(Auth::user()->instructor == 1)
            $questions->with(['comments' => function($query) {
                return $query->with('user');
            }]);
        else
            $questions->with('comments:question_id,id,contents');

        //This is primarily to get the simple question answer (but also captures the first variable in standard questions unnecessarily)
        //Answers for standard questions are eager loaded with the variables further below in the query builder chain.
        $questions->with(['answer' => function($q) use ($user) {
            return $q->where('user_id',$user->id);
        }]);
        $questions->with(['result' => function($q) use ($user) {
            return $q->where('user_id',$user->id);
        }]);
        //The following gets the latest answer for a user for a variable
        //Won't handle answers for simple questions.
        $questions->with('computedVariables')  //Pull in the computed variables separately from the rest so these variables can be unset
            ->with(['variables' => function($q) use($user) {
            return $q->where('type','!=',3)  //Don't include computed variables in the variable set returned to the browser.
                ->with(['answer' => function($q2) use($user) {
                return $q2->where('user_id',$user->id);
            }]);
        }]);
        $question_fields = ['id','molecule','choices','description','max_points','order','type','extra','options'];

        if(!isset($assignment->options['suppressQuestionNames']) || (isset($assignment->options['suppressQuestionNames']) && !$assignment->options['suppressQuestionNames']))
            array_push($question_fields, 'name');

        $questions->select($question_fields);

        if($assignment->type == 2 && !$preview) {  //If it is a quiz, filter down to only the currently available questions.
            $qids = $assignment->check_quiz_existence($user) ? $assignment->get_current_quiz_questions($assignment, $user, $instructorPreview) : [];

            $qidsOrder = implode(',',$qids);
            \Debugbar::info($qidsOrder);
            $questions->whereIn('id',$qids);
            if(count($qids) > 0)
                $questions->orderByRaw("FIELD(id, $qidsOrder)");  //The assignment model drops its orderby('order') field on the questions relationship if the assignment type is a quiz
        }

        $questions = $questions->get();

        //if($assignment->type)

        //This block pulls in the cached values in case there are computed variables
        //And to defer feedback
        //And to shuffle MC questions
        $computedVars = array();
        $computedVarsByName = array();
        //Using firstOrNew pulls the oldest version. Theoretically, there should only be one version of the cache,
        //but since a race condition can generate two versions, pulling the oldest is a problem (particularly because the evaluation pulls the latest version).
        //$cache = CachedAnswer::firstOrNew(['user_id' => $user->id, 'assignment_id' => $assignment->id],['values'=>json_encode(array())]);
        $cache = Cached::where("user_id", $user->id)
            ->where("assignment_id", $assignment->id)
            ->latest()->first();
        if(!isset($cache)) {
            $cache = new Cached;
            $cache->assignment_id = $assignment->id;
            $cache->user_id = $user->id;
            $cache->values = json_encode(array());
        }
        $cachedVals = json_decode($cache->values, true);

        foreach($questions as $q) {
            if(isset($q->result) && $q->result->status == 3 && !(isset($assignment->options['deferredOverride']) && $assignment->options['deferredOverride'])) { //Deferred feedback
                $q->result->feedback = "Submission recorded.";
                unset($q->result->earned);
            }
            foreach($q->variables as $v) {
                //Re-format array variables
                if ($v->type == self::COLUMN_VARIABLE)
                {
                    if($v->answer != null)
                        $v->answer->submission = implode("\n",explode("|", $v->answer->submission));
                }
            }
            foreach ($q->computedVariables as $v) {

                //Search for computed variables and set them if necessary
                if ($v->type == self::COMPUTED_VARIABLE) {
                    $variableName = json_decode(json_encode($v->name));
                    if(($q->options["isolated"] ?? false)) {
                        $q->description = $this->replace_isolated_computed_variable_name($q->description, $q->id, $variableName);
                        if(isset($q->extra['text'])) {
                            $extra = $q->extra;
                            $extra['text'] = $this->replace_isolated_computed_variable_name($extra['text'], $q->id, $variableName);
                            $q->extra = $extra;
                        }
                        foreach($q->variables as $var) {
                            if($var->type == self::SELECTION_VARIABLE) {
                                $choices = $var->choices;
                                foreach($choices as $key => $choice)
                                    $choices[$key] = $this->replace_isolated_computed_variable_name($choice, $q->id, $variableName);
                                $var->choices = $choices;
                            }
                        }

                        $variableName = $variableName . "__" . $q->id;
                    }
                    $q->computed = true;
                    if(array_key_exists($variableName, $cachedVals) && !$this->check_computed_error($cachedVals[$variableName])) {
                        $computedVars[$variableName] = $cachedVals[$variableName];
                        $computedVarsByName[$v->name] = $cachedVals[$variableName];
                    }
                    else {
                        try {
                            \Debugbar::addMessage('Evaluating computed for '. $variableName);
                            \Debugbar::info($computedVarsByName);
                            $eval = new Evaluator($computedVarsByName);
                            $newVal = $eval->getValue($v->descript);
                        }
                        catch(\Throwable $e) {
                            \Debugbar::error($e);
                            $newVal = "<b>error</b>";
                        }
                        $computedVars[$variableName] = $newVal;
                        $computedVarsByName[$v->name] = $newVal;
                        $cachedVals[$variableName] = $newVal;
                    }

                }
            }
            if($q->type == self::MULTIPLE_CHOICE_QUESTION && $q->options['MC']['shuffleType'] != 'none') {
                if(array_key_exists('__shuffle_'.$q->id, $cachedVals) && $q->options['MC']['shuffleType'] == $cachedVals['__shuffleType_'.$q->id] && $this->validate_shuffle($cachedVals['__shuffle_'.$q->id],collect($q->choices)->pluck('id')))
                    $q->choices = $q->shuffle_MC_apply($cachedVals['__shuffle_'.$q->id]);
                else {
                    $q->choices = $q->shuffle_MC();
                    $cachedVals['__shuffle_'.$q->id] = collect($q->choices)->pluck('id');
                    $cachedVals['__shuffleType_'.$q->id] = $q->options['MC']['shuffleType'];
                }
            }
        }

        // Inject computed variables and then filter computed variables out of the returned question
        $questions->map(function($question) use($computedVars) {
            $question->inject_computed($computedVars);

            unset($question->computedVariables);

            return $question;
        });


        $cache->values = json_encode($cachedVals,JSON_PARTIAL_OUTPUT_ON_ERROR);

        $cache->save();

        return $questions;
    }

    private function replace_isolated_computed_variable_name($field, $id, $variableName) {
        $field = str_replace('##'.$variableName.'##', '##'.$variableName. "__" . $id.'##', $field);
        $field = str_replace('#_'.$variableName.'_#', '#_'.$variableName. "__" . $id.'_#', $field);
        $field = str_replace('#^'.$variableName.'^#', '#^'.$variableName. "__" . $id.'^#', $field);
        return $field;
    }

    private function check_computed_error($var) {
        if(!is_string($var))
            return false;
        return strcmp($var, "<b>error</b>") == 0;
    }

    //Verify that the cached shuffle list has a 1:1 correspondence with the choice ids
    private function validate_shuffle($shuffled, $choicesList) {
        if(count($shuffled) != count($choicesList))
            return false;
        foreach($choicesList as $choice) {
            if(!in_array($choice, $shuffled))
                return false;
        }
        return true;
    }

    public function load_into_student_view($course_id, $assignment_id, $student_id) {
        $student = User::find($student_id);
        $assignment = Assignment::find($assignment_id);

        $view = View::make('assignments.AssignmentView');
        $view->instructor = Auth::user()->instructor;
        $view->user = $student;
        $view->course = $assignment->course;
        $view->assignment = $assignment;

        $questions = $this->get_assignment_questions($assignment, $student, false, true);

        //Get student's cache
        $cache_student = Cached::where("user_id", $student_id)
            ->where("assignment_id", $assignment_id)
            ->latest()->first();
        //Get instructor's cache
        $cache = Cached::where("user_id", Auth::id())
            ->where("assignment_id", $assignment_id)
            ->latest()->first();
        if(!isset($cache)) {
            $cache = new Cached;
            $cache->assignment_id = $assignment_id;
            $cache->user_id = Auth::id();
        }
        if(isset($cache_student))
            $cache->values = $cache_student->values;
        else
            $cache->values = "";
        $cache->save();

        $qdata = array(
            "assignment" => $assignment,
            "questions" => $questions,
            "student_view" => true,
            "fileList"  => $assignment->course->file_list(),
        );

        if($assignment->type == 2 && $assignment->check_quiz_existence($student)) {
            $qdata['quiz_controls'] = $assignment->quiz_controls($student);
            $qdata['pages'] = $assignment->quiz_page_progress($assignment, $student);
        }

        $view->qdata = json_encode($qdata);

        return $view;
    }

    public function new_computed_values_for_user($cid, $aid, $qid, $uid) {
        $q = Question::find($qid);
        return $q->new_computed_values_for_user($uid);
    }

    public function new_computed_values($cid, $aid, $qid) {
        $q = Question::find($qid);
        $cache = Cached::where("user_id", Auth::id())
            ->where("assignment_id", $q->assignment->id)
            ->latest()->first();
        $cachedVals = json_decode($cache->values, true);

        foreach ($q->variables as $key => $v) {
            if ($v->type == self::COMPUTED_VARIABLE) {
                $variableName = json_decode(json_encode($v->name));
                if(($q->options["isolated"] ?? false))
                    $variableName = $variableName . "__" . $q->id;
                try {
                    $eval = new Evaluator($cachedVals);
                    $cachedVals[$variableName] = $eval->getValue($v->descript);
                }
                catch(\Throwable $e) {
                    report($e);
                    $cachedVals[$variableName] = "<b>error</b>";
                }
            }
        }
        $cache->values = json_encode($cachedVals,JSON_PARTIAL_OUTPUT_ON_ERROR);
        $cache->save();
        return Redirect::back()->with('status',"Values changed for ".$q->name);
    }

    public function new_assignment($course_id)
    {
        $assignment = array(
            "course_id" 	=> $course_id,
            "active"    	=> 0,
            "creator_id"	=> Auth::id(),
            "id"			=> -1,
            "name"			=> "",
            "description"	=> "",
            "info"          => "",
            "options"       => new \stdClass(),

        );

        $data['assignment'] = $assignment;
        $data['questions'] = [];

        $course = Course::find($course_id);
        $data['course'] = $course;
        $data['editing'] = false;

        $data["data"] = json_encode($data);
        $data["assignment"] = $assignment;
        $view = View::make("instructor.assignments.editor", $data );
        $view->course = $course;
        $view->instructor = Auth::user()->instructor;
        return $view;
    }

    public function save_and_preview_assignment()
    {
        if (!Input::get("data")) //If the POST assignment is not set, die
        {
            return "No data sent";
        }
        // Validation
        $data = Input::get('data');
        $data = json_decode($data);
        $valid = $this->validate_assignment($data);
        if ($valid !== true)
        {
            return $valid;
        }

        $assignment = $data->assignment;

        // Check if this is a new assignment (ID = -1)
        if ($assignment->id != -1)
        {
            // Find assignment and eager load questions and nested relationships
            $assn = Assignment::with(['questions.variables','questions.interVariables','questions.conditions'])
                ->with(['linked_assignments' => function($query) {
                    $query->with(['questions.variables','questions.interVariables','questions.conditions']);
                }])
                ->find($assignment->id);
        }
        else
        {
            // Create new assignment and use default values
            $assn = new Assignment();
            $assn->creator_id = Auth::id();
            $assn->active = 0;
            $assn->course_id = $assignment->course_id;
        }

        if($assn->parent_assignment_id != null)
            return "This assignment is linked to a parent assignment in the course ".$assn->linked_parent_assignment->course->name.". You cannot edit a linked child assignment directly because it may create conflicts between assignment versions. You must either edit the parent assignment directly or unlink the assignment.";

        //Save the assignment.
        $errorMsgs = [];
        $successMsgs = [];
        $response = $this->save_assignment($assn, $assignment, $data);
        if(isset($response['ids']))
            $ids = $response['ids'];
        else
            $ids = null;
        if($response["status"] == 'error')
            array_push($errorMsgs, $response['msg']);
        if($response["status"] == 'success')
            array_push($successMsgs, $response['msg']);

        //Apply to linked assignments
        foreach ($assn->linked_assignments as $linked_assignment) {
            $response = $this->save_assignment($linked_assignment, $assignment, $data, true, $ids); //Last parameter says linked is true
            if($response["status"] == 'error')
                array_push($errorMsgs, $response['msg']);
            if($response["status"] == 'success')
                array_push($successMsgs, $response['msg']);
        }

        //Return a preview of the assignment
        $preview = $this->get_preview($assn->id, Auth::user());
        $returnData = array("assignmentID" => $assn->id, "message" => $preview, "ids" => $ids, 'errorMsgs' => $errorMsgs, 'successMsgs' => $successMsgs);

        return json_encode($returnData);
    }

    private function save_assignment($assn, $assignment, $data, $linked = false, $parent_ids = []) {
        $assn->name = $assignment->name;
        $assn->description = $assignment->description; //Purifier::clean($assignment->description);  //TODO Move all purifier to accessor method
        $assn->closes_at = $assignment->closes_at ?? null;
        $assn->info = Purifier::clean($assignment->info);
        try {
            $assn->type = $assignment->type;
        }
        catch(\Exception $e) {
            $assn->type = 1;
        }

        \Debugbar::info($assignment);
        $assn->options = $this->set_assignment_options($assignment->options, $linked, $assn->id);
        try
        {
            $assn->save();
        }
        catch (Exception $ex)
        {
            report($ex);
            return ['status' => 'error', 'msg' => $ex->getMessage()];
        }

        return $this->save_questions($assn, $data->questions, $linked, $parent_ids);
    }

    //TODO this lives here and in the Assignment model.  Should thin out controller.
    //Note that this version uses objects; other version uses arrays.
    private function set_assignment_options($options, $linked, $assignment_id) {
        if(!$linked || !isset($options->quiz))
            return $options;
        $options_duplicate = json_decode(json_encode($options));
        foreach($options_duplicate->quiz->pages as $kp => $page) {
            foreach($page->groups as $kg => $group) {
                foreach($group->question_ids as $kq => $qid) {
                    $qid = \DB::table('questions')
                        ->where('parent_question_id', $qid)->where('assignment_id',$assignment_id)->value('id');
                    $options_duplicate->quiz->pages[$kp]->groups[$kg]->question_ids[$kq] = $qid;
                }
            }
        }
        return $options_duplicate;
    }

    private function get_preview($aid, $user) {
        $assignment = Assignment::find($aid);


        $questions = $this->get_assignment_questions($assignment, $user, true);

        $qdata = array(
            "assignment" => $assignment,
            "questions" => $questions,  //TODO want to send only minimized question without extra details on it (drop portions from object)
            'fileList'  => $assignment->course->file_list(),
        );

        $view = View::make('assignments.assignmentPreview');
        $view->user = Auth::user();
        $view->instructor = Auth::user()->instructor;
        $view->course = $assignment->course;
        $view->assignment = $assignment;

        $view->qdata = json_encode($qdata);

        return $view->render();

        //return json_encode($qdata);
    }

    public function edit_assignment($id,$aid)
    {
        $assignment = Assignment::withCount('linked_assignments')
            ->with('linked_parent_assignment.course')
            ->find($aid);

        $questions = $assignment->questions()
            ->with('conditions','variables','interVariables','responses')
            ->get();

        $data['assignment'] = $assignment;
        $data['questions'] = $questions;

        $data['course'] = $assignment->course;
        $data['editing'] = true;

        $data["data"] = json_encode($data);
        $data["assignment"] = $assignment;

        $view = View::make("instructor.assignments.editor", $data );
        $view->instructor = Auth::user()->instructor;
        return $view;
    }

    private function validate_assignment($data)
    {
        try
        {
            $assignment = $data->assignment;

            if (!isset($data->questions))
                return "Please include at least one question in the assignment";
            $questions = $data->questions;
            if (count($questions) == 0)
                return "Please include at least one question in the assignment";

            if (!isset($assignment->name))
                return "Please include a name for the assignment";
            if ($assignment->name == "")
                return "Please include a name for the assignment";

            $all_variable_names = array();
            foreach($questions as $q)
            {
                $question_variable_names = [];
                if ($q->name == "")
                    return "Please include a name for all the questions";

                if ($q->type == self::STANDARD_QUESTION)
                {
                    if (!isset($q->variables)) {
                        $msg="Question name: " . $q->name . "<br>Question ID:" . $q->id . " Assignment ID: " .$q->assignment_id;
                        return "Please include at least one variable in your questions. $msg";
                    }

                    foreach ($q->variables as $v)
                    {
                        if ($v->name == "")
                            return "Please include a name for all your variables. Check question ".$q->name.".";
                        if ($v->title == "" && $v->type != self::COMPUTED_VARIABLE)
                            return "Please include a title for all your variables. Check variable ".$v->name.".";
                        if ($v->descript == "" && $v->type == self::COMPUTED_VARIABLE)
                            return "Please include a formula in the description for all your computed variables. Check variable ".$v->name.".";
                        if ($v->type == self::SELECTION_VARIABLE) {
                            if($v->choices == null || count($v->choices)==0)
                                return "Selection variables must contain at least one choice. Check variable ".$v->name.".";
                            foreach($v->choices as $c) {
                                if($c->value == "")
                                    return "All selection variable choices must contain a value. Check variable ".$v->name.".";
                                if($c->name == "")
                                    return "All selection variable choices must have a name.  Check variable ".$v->name.".";
                            }
                        }
                        if(($q->options->isolated ?? false))
                            $question_variable_names[] = $v->name;
                        else
                            $all_variable_names[] = $v->name;
                    }

                    foreach ($q->variables as $v) {
                        if($v->type == self::COMPUTED_VARIABLE) {
                            // Try to compile the equations
                            try
                            {
                                if(($q->options->isolated ?? false))
                                    $parser = new Parser($v->descript, $question_variable_names);
                                else
                                    $parser = new Parser($v->descript, $all_variable_names);
                            }
                            catch (Exception $e)
                            {
                                $msg = $e->getMessage();
                                return "There was an error in the equation for computed variable '$v->name': $msg";
                            }
                        }
                    }

                    if (isset($q->inter_variables))
                    {
                        foreach ($q->inter_variables as $v)
                        {
                            if ($v->name == "")
                                return "Please include a name for all your intermediate variables";
                            if ($v->equation == "")
                                return "Please include a valid equation for your intermediate variables";

                            if(($q->options->isolated ?? false))
                                $question_variable_names[] = $v->name;
                            else
                                $all_variable_names[] = $v->name;
                            $equation = $v->equation;
                            try
                            {
                                if(($q->options->isolated ?? false))
                                    $parser = new Parser($equation, $question_variable_names);
                                else
                                    $parser = new Parser($equation, $all_variable_names);
                            }
                            catch(Exception $e)
                            {
                                $msg = $e->getMessage();
                                return "There was an error in your intermediate equation ($equation): $msg";
                            }

                        }
                    }
                    if (!isset($q->conditions))
                        return "Please include at least one condition for your questions.";

                    foreach ($q->conditions as $c)
                    {
                        if ($c->result == "")
                            return "Please include a return expression for all your conditions";
                        if ($c->equation == "")
                            return "Please include a valid equation for your conditions";

                        // Try to compile the equations
                        try
                        {
                            if(($q->options->isolated ?? false))
                                $parser = new Parser($c->equation, $question_variable_names);
                            else
                                $parser = new Parser($c->equation, $all_variable_names);
                        }
                        catch (Exception $e)
                        {
                            // print_r($all_variable_names);
                            $cond = $c->equation;
                            $msg = $e->getMessage();
                            return "There was an error in condition '$cond': $msg";
                        }
                    }
                }
                else if ($q->type == self::SHORT_ANSWER_QUESTION)
                {
                    // Nothing to check
                }
                else if ($q->type == self::SIMPLE_QUESTION)
                {
                    if ($q->tolerance == "" || $q->answer == "")
                        return "Please include a question, answer, and tolerance for simple questions";
                }
                else if ($q->type == self::SIMPLE_TEXT_QUESTION)
                {
                    if ($q->answer == "")
                        return "Please include an answer for simple text questions";
                }
                else if ($q->type == self::REACTION_QUESTION)
                {
                    if($q->answer->scoringMode == "specified") {
                        $total = 0;
                        foreach($q->answer->specified->points as $point) {
                            $total += $this->parseFraction($point ?? 0);
                        }
                        if($total != 1)
                            return "In specified scoring for reaction questions, the sum of scoring fractions must equal 1. They currently sum to " . $total . ".";
                    }

                }
            }
            return true;
        }
        catch(Exception $e)
        {
            return $e->getMessage()." on line ".$e->getLine();
            return "There was a problem parsing the data object from the browser.";
        }
    }

    //Adapted from Tim Williams's answer at https://stackoverflow.com/a/58574477
    //CC BY-SA 4.0 https://creativecommons.org/licenses/by-sa/4.0/
    private function parseFraction(string $fraction): float
    {
        if(preg_match('#(\d+)\s+(\d+)/(\d+)#', $fraction, $m)) {
            return ($m[1] + $m[2] / $m[3]);
        } else if( preg_match('#(\d+)/(\d+)#', $fraction, $m) ) {
            return ($m[1] / $m[2]);
        }
        return (float)$fraction;
    }


    // Save Assignment
    private function save_questions($assignment, $questions, $linked = false, $parent_ids = []) {

        \Debugbar::addMessage('save_questions');
        \Debugbar::info($assignment->questions);
        $i = 1;
        $ids = array();
        if($linked) {
            foreach($questions as $q) {
                $q->search_id = null;
                if($q->id == -1) //Don't try to find linked questions for new questions.
                    continue;
                $q->search_id= \DB::table('questions')
                    ->where('parent_question_id', $q->id)->where('assignment_id',$assignment->id)->value('id');
                if($q->search_id == null)
                    return ['status' => 'error', 'msg' => 'Linked assignment '.$assignment->id.' in '.$assignment->course->name.' missing linked question for question id '.$q->id.'. Linked assignment not updated.'];
            }
        }

        $qindex = 0;
        foreach($questions as $q) // Process each question
        {
            //Get the existing question from the collection.  First returns null if not found (ie need new q)
            $question = $assignment->questions->first(function ($item) use ($q, $linked) {
                if ($linked)
                    return $item->id == $q->search_id;
                return $item->id == $q->id;
            });
            if ($question == null)
                $question = new Question();

            $question->name = $q->name;

            if (isset($q->description))
                $question->description = $q->description;//Purifier::clean($q->description);

            if (isset($q->extra)) {
                $q->extra->text = $q->extra->text; //Purifier::clean($q->extra->text);
                $question->extra = $q->extra;
            }

            if($linked)
                $question->parent_question_id = $parent_ids[$qindex]['qid'];

            $question->order = $qindex+1;
            $question->type = $q->type;
            $question->deferred = $q->deferred;

            $question->options = $q->options;

            // Set assignment_id so that this question belongs to $a
            $question->assignment()->associate($assignment);

            // Save the details of the question (variables, conditions, intermediate variables)
            // Depending on the question type
            $subIDs;
            if ($q->type == self::STANDARD_QUESTION) {
                $subIDs = $this->save_normal_question($q, $question, $qindex, $linked, $parent_ids);
            } else if ($q->type == self::SHORT_ANSWER_QUESTION) {
                if (isset($q->max_points))
                    $question->max_points = $q->max_points;
                $this->save_short_answer_question($q, $question);
            } else if ($q->type == self::SIMPLE_QUESTION) {
                $this->save_simple_question($q, $question);
            } else if ($q->type == self::SIMPLE_TEXT_QUESTION) {
                $this->save_simple_text_question($q, $question);
            } else if ($q->type == self::UNANSWERED_QUESTION) {
                // nothing to do here, name as description already saved
                $question->save();
            } else if ($q->type == self::MOLECULE_QUESTION) {
                $this->save_molecule_question($q, $question);
            } else if ($q->type == self::MULTIPLE_CHOICE_QUESTION) {
                $this->save_multiple_choice_question($q, $question);
            } else if ($q->type == self::REACTION_QUESTION) {
                $this->save_reaction_question($q, $question);
            } else
                throw new Exception("Unknown question type " . $q->type);

            $qid = $question->id;
            if ($question->type == self::STANDARD_QUESTION)
                array_push($ids, compact("qid", "subIDs"));
            else
                array_push($ids, ["qid" => $qid]);

            $qindex++;
        }


        //TODO Delete dropped items
        foreach($assignment->questions as $q)
        {
            //Check if currently saved question is not in the editor's list of questions.
            //If so, delete the saved question.

            $question = collect($questions)->first(function ($item) use ($q, $linked) {
                if($linked) {
                    return $item->search_id == $q->id;
                }
                return $item->id == $q->id;
            });
            if ($question == null)
                $q->delete();
        }

        $msg = 'Updated assignment.';
        if($linked)
            $msg = 'Updated linked assignment '.$assignment->id.' in '.$assignment->course->name.'.';
        \Debugbar::info($ids);
        return(['status' => 'success', 'ids' => $ids, 'msg' => $msg]);
    }

    private function save_short_answer_question($q, $newQ)
    {
        $newQ->save();
        $rIDs=[];
        if(isset($q->responses))
        {
            foreach ($q->responses as $resp)
            {
                $newResp = new ProfResponse();
                $newResp->response = $resp->response;
                $newResp->question()->associate($newQ);
                $newResp->save();
                array_push($rIDs, $newResp->id);
            }
        }

        $this->delete_missing($newQ->responses,$rIDs);

    }

    private function save_simple_question($q, $newQ)
    {
        $newQ->answer = $q->answer;
        $newQ->tolerance = $q->tolerance;			// tolerance of form 'tolerance ~  Percent/Range boolean'
        $newQ->tolerance_type = $q->tolerance_type;
        $newQ->max_points = $q->max_points;
        $newQ->feedback = $q->feedback;
        $newQ->save();

    }

    private function save_simple_text_question($q, $newQ)
    {
        $newQ->tolerance = 0;       //Not actually used, but avoids SQL error.
        $newQ->tolerance_type = $q->tolerance_type;;  // 0 = case-insensitive; 1 = case-sensitive
        $newQ->answer = $q->answer;
        $newQ->max_points = $q->max_points;
        $newQ->feedback = $q->feedback;
        $newQ->save();

    }

    private function save_molecule_question($q, $newQ)
    {
        $newQ->tolerance = 0;       //Not actually used, but avoids SQL error.
        $newQ->tolerance_type = $q->tolerance_type;  // 0 = case-insensitive; 1 = case-sensitive
        $newQ->molecule = $q->molecule;
        $newQ->max_points = $q->max_points;
        $newQ->feedback = $q->feedback;
        $newQ->save();

    }

    private function save_reaction_question($q, $newQ)
    {
        $newQ->tolerance = 0;       //Not actually used, but avoids SQL error.
        $newQ->tolerance_type = $q->tolerance_type;  // 0 = case-insensitive; 1 = case-sensitive
        $newQ->answer = json_encode($q->answer);
        $newQ->max_points = $q->max_points;
        $newQ->feedback = $q->feedback;
        $newQ->save();
        \Debugbar::debug($newQ);

    }

    private function save_multiple_choice_question($q, $newQ)
    {
        $newQ->tolerance = 0;       //Not actually used, but avoids SQL error.
        $newQ->choices = $q->choices;
        $newQ->max_points = $q->max_points;
        $newQ->feedback = $q->feedback;
        $newQ->answer = $q->answer;
        $newQ->save();

    }

    private function save_normal_question($q, $newQ, $qindex, $linked=false, $parent_ids = [])
    {
        $cIDs = array();
        $vIDs = array();
        $iIDs = array();

        //Set points to the maximum from the conditions
        $newQ->max_points = collect($q->conditions)->max('points');
        $newQ->save();

        if($linked) {
            foreach($q->conditions as $param) {
                $param->search_id = null;
                if($param->id == -1) //Don't try to find linked questions for new questions.
                    continue;
                $param->search_id= \DB::table('conditions')
                    ->where('parent_id', $param->id)->where('question_id',$newQ->id)->value('id');
            }
            foreach($q->inter_variables as $param) {
                $param->search_id = null;
                if($param->id == -1) //Don't try to find linked questions for new questions.
                    continue;
                $param->search_id= \DB::table('inter_variables')
                    ->where('parent_id', $param->id)->where('question_id',$newQ->id)->value('id');
            }
            foreach($q->variables as $param) {
                $param->search_id = null;
                if($param->id == -1) //Don't try to find linked questions for new questions.
                    continue;
                $param->search_id= \DB::table('variables')
                    ->where('parent_id', $param->id)->where('question_id',$newQ->id)->value('id');
            }
        }

        \Debugbar::debug($qindex, $parent_ids);
        $c=1;
        foreach ($q->conditions as $cond)
        {
            $newC = $newQ->conditions->first(function($item) use ($cond, $linked) {
                if ($linked)
                    return $item->id == $cond->search_id;
                return $item->id == $cond->id;
            });
            if($newC == null)
                $newC = new Condition();

            $newC->equation = $cond->equation;
            $newC->result = $cond->result;
            $newC->points = $cond->points;
            $newC->type = ($cond->type);
            $newC->order = ($c);
            if($linked)
                $newC->parent_id = $parent_ids[$qindex]['subIDs']['cIDs'][$c-1];
            // Set question_id so that this condition belongs to $newQ
            $newC->question()->associate($newQ);
            // Push condition to database
            $newC->save();
            array_push($cIDs, $newC->id);
            $c++;
        }

        // Process the intermediate variables

        if (isset($q->inter_variables))
        {
            $iv = 1;
            foreach ($q->inter_variables as $var)
            {
                $newVar = $newQ->interVariables->first(function($item) use ($var, $linked) {
                    if ($linked)
                        return $item->id == $var->search_id;
                    return $item->id == $var->id;
                });
                if($newVar == null)
                    $newVar = new InterVariable;
                $newVar->name = $var->name;
                $newVar->equation = $var->equation;
                $newVar->order = $iv;
                if($linked)
                    $newVar->parent_id = $parent_ids[$qindex]['subIDs']['iIDs'][$iv-1];
                // Set question_id so that this variable belongs to $newQ
                $newVar->question()->associate($newQ);
                // Push variable to database
                $newVar->save();

                array_push($iIDs, $newVar->id);
                $iv++;
            }
        }
        // Process the variables
        $v=1;
        foreach ($q->variables as $var)
        {
            $newVar = $newQ->variables->first(function($item) use ($var , $linked) {
                if ($linked)
                    return $item->id == $var->search_id;
                return $item->id == $var->id;
            });
            if($newVar == null)
                $newVar = new Variable();
            $newVar->name = $var->name;
            $newVar->title = $var->title;
            $newVar->descript = $var->descript;
            $newVar->type = $var->type;
            $newVar->order = $v;
            $newVar->choices = $var->choices;
            if($linked)
                $newVar->parent_id = $parent_ids[$qindex]['subIDs']['vIDs'][$v-1];
            // Set question_id so that this variable belongs to $newQ
            $newVar->question()->associate($newQ);
            // Push variable to database
            $newVar->save();
            array_push($vIDs, $newVar->id);
            $v++;
        }

        //Delete items that are no longer in the editor.
        $this->delete_missing($newQ->variables,$vIDs);
        $this->delete_missing($newQ->interVariables, $iIDs);
        $this->delete_missing($newQ->conditions, $cIDs);

        return compact("cIDs","iIDs","vIDs");
    }

    //$old is the set of old items, $newList is an array of the IDs that should still exist from the editor
    private function delete_missing($old,$newList) {
        if($old == null)
            return;
        foreach($old as $o) {
            if(!in_array($o->id,$newList))
                $o->delete();
        }
    }

    /*------------------------------------------------------------------------
     * Assignment Scoring and Grading
     *------------------------------------------------------------------------
     */

    //TODO collapse everything into a single evaluate function; no separate function for molecule question (use case statement)
    public function evaluate_molecule_question($cid,$aid, $user = null, $qid = null, $preview = false) {
        if($user == null)
            $user = Auth::user();
        if (!Input::has('question') && $qid == null) {
            return json_encode(array("output"=>"No question supplied", "valid"=>false));
        }
        // Find the question based on the supplied question id
        if($qid == null)
            $qid = Input::get('question');

        $question = Question::with("assignment")->find($qid);
        $eval_allowed = $question->assignment->eval_allowed();
        if(!$eval_allowed['allow'] === true)
            return json_encode(array("output"=>"Evaluation for this assignment has been disabled. " . $eval_allowed['msg'], "valid"=>false));

        //Check if this is a preview; for now, just let all instructors preview...
        if(Auth::user()->instructor)
            $preview = true;

        try {
            $submission = Input::get('submission');
            $comparison = Input::get('comparison');
            $numMatches = Input::get('numMatches') ?? 0;
            $groups = Input::get('groups') ?? null;
            $feedback = Input::get('feedback');
            $eval = $question->evaluateMolecule($submission, $comparison, $numMatches, $groups, $feedback, $user, $preview);

            $attempts = $eval['attempts'];
            $answers = Answer::where('question_id', $question->id)->where('user_id', $user->id)->get();
            $result = Result::where('question_id', $question->id)->where('user_id', $user->id)->latest()->first();

            broadcast(new QuestionAnswered($question->assignment->course_id, $user, $question->assignment->id, $question->id, $result, $answers));
        }
        catch (Exception $e)
        {
            \Debugbar::info($e);
            //report($e);
            $eval["valid"] = false;
            $msg = $e->getMessage();

            $eval['output'] = "Error evaluating equation: $msg";
        }
        return json_encode($eval, JSON_PARTIAL_OUTPUT_ON_ERROR);
    }

    public function evaluate_for_user($cid, $aid, User $user) {
        return $this->evaluate_question($cid, $aid, $user, null, false, true);
    }

    public function evaluate_molecule_for_user($cid, $aid, User $user) {
        return $this->evaluate_molecule_question($cid, $aid, $user, null, true);
    }

    public function rescore_for_user($cid, $aid, $qid, User $user) {
        return $this->evaluate_question($cid, $aid, $user, $qid, true);
    }

    public function rescore_for_course_users($cid, $aid, $question) {

        $rescoreCnt = 0;
        foreach($question->assignment->course->users as $user) {
            $result = $this->rescore_for_user($cid, $aid, $question->id, $user);
            if($result != null)
                $rescoreCnt++;
        }
        return ['status' => 'success', 'msg' => 'Rescored question for '.$rescoreCnt. ($rescoreCnt == 1 ? ' student.' : ' students.'), 'cid' => $cid, 'aid' => $aid];
    }

    public function rescore_question($cid, $aid, \Illuminate\Http\Request $request) {
        $qid = $request->input('qid');
        $question = Question::with('assignment.course.users')->find($qid);
        if($question->type == self::MOLECULE_QUESTION)
            return ['status' => 'error', 'msg' => 'Molecule questions not available for rescoring' ]; //Molecule question rescoring has major issue; don't do it.
        return $this->rescore_for_course_users($cid, $aid, $question);
    }

    public function rescore_question_including_linked($cid, $aid, \Illuminate\Http\Request $request) {
        $qid = $request->input('qid');
        $question = Question::with('assignment.course.users','linked_questions.assignment.course.users')->find($qid);
        if($question->type == self::MOLECULE_QUESTION)
            return ['status' => 'error', 'msg' => 'Molecule questions not available for rescoring' ]; //Molecule question rescoring has major issue; don't do it.
        $status = [];
        $status[] =  $this->rescore_for_course_users($cid, $aid, $question);
        foreach($question->linked_questions as $linked) {
            $status[] = $this->rescore_for_course_users($linked->assignment->course->id, $linked->assignment->id, $linked);
        }
        $status = $this->compile_status($status, 'success');
        return ['status' => $status['status'], 'msg' => $status['msg']];
    }

    public function evaluate_question($cid,$aid, $user = null, $qid = null, $rescore = false, $preview = false) {
        if($user == null)
            $user = Auth::user();

        if (!Input::has('question') && $qid == null) {
            return json_encode(array("output"=>"No question supplied", "valid"=>false));
        }
        // Find the question based on the supplied question id
        if($qid == null)
            $qid = Input::get('question');

        $question = Question::with("assignment")->find($qid);
        $eval_allowed = $question->assignment->eval_allowed();
        if(!$eval_allowed['allow'] === true)
            return json_encode(array("output"=>"Evaluation for this assignment has been disabled. " . $eval_allowed['msg'], "valid"=>false));

        // Get the input data from the header
        $values = json_decode(Input::get('values'), true);

        //Check if this is a preview; for now, just let all instructors preview...
        if(Auth::user()->instructor)
            $preview = true;

        try {
            //Evaluate
            if ($question->type == Question::STANDARD_QUESTION) {
                if($rescore) {  //Don't re-evaluate if the user hasn't attempted the question.
                    if(Result::where('question_id', $question->id)->where('user_id', $user->id)->count() == 0)
                        return;
                }
                if(($question->options["isolated"] ?? false)) {
                    $values = $this->get_isolated_cached($values, $aid, $question->id, $user);
                }
                else
                    $values = $this->get_cached($values, $aid, $user);

                //$values = $this->validateValues($values);
                $values = $this->trimValues($values);
                if(($question->options["isolated"] ?? false))
                    $values = $this->get_isolated_cached($values, $aid, $question->id, $user);

                $values = $question->processIntermediates($values);

                \Debugbar::debug($user->lastname);
                \Debugbar::debug($values);

                $eval = $question->evaluate($values, $user, $rescore, $preview);

                //Cache the values if the evaluation was valid:
                if($eval['valid'] != false && !($question->options["isolated"] ?? false))
                    $this->cache_values($values, $aid, $user);
                elseif($eval['valid'] != false && ($question->options["isolated"] ?? false))
                    $this->cache_isolated_values($values, $aid, $qid, $user); //Need to store isolated values for rescoring purposes;

                if (Auth::user()->instructor != 1) {  //Don't return intermediate values if not an instructor.
                    unset($eval["interVars"]);
                    unset($eval["values"]);
                }
            }
            else {
                //Need to get values from existing answer if this is a re-score.
                if($rescore) {
                    $ans = Answer::where('question_id', $question->id)->where('user_id', $user->id)->latest()->first();
                    if($ans == null)
                        return;
                    if($question->type == Question::REACTION_QUESTION)
                        $values = [$qid => json_encode($ans->submission)];
                    else
                        $values = [$qid => $ans->submission];
                }
                \Debugbar::info($values);
                $values = $this->trimValues($values);
                //$values = $this->validateValues($values);
                $eval = $question->evaluate($values, $user, $rescore, $preview);
            }
            \Debugbar::addMessage('Eval object');
            \Debugbar::info($eval);

            $answers = Answer::where('question_id',$question->id)->where('user_id',$user->id)->get()
                ->keyBy(function($item) {
                if($item['variable_id'] == null)
                    return 0;
                else
                    return $item['variable_id'];
            });
            $result = Result::where('question_id',$question->id)->where('user_id',$user->id)->latest()->first();

            broadcast(new QuestionAnswered($question->assignment->course_id,$user,$question->assignment->id,$question->id, $result, $answers));
        }
        catch (Exception $e)
        {
            \Debugbar::info($e);
            //report($e);
            $eval["valid"] = false;
            $eqn = "";
            if (isset($e->equation))
            {
                $eqn = $e->equation;
            }
            $eqn = " in '$eqn'";
            $msg = $e->getMessage();
            if ($e->getCode() == EvaluateMethods::MSG_TO_USER)
                $eval['output'] = "$msg";
            else
                if (Auth::user()->instructor == 1)
                    $eval['output'] = "Error evaluating equation: $msg $eqn";
                else
                    $eval['output'] = "Error evaluating equation: $msg";
        }

        return json_encode($eval, JSON_PARTIAL_OUTPUT_ON_ERROR);

    }

    private function trimValues($values) {
        foreach($values as $key => $value)
        {
            if (is_numeric($value))
                $values[$key] = trim($value);
            else if (is_array($value))
            {
                foreach($value as $k => $v)
                {
                    if(is_array($v)) {  //Adds a second level of checking for arrays (for example, if an intermediate variable builds an array from other values).
                        foreach($v as $k2 => $v2)  //TODO do the array checking recursively to handle any depth (possibly use array_walk)
                            $values[$key][$k][$k2] = trim($v2);
                    }
                    else
                        $values[$key][$k] = trim($v);
                }
            }
            else {
                $values[$key] = trim($value);
            }
        }
        return $values;
    }

    private function validateValues($values)
    {
        foreach($values as $key => $value)
        {
            if (is_numeric($value))
                $values[$key] = floatval($value);
            else if (is_array($value))
            {
                foreach($value as $k => $v)
                {
                    $values[$key][$k] = floatval($v);
                }
            }
            else {
                $values[$key] = $value;
            }
        }
        return $values;
    }

    private function cache_values($values, $aid, $user = null)
    {
        if($user == null)
            $user = Auth::user();
        $cache = Cached::where("user_id", $user->id)
            ->where("assignment_id", $aid)
            ->latest()->first();
        if(!isset($cache))
            $cache = new Cached;
        $cache->values = json_encode($values,JSON_PARTIAL_OUTPUT_ON_ERROR);
        $cache->assignment_id = $aid;
        $cache->user_id = $user->id;
        $cache->save();
    }

    private function cache_isolated_values($values, $aid, $qid, $user = null) {
        if($user == null)
            $user = Auth::user();
        $cache = Cached::where("user_id", $user->id)
            ->where("assignment_id", $aid)
            ->latest()->first();
        if(!isset($cache))
            $cache = new Cached;
        $old = json_decode($cache->values, true);
        foreach($values as $name => $val) {
            $name .= "__" . $qid;
            $old[$name] = $val;
        }
        $cache->values = json_encode($old, JSON_PARTIAL_OUTPUT_ON_ERROR);
        $cache->assignment_id = $aid;
        $cache->user_id = $user->id;
        $cache->save();
    }

    private function get_cached($values, $aid, $user = null)
    {
        if($values == null)
            $values = [];
        if($user == null)
            $user = Auth::user();
        $cache = Cached::where("user_id", $user->id)
            ->where("assignment_id", $aid)
            ->latest()->first();
        if(!isset($cache))
            return $values;
        //\Debugbar::info($cache);
        try {
            //Get the cached values and replace with any new values
            $old = json_decode($cache->values, true);
            foreach ($values as $key => $value) {
                $old[$key] = $value;
            }

            return $old;
        }
        catch(Exception $e) {
            return $values;
        }
    }

    private function get_isolated_cached($values, $aid, $qid, $user = null) {
        if($values == null)
            $values = [];
        if($user == null)
            $user = Auth::user();
        $cache = Cached::where("user_id", $user->id)
            ->where("assignment_id", $aid)
            ->latest()->first();
        if(!isset($cache))
            return $values;
        try {
            //Get the cached values and replace with any new values
            $old = json_decode($cache->values, true);
            //Filter for only values in the cache that contain the isolation tag for the question
            $old = array_filter($old, function($key) use($qid) {
                \Debugbar::debug($key);
                return (strpos($key, '__'.$qid)!== false);
            }, ARRAY_FILTER_USE_KEY);
            //Strip isolation tag
            /*$old = array_map(function($item) use($qid) {
                return substr($item, 0, strpos($item, '__'.$qid));
            }, $old);*/
            foreach($old as $key => $value) {
                $newKey = substr($key, 0, strpos($key, '__'.$qid));
                $old[$newKey] = $value;
                unset($old[$key]);
            }
            \Debugbar::debug($old);
            foreach ($values as $key => $value) {
                $old[$key] = $value;
            }

            return $old;
        }
        catch(Exception $e) {
            \Debugbar::alert($e);
            return $values;
        }
    }

    /*------------------------------------------------------------------------
     * Assignment Administration
     *------------------------------------------------------------------------
     */


    public function remove_assignment($cid,$aid)
    {
        Assignment::find($aid)->delete();

        return Redirect::to('instructor/course/'.$cid.'/landing');

    }

    public function activate_assignment($cid,$aid)
    {
        //$assignment = Assignment::findorfail($aid);
        //$course = Course::find($assignment->course_id);
        //$course->addActiveToEnd($assignment);

        Assignment::where('id',$aid)->update(['active' => true]);

        return back()->with('status', 'Assignment activated.');
        //Redirect::to('instructor/course/'.$assignment->course->id.'/landing');
    }

    public function activate_assignment_including_linked($cid,$aid)
    {
        //$assignment = Assignment::findorfail($aid);
        //$course = Course::find($assignment->course_id);
        //$course->addActiveToEnd($assignment);

        Assignment::update(['active' => true])->find($aid);
        $count = Assignment::where('parent_assignment_id',$aid)->update(['active' => true]);

        return back()->with('status', 'Activated assignment including '.$count.' linked assignments.');
    }

    public function deactivate_assignment($cid,$aid)
    {
        $assignment = Assignment::findorfail($aid);

        $assignment->active = 0;
        $assignment->disabled = 0;
        $assignment->save();

        return Redirect::to('instructor/course/'.$assignment->course->id.'/landing');
    }

    public function deactivate_assignment_including_linked($cid,$aid)
    {
        $assignment = Assignment::where('id', $aid)->update(['active' => 0, 'disabled' => 0]);

        $count = Assignment::where('parent_assignment_id',$aid)->update(['active' => 0, 'disabled' => 0]);

        return back()->with('status', 'Deactivated assignment including '.$count.' linked assignments.');
    }

    public function disable_assignment($cid,$aid)
    {
        $assignment = Assignment::findorfail($aid);

        $assignment->disabled = 1;
        $assignment->save();

        return Redirect::to('instructor/course/'.$assignment->course->id.'/landing');
    }

    public function disable_assignment_including_linked($cid,$aid)
    {
        $assignment = Assignment::where('id', $aid)->update(['disabled' => 1]);

        $count = Assignment::where('parent_assignment_id',$aid)->update(['disabled' => 1]);

        return back()->with('status', 'Disabled assignment including '.$count.' linked assignments.');
    }

    public function update_extension($cid, $aid, \Illuminate\Http\Request $request) {
        $sid = json_decode($request->input('sid'));
        $allow = json_decode($request->input('allow'));
        $data = $request->all();
        $expiration = $request['expires_at'] ?? null;
        \Debugbar::debug($request['lock']);
        \Debugbar::info($request['lock'] === 'true');
        $lock = $request['lock'] === 'true' ? 1 : 0;
        $lock_message = $request['lock_message'] ?? null;

        $expires_at = $expiration == null ? null : Carbon::createFromFormat('Y-m-d H:i', $expiration);
        if($allow) {
            $extension = Extension::updateOrCreate(['assignment_id' => $aid, 'user_id' => $sid],['expires_at' => $expires_at, 'lock' => $lock, 'lock_message' => $lock_message]);
            return ['status' => 'success', 'extension' => true, 'ext' => $extension];
        }
        else {
            $extension = Extension::where(['assignment_id' => $aid, 'user_id' => $sid])->first();
            $extension->delete();
            return ['status' => 'success', 'extension' => false, 'ext' => $extension];
        }
        return $extension;
    }

    public function enable_assignment($cid,$aid)
    {
        $assignment = Assignment::findorfail($aid);

        $assignment->disabled = 0;
        $assignment->save();

        return Redirect::to('instructor/course/'.$assignment->course->id.'/landing');
    }

    public function enable_assignment_including_linked($cid,$aid)
    {
        $assignment = Assignment::where('id', $aid)->update(['disabled' => 0]);

        $count = Assignment::where('parent_assignment_id',$aid)->update(['disabled' => 0]);

        return back()->with('status', 'Enabled assignment including '.$count.' linked assignments.');
    }

    public static function duplicate_assignment($cid, $aid, $changeName = true) {
        $assignment = Assignment::find($aid);

        $new_assignment = $assignment->replicate();
        $new_assignment->course_id = $cid;
        if($changeName)
            $new_assignment->name = $assignment->name . " (duplicate)";
        $new_assignment->save();


        foreach ($assignment->questions as $question)
        {
            $new_question = $question->replicate();
            $new_question->assignment_id = $new_assignment->id;
            $new_question->save();

            // Copy Conditions
            foreach ($question->conditions as $condition)
            {
                $new_condition = $condition->replicate();
                $new_condition->question_id = $new_question->id;
                $new_condition->save();
            }
            // Copy Intermediate Variables
            foreach ($question->interVariables as $interVar)
            {
                $new_inter = $interVar->replicate();
                $new_inter->question_id = $new_question->id;
                $new_inter->save();
            }
            // Copy Variables
            foreach ($question->variables as $variable)
            {
                $new_variable = $variable->replicate();
                $new_variable->question_id = $new_question->id;
                $new_variable->save();
            }
            // if ($question->type == 2){
            foreach ($question->responses as $resp) {
                $new_resp = $resp->replicate();
                $new_resp->question_id = $new_question->id;
                $new_resp->save();
            }
        }
        return Redirect::to('instructor/course/'.$assignment->course->id.'/landing');
    }

    public function scheduler_landing($cid) {
        $course = Course::find($cid);
        $assignments = Assignment::where('course_id',$course->id)->withCount('linked_assignments')->orderBy('order')->get();
        $schedules = Schedule::where('course_id', $cid)->where('type','assignment')->get();

        $view = View::make('instructor.scheduleLanding');
        $data = array(
            'course' => $course,
            'assignments' => $assignments,
            'schedules' => $schedules,
        );
        $view->course = $course;
        $view->data = json_encode($data);
        return $view;
    }

    public function save_schedules($cid, \Illuminate\Http\Request $request) {
        $schedules = $request->input('schedules');
        foreach($schedules as $sch) {
            if($sch['id'] > 0) {
                $task = Schedule::find($sch['id']);
                if(array_key_exists('deleted', $sch) && $task != null) {
                    $task->delete();
                    continue;
                }
            }
            else {
                if(array_key_exists('deleted', $sch))
                    continue;
                $task = new Schedule();
            }

            $task->type = $sch['type'];
            $task->completed = $sch['completed'];
            $task->course_id = $cid;
            $task->details = $sch['details'];
            $task->enabled = $sch['enabled'];
            $task->time = new Carbon($sch['time']);
            $task->save();

        }
        $scheds = Schedule::where('course_id', $cid)->where('type','assignment')->get();
        return json_encode($scheds);
    }

    public static function run_task($task) {
        try {
            $assignment = Assignment::find($task->details['assignment_id']);
            $count = 0;
            if ($task->details['property'] == "Active") {
                //Set the requested assignment to the requested state
                $assignment->active = $task->details['state'];
                $assignment->save();
                if(isset($task->details['linked']) && $task->details['linked']==true)
                    $count = Assignment::where('parent_assignment_id',$task->details['assignment_id'])->update(['active' => $task->details['state']]);
            } else if ($task->details['property'] == "Disabled") {
                $assignment->disabled = $task->details['state'];
                $assignment->save();
                if(isset($task->details['linked']) && $task->details['linked']==true)
                    $count = Assignment::where('parent_assignment_id',$task->details['assignment_id'])->update(['disabled' => $task->details['state']]);
            } else if ($task->details['property'] == "Release Deferred") {
                $action = $task->details['state'] == true ? 'release' : 'defer';
                $val = $assignment->update_feedback_state($action, $task->details['linked'] ?? false);
                $count = $val['count'] ?? 0;
            }
            $task->completed = 1;
            $task->save();

            Log::debug('Ran task ' . $task->id . ' for course ' . $task->course_id . '. Type: ' . $task->type . '. Linked: '.$count);
        }
        catch(\Throwable $e) {
            Log::debug('Error running ' . $task->id . ' for course ' . $task->course_id . '. Type: ' . $task->type . '. ' . $e->getMessage());
            report($e);
            $task->completed = 1;
            $task->save();
        }
    }

    public function push_to_linked_courses($cid, $aid) {
        $course = Course::with('linked_courses:id,parent_course_id')->find($cid);
        $result = $course->push_assignment_to_linked_courses($aid);
        return back()->with($result['msgType'], $result['msg']);

    }

    public function unlink_from_parent($cid, $aid) {
        $assignment = Assignment::where('id',$aid)->update(['parent_assignment_id' => null]);
        return back()->with('status','Assignment unlinked from parent.');
    }

    /*------------------------------------------------------------------------
 * Assignment Import from excel .xlsx
 *------------------------------------------------------------------------
 */

    public function import_assignment($cid) {
        $files = Request::file('assignment_import');
        $msgs = [];
        foreach($files as $file) {
            $ext = $file->getClientOriginalExtension();
            if ($ext == "xlsx")
                array_push($msgs, $this->import_excel($cid, $file));
            elseif ($ext == "xml")
                array_push($msgs, $this->import_xml($cid, $file));
            else
                array_push($msgs, $this->return_import(null, "Please upload an appropriate file type.", $file));
        }

        $returnMsg = '';
        foreach ($msgs as $msg) {
            $returnMsg .= $msg['msg'] . '<br />';
        }
        return Redirect::back()->with("error", $returnMsg);
    }

    private function import_xml($cid, $file) {
        $name = $file->getClientOriginalName();

        try {
            $contents = file_get_contents($file->getRealPath());
            $tags = array('description','info','equation','name','title','result','text','molecule','options','choices');
            foreach($tags as $tag) {
                $contents = str_replace('<'.$tag.'>','<'.$tag.'><![CDATA[',$contents);
                $contents = str_replace('</'.$tag.'>',']]></'.$tag.'>',$contents);
            }
            $contents = str_replace('<br>','<br/>',$contents);

            $assignment = new Assignment();

            $xml = simplexml_load_string($contents);

            $assignment->creator_id = Auth::id();
            $assignment->course_id = $cid;

            $assignment->active = 0;
            $assignment->name = $xml->name;
            $assignment->description = $xml->description;
            $assignment->info = $xml->info;
            $assignment->order = Assignment::where('course_id',$cid)->max('order')+1;
            if(isset($xml->type) && is_numeric($xml->type))
                $assignment->type = $xml->type;
            else
                $assignment->type = 1;
            $assignment->options = json_decode($xml->options);
            $assignment->save();

            $resp = $this->parse_xml_questions($assignment, $xml);
        }
        catch (Exception $e) {
            $assignment->delete();
            \Debugbar::info($e);
            return $this->return_import($name, $e->getMessage().' '.$e->getLine(), $file); // return back with error message
        }

        return $this->return_import($name, "Assignment imported!", $file);
    }

    private function parse_xml_questions($assignment, $xml) {
        $question_num = 0;

        foreach ($xml->question as $key => $q) {
            $question_num++;
            if($q->name == NULL){
                $importMsg = "Please provide names for all questions.";
                $assignment->delete();
                return $this->return_import($name, $importMsg, $file);
            }

            $newQ = new Question();
            $newQ->type = $q->type;
            $newQ->name = $q->name;
            $newQ->description = $q->description;
            $newQ->assignment()->associate($assignment);
            $newQ->order = $question_num;
            $extra = new \stdClass();
            $extra->text = (string) $q->extra->text;
            $extra->available = $q->extra->available == 1 || $q->extra->available == 'true' ? true : false;
            $extra->newValues = $q->extra->newValues == 'true' ? true : false;
            $newQ->extra = $extra;
            try {
                $newQ->options = json_decode($q->options);
            }
            catch (\Exception $e) {}
            if(!empty($q->deferred))
                $newQ->deferred = $q->deferred;

            try {
                $newQ->save(); // initial save for access to $newQ->id
                if ($q->type == Question::SHORT_ANSWER_QUESTION) {
                    $this->import_xml_short_answer($newQ, $q);
                }
                elseif ($q->type == Question::SIMPLE_QUESTION) {
                    $this->import_xml_simple($newQ, $q);
                }
                elseif ($q->type == Question::STANDARD_QUESTION) {
                    $this->import_xml_standard($newQ, $q);
                }
                elseif ($q->type == Question::SIMPLE_TEXT_QUESTION) {
                    $this->import_xml_simple_text($newQ, $q);
                }
                elseif ($q->type == Question::MOLECULE_QUESTION) {
                    $this->import_xml_molecule($newQ, $q);
                }
                elseif ($q->type == Question::MULTIPLE_CHOICE_QUESTION) {
                    $this->import_xml_multiple_choice($newQ, $q);
                }
                elseif ($q->type == Question::REACTION_QUESTION) {
                    $this->import_xml_reaction($newQ, $q);
                }
                elseif ($q->type == Question::UNANSWERED_QUESTION) {
                    $newQ->save();
                }
                else {
                    throw new Exception('Invalid type for question '. $question_num);
                }
            }
            catch (Exception $e) {
                \Debugbar::info($e);
                throw new Exception($e->getMessage() . " (Question $question_num) ");
            }
        }
    }

    private function import_xml_short_answer($newQ, $q) {  //TODO add error handling
        $newQ->max_points = $q->max_points;
        $newQ->save();
        foreach($q->response as $response) {
            $new_resp = new ProfResponse;
            $new_resp->response = trim($response);
            $new_resp->question_id = $newQ->id;
            $new_resp->question()->associate($newQ);
            $new_resp->save();
        }
    }

    private function import_xml_simple($newQ, $q) {
        $newQ->answer = $q->answer;
        $newQ->tolerance = $q->tolerance;
        $newQ->tolerance_type = $q->tolerance_type;
        $newQ->max_points = $q->max_points;
        $newQ->feedback = $q->feedback;
        $newQ->save();
    }

    private function import_xml_simple_text($newQ, $q) {
        $newQ->answer = $q->answer;
        $newQ->tolerance = 0;
        $newQ->tolerance_type = $q->case_sensitive;
        $newQ->max_points = $q->max_points;
        $newQ->feedback = $q->feedback;
        $newQ->save();
    }

    private function import_xml_molecule($newQ, $q) {
        $newQ->answer = $q->answer;
        $newQ->tolerance = 0;
        $newQ->max_points = $q->max_points;
        $newQ->feedback = $q->feedback;
        $newQ->molecule = json_decode($q->molecule);
        $newQ->save();
    }

    private function import_xml_multiple_choice($newQ, $q) {
        $newQ->answer = $q->answer;
        $newQ->tolerance = 0;
        $newQ->max_points = $q->max_points;
        $newQ->feedback = $q->feedback;
        $newQ->choices = json_decode($q->choices);
        $newQ->save();
    }

    private function import_xml_reaction($newQ, $q) {
        $newQ->answer = $q->answer;
        $newQ->tolerance = 0;
        $newQ->max_points = $q->max_points;
        $newQ->feedback = $q->feedback;
        $newQ->save();
    }

    private function import_xml_standard($newQ, $q) {
        $vcnt = 0;
        $ivcnt = 0;
        $ccnt = 0;
        foreach($q->variable as $num => $v) {
            $vcnt++;
            $this->import_xml_standard_variable($newQ, $vcnt, $v);
        }
        foreach($q->intermediate_variable as $num => $iv) {
            $ivcnt++;
            $this->import_xml_stanard_intervar($newQ, $ivcnt, $iv);
        }
        foreach($q->condition as $num => $c) {
            $ccnt++;
            $this->import_xml_standard_condition($newQ, $ccnt, $c);
        }
        $newQ->max_points = collect($newQ->conditions)->max('points');
        $newQ->save();
    }

    private function import_xml_standard_variable($newQ, $num, $v) {
        try {
            $newVar = new Variable();
            $newVar->name = $v->name;
            $newVar->title = $v->title;
            $newVar->descript = $v->description;
            $newVar->type = $v->type;
            $newVar->order = $num;
            try {
                $newVar->choices = json_decode($v->choices);
            }
            catch (\Exception $e) {}
            $newVar->question()->associate($newQ);
            $newVar->save();
        }
        catch (Exception $e) {
            $num++;
            throw new Exception("Error parsing variable $num");
        }
    }

    private function import_xml_stanard_intervar($newQ, $num, $iv) {
        try {
            $newVar = new InterVariable;
            $newVar->name = $iv->name;
            $newVar->equation = $iv->equation;
            $newVar->order = $num;
            $newVar->question()->associate($newQ);
            $newVar->save();
        }
        catch (Exception $e) {
            $num++;
            throw new Exception("Error parsing intermediate variable $num");
        }
    }

    private function import_xml_standard_condition($newQ, $num, $c) {
        try {
            $newC = new Condition;
            $newC->equation = $c->equation;
            $newC->result = $c->result;
            $newC->points = $c->points;
            $newC->type = $c->type;
            $newC->order = $num;
            $newC->question()->associate($newQ);
            $newC->save();
        }
        catch (Exception $e) {
            $num++;
            throw new Exception("Error parsing condition $num");
        }
    }


    private function import_excel($id, $file) {
        //$file = Request::file('assignment_import');
        $importMsg = "Assignment imported!";
        $name = null;

        $valid = $this->validateFile($file, $id);
        if (!$valid['valid']) {
            return $this->return_import($name, $valid['msg'], $file);
        }
        else {
            $name = $file->getClientOriginalName();
            //$file = $file->move(getcwd(), $name);
            $questions = Excel::load($file)->all(); // array from file

            $assignment = new Assignment();
            $assignment->creator_id = Auth::id();
            $assignment->course_id = $id;
            $assignment->active = 0;
            $assignment->name = substr($name, 0, -5);
            if ($questions[0]['assignment_description'] !== null)
                $assignment->description = $questions[0]['assignment_description'];
            try {
                $assignment->save();
            }
            catch (Exception $e) {
                return $this->return_import($name, $e->getMessage(), $file);
            }

            return $this->initialize_assignment($questions, $name, $assignment, $file);
        }

    }

    public function initialize_assignment($questions, $name, $assignment, $file) {

        try {
            $this->clean_empty_questions($questions);
            $this->parse_questions($questions, $assignment, $file);
        }
        catch (Exception $e) {
            $assignment->delete();
            return $this->return_import($name, $e->getMessage().' '.$e->getLine(), $file); // return back with error message
        }
        return $this->return_import($name, "Assignment imported!", $file);
    }

    private function parse_questions($questions, $assignment, $file) {
        $k = 1;
        $question_num = 0;

        foreach ($questions as $key => $q) {
            $question_num++;
            if($q["questionsname_question_description_answer_options"] == NULL){
                $importMsg = "Please provide names for all questions.";
                $assignment->delete();
                return $this->return_import($name, $importMsg, $file);
            }

            $question_info = explode('~', trim($q["questionsname_question_description_answer_options"]));
            $q_name = trim($question_info[0], '~');

            $newQ = new Question();
            if (count($question_info) >= 5)
                $newQ->name = "Question";
            else
                $newQ->name = $q_name;
            $newQ->assignment()->associate($assignment);
            $newQ->order = $question_num;

            try {
                $newQ->save(); // initial save for access to $newQ->id
                if (count($question_info) == 3) {
                    $this->parse_short_answer($q, $question_info, $newQ);
                }
                else if (count($question_info) >= 5) {
                    $newQ->description = $q_name;
                    $this->parse_simple($q, $question_info, $newQ);
                }
                else if (count($question_info) == 2 || count($question_info) == 1) {
                    $this->parse_std($q, $question_info, $newQ);
                }
                else {
                    throw new Exception('Invalid formatting in question '. $question_num);
                }
            }
            catch (Exception $e) {
                throw new Exception($e->getMessage() . " (Question $question_num)");
            }
        }
    }

    private function parse_short_answer($q, $info, $newQ) {
        $responses = array();
        $newQ->max_points = 1;
        $newQ->type = Question::SHORT_ANSWER_QUESTION;

        if (isset($info[1]))
            $newQ->description = $info[1];
        if (isset($info[2]))
            if (is_numeric(trim($info[2])))
                $newQ->max_points = $info[2];
            else
                throw new Exception("Please enter numerical value for points. [question ~ description ~ points]", 1);


        $parsed_responses = explode('~', $q["prewritten_responsesresponse_1_response_2_etc"]);
        foreach ($parsed_responses as $r) {
            if (trim($r) !== "") {
                $new_resp = new ProfResponse;
                $new_resp->response = trim($r);
                $new_resp->question_id = $newQ->id;
                $new_resp->question()->associate($newQ);
                $new_resp->save();
            }
        }

        $newQ->save();
    }

    private function parse_simple($q, $info, $newQ) {
        $newQ->type = Question::SIMPLE_QUESTION;
        try {
            if (!(is_numeric(trim($info[1])) && is_numeric(trim($info[2])) && is_numeric(trim($info[3])) && is_numeric(trim($info[4])))) {
                throw new Exception('Error in simple question. Must enter numerical values.', 1);
            }

            $newQ->answer = $info[1];
            $newQ->tolerance = $info[2];
            $newQ->tolerance_type = $info[3];
            $newQ->max_points = $info[4];
            if (isset($info[5]))
                $newQ->feedback = $info[5];

            $newQ->save();
        }
        catch (Exception $e) {
            throw $e;
        }
    }

    private function parse_std($q, $info, $newQ) {
        $newQ->type = $this->unanswered($q) ? Question::UNANSWERED_QUESTION : Question::STANDARD_QUESTION;
        if (isset($info[1]))
            $newQ->description = $info[1];

        try {
            if ($newQ->type == Question::STANDARD_QUESTION) {
                $this->parse_variables($q, $newQ);
                $this->parse_conditions($q, $newQ);
                $this->parse_intermediates($q, $newQ);
            }
            $newQ->max_points = collect($newQ->conditions)->max('points');
            $newQ->save();
        }
        catch (Exception $e) {
            throw $e;
        }
    }

    private function unanswered($q) {
        $unanswered = false;
        if ($q["variablesname_title_description_type"] == null &&
            $q["conditionsequation_result_points_type"] == null &&
            $q["intermediate_variablesname_equation"] == null &&
            $q["prewritten_responsesresponse_1_response_2_etc"] == null)
            $unanswered = true;

        return $unanswered;
    }

    private function parse_variables($q, $newQ) {
        if ($q["variablesname_title_description_type"] == null) {
            throw new Exception("Please provide at least one variable");
        }

        $parsed_vars = explode(PHP_EOL, trim($q["variablesname_title_description_type"]));
        try {
            foreach ($parsed_vars as $num => $v) {
                $newVar = new Variable();
                $newVar->name = trim(explode('~', $v)[0]);
                $newVar->title = trim(explode('~', $v)[1]);
                $newVar->descript = trim(explode('~', $v)[2]);
                $newVar->type = trim(explode('~', $v)[3]);
                $newVar->question()->associate($newQ);
                $newVar->save();
            }
        }
        catch (Exception $e) {
            $num++;
            throw new Exception("Error parsing variable $num", $num);
        }
    }

    private function parse_conditions($q, $newQ) {
        if ($q["conditionsequation_result_points_type"] == null) {
            throw new Exception("Please provide at least one condition");
        }

        $parsed_conds = explode(PHP_EOL, trim($q["conditionsequation_result_points_type"]));

        try {
            $default = false;
            foreach ($parsed_conds as $num => $c) {
                $newC = new Condition;
                $newC->equation = trim(explode('~', $c)[0]);
                if ($newC->equation == "1")
                    $default = true;
                $newC->result = trim(explode('~', $c)[1]);
                $newC->points = trim(explode('~', $c)[2]);
                if(count(explode('~', $c)) == 4)
                    $newC->type = trim(explode('~', $c)[3]);
                else
                    $newC->type = '1';
                $newC->question()->associate($newQ);
                $newC->save();
            }
            // catch all condition, added in for user, only if not added in
            if (!$default) {
                $newC = new Condition;
                $newC->equation = "1";
                $newC->result = "Incorrect.";
                $newC->points = '0';
                $newC->type = '1';
                $newC->question()->associate($newQ);
                $newC->save();
            }
        }
        catch (Exception $e) {
            $num++;
            throw new Exception("Error parsing condition $num", $num);
        }
    }

    private function parse_intermediates($q, $newQ) {
        $parsed_inters = explode(PHP_EOL, trim($q["intermediate_variablesname_equation"]));

        try {
            foreach ($parsed_inters as $num => $iv) {
                if ($iv != "") {
                    $newVar = new InterVariable;
                    $newVar->name = trim(explode('~', $iv)[0]);
                    $newVar->equation = trim(explode('~', $iv)[1]);
                    $newVar->question()->associate($newQ);
                    $newVar->save();
                }
            }
        }
        catch (Exception $e) {
            $num++;
            throw new Exception("Error parsing intermediate variable $num", $num);
        }
    }

    public function clean_empty_questions($questions) {
        foreach ($questions as $key => $q) {
            unset($q["assignment_description"]);
            if($q["questionsname_question_description_answer_options"] == NULL &&
                $q["variablesname_title_description_type"] == NULL &&
                $q["conditionsequation_result_points_type"] == NULL &&
                $q["intermediate_variablesname_equation"] == NULL &&
                $q["prewritten_responsesresponse_1_response_2_etc"] == NULL)
                unset($questions[$key]);
        }
    }

    public function validateFile($file, $id) {
        $ret = [
            "valid" => true,
            "msg" => ""
        ];

        if ($file == null) {
            $ret["valid"] = false;
            $ret["msg"] = "No file selected";
        }
        else if (!$file->isValid()) {
            $ret['msg'] = "File is invalid";
            $ret['valid'] = false;
        }
        else if (strcmp($file->getMimeType(), "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") != 0) {
            $ret['msg'] = "File unreadable, please upload an Excel (.xlsx) file using the template";
            $ret['valid'] = false;
        }

        return $ret;
    }

    /*private function delete_file($name, $importMsg, $file) {
        File::delete(getcwd().'/'.$name);
        File::delete($file);
        //$view = View::make('instructor.course.landing');
        //$view->course = $course;
        //return $view;
        return Redirect::back()->with("error", $importMsg);
    }*/


    public function return_import($name, $importMsg, $file) {
        return ["status" => "error", "msg" => $name .': ' . $importMsg];
    }

    /*------------------------------------------------------------------------
    * Assignment Export to XML
    *------------------------------------------------------------------------
    */

    public function export_xml($cid,$aid) {
        $assignment = Assignment::find($aid);

        $questions = $assignment->questions->toArray();
        $questions_obj = $assignment->questions;

        foreach ($questions_obj as $q) {
            $conditions[] = $q->conditions->toArray();
            $variables[] = $q->variables->toArray();
            $interVariables[] = $q->interVariables->toArray();
            $responses[] = $q->responses->toArray();
        }

        $export = '<assignment>'.PHP_EOL; // fill this array => excel
        $export .= "\t".'<name>'.$assignment->name.'</name>'.PHP_EOL;
        $export .= "\t".'<description>'.$assignment->description.'</description>'.PHP_EOL;
        $export .= "\t".'<info>'.$assignment->info.'</info>'.PHP_EOL;
        $export .= "\t".'<type>'.$assignment->type.'</type>'.PHP_EOL;
        $export .= "\t".'<options>'.json_encode($assignment->options).'</options>'.PHP_EOL;

        $i = 0;
        foreach ($questions as $q) {
            $export .= "\t".'<question>'.PHP_EOL;
            $export .= "\t\t".'<type>'.$q['type'].'</type>'.PHP_EOL;
            $export .= "\t\t".'<name>'.$q['name'].'</name>'.PHP_EOL;
            $export .= "\t\t".'<description>'.$q['description'].'</description>'.PHP_EOL;
            $export .= "\t\t".'<deferred>'.$q['deferred'].'</deferred>'.PHP_EOL;
            $export .= "\t\t".'<options>'.json_encode($q['options']).'</options>'.PHP_EOL;
            $export .= "\t\t".'<extra>'.PHP_EOL;
            try {
                $export .= "\t\t\t" . '<available>' . ($q['extra']['available'] == false ? 'false' : 'true') . '</available>' . PHP_EOL;
            }
            catch(Exception $e) {
                $export .= "\t\t\t" . '<available>0</available>' . PHP_EOL;
            }
            try {
                $export .= "\t\t\t" . '<newValues>' . ($q['extra']['newValues'] == false ? 'false' : 'true') . '</newValues>' . PHP_EOL;
            }
            catch(Exception $e) {
                $export .= "\t\t\t" . '<newValues>false</newValues>' . PHP_EOL;
            }
            $export .= "\t\t\t".'<text>'.$q['extra']['text'].'</text>'.PHP_EOL;
            $export .= "\t\t".'</extra>'.PHP_EOL;

            if ($q['type'] == Question::STANDARD_QUESTION) {
                $export .= $this->xml_variables($variables[$i]);
                $export .= $this->xml_inter_variables($interVariables[$i]);
                $export .= $this->xml_conditions($conditions[$i]);
            }
            elseif ($q['type'] == Question::SIMPLE_QUESTION) {
                $export .= "\t\t".'<answer>'.$q['answer'].'</answer>'.PHP_EOL;
                $export .= "\t\t".'<tolerance>'.$q['tolerance'].'</tolerance>'.PHP_EOL;
                $export .= "\t\t".'<tolerance_type>'.$q['tolerance_type'].'</tolerance_type>'.PHP_EOL;
                $export .= "\t\t".'<max_points>'.$q['max_points'].'</max_points>'.PHP_EOL;
                $export .= "\t\t".'<feedback>'.$q['feedback'].'</feedback>'.PHP_EOL;
            }
            elseif ($q['type'] == Question::SIMPLE_TEXT_QUESTION) {
                $export .= "\t\t".'<answer>'.$q['answer'].'</answer>'.PHP_EOL;
                $export .= "\t\t".'<case_sensitive>'.$q['tolerance_type'].'</case_sensitive>'.PHP_EOL;
                $export .= "\t\t".'<max_points>'.$q['max_points'].'</max_points>'.PHP_EOL;
                $export .= "\t\t".'<feedback>'.$q['feedback'].'</feedback>'.PHP_EOL;
            }
            else if ($q['type'] == Question::SHORT_ANSWER_QUESTION) {
                $export .= "\t\t".'<max_points>'.$q['max_points'].'</max_points>'.PHP_EOL;
                $export .= $this->xml_responses($responses[$i]);
            }
            else if ($q['type'] == Question::MOLECULE_QUESTION) {
                $export .= "\t\t".'<max_points>'.$q['max_points'].'</max_points>'.PHP_EOL;
                $export .= "\t\t".'<feedback>'.$q['feedback'].'</feedback>'.PHP_EOL;
                $export .= "\t\t".'<molecule>'.json_encode($q['molecule']).'</molecule>'.PHP_EOL;
            }
            else if ($q['type'] == Question::REACTION_QUESTION) {
                $export .= "\t\t".'<max_points>'.$q['max_points'].'</max_points>'.PHP_EOL;
                $export .= "\t\t".'<feedback>'.$q['feedback'].'</feedback>'.PHP_EOL;
                $export .= "\t\t".'<answer>'.json_encode($q['answer']).'</answer>'.PHP_EOL;
            }
            else if ($q['type'] == Question::MULTIPLE_CHOICE_QUESTION) {
                $export .= "\t\t".'<answer>'.$q['answer'].'</answer>'.PHP_EOL;
                $export .= "\t\t".'<max_points>'.$q['max_points'].'</max_points>'.PHP_EOL;
                $export .= "\t\t".'<feedback>'.$q['feedback'].'</feedback>'.PHP_EOL;
                $export .= "\t\t".'<choices>'.json_encode($q['choices']).'</choices>'.PHP_EOL;
            }

            $export .= "\t".'</question>'.PHP_EOL;
            $i++;
        }
        $export .= '</assignment>';

        return Response::make($export, '200', array(
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="'.$assignment->name.'.xml"'
        ));

    }

    private function xml_variables($variables) {
        $export = "";

        foreach ($variables as $key => $v) {
            $export .= "\t\t".'<variable>'.PHP_EOL;
            $export .= "\t\t\t".'<name>'.$v['name'].'</name>'.PHP_EOL;
            $export .= "\t\t\t".'<title>'.$v['title'].'</title>'.PHP_EOL;
            $export .= "\t\t\t".'<description>'.$v['descript'].'</description>'.PHP_EOL;
            $export .= "\t\t\t".'<type>'.$v['type'].'</type>'.PHP_EOL;
            $export .= "\t\t\t".'<choices>'.json_encode($v['choices']).'</choices>'.PHP_EOL;
            $export .= "\t\t".'</variable>'.PHP_EOL;
        }
        return $export;
    }

    private function xml_inter_variables($interVariables) {
        $export = "";

        foreach ($interVariables as $key => $iv) {
            $export .= "\t\t".'<intermediate_variable>'.PHP_EOL;
            $export .= "\t\t\t".'<name>'.$iv['name'].'</name>'.PHP_EOL;
            $export .= "\t\t\t".'<equation>'.$iv['equation'].'</equation>'.PHP_EOL;
            $export .= "\t\t".'</intermediate_variable>'.PHP_EOL;
        }
        return $export;
    }

    private function xml_conditions($conditions) {
        $export = "";

        foreach ($conditions as $key => $c) {
            $export .= "\t\t".'<condition>'.PHP_EOL;
            $export .= "\t\t\t".'<equation>'.$c['equation'].'</equation>'.PHP_EOL;
            $export .= "\t\t\t".'<result>'.$c['result'].'</result>'.PHP_EOL;
            $export .= "\t\t\t".'<points>'.$c['points'].'</points>'.PHP_EOL;
            $export .= "\t\t\t".'<type>'.$c['type'].'</type>'.PHP_EOL;
            $export .= "\t\t".'</condition>'.PHP_EOL;
        }
        return $export;
    }

    private function xml_responses($responses) {
        $export = "";

        foreach ($responses as $key => $r) {
            $export .= "\t\t".'<response>'.$r['response'].'</response>'.PHP_EOL;
        }
        return $export;
    }
}
