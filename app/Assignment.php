<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\QuizJob;
use Carbon\Carbon;
use Redirect;
use Auth;

class Assignment extends Model
{
    protected $fillable = ['name', 'description', 'info', 'options', 'closes_at'];

    protected $casts = [
        'options' => 'array',
        'closes_at' => 'datetime:Y-m-d H:i',
    ];

    public function setClosesAtAttribute($date) {
        $this->attributes['closes_at'] = empty($date) ? null : Carbon::parse($date);
    }


    public function course() {
        return $this->belongsTo('App\Course');
    }

    public function creator() {
        return $this->belongsTo('App\User');
    }

    public function linked_assignments() {
        return $this->hasMany('App\Assignment','parent_assignment_id');
    }

    public function linked_parent_assignment() {
        return $this->hasOne('App\Assignment','id','parent_assignment_id')->select('id','course_id');
    }

    public function questions() {
        return $this->hasMany('App\Question')->orderBy('order');
    }

    public function unorderedQuestions() {
        return $this->hasMany('App\Question');
    }

    public function variables() {
        return $this->hasManyThrough('App\Variable', 'App\Question');
    }

    public function results() {
        return $this->hasManyThrough('App\Result', 'App\Question')->orderBy('results.updated_at','desc');
    }

    public function ungraded_results() {
        return $this->hasManyThrough('App\Result', 'App\Question')->where('status',0)->orderBy('results.updated_at','desc');
    }

    public function deferred_results() {
        return $this->hasManyThrough('App\Result', 'App\Question')->where('status',3);
    }

    public function unorderedResults() {  //Update on results model doesn't work when using the orderBy on the results() function.
        return $this->hasManyThrough('App\Result', 'App\Question');
    }

    public function answers() {
        return $this->hasMany('App\Answer');
    }

    /*public function writtenAnswers() {
        return $this->hasMany('App\Answer')
            ->whereHas('question', function($q) {
                $q->where('type',2);
            });
    }*/

    public function cachedAnswers() {
        return $this->hasMany('App\CachedAnswer');
    }

    public function hands() {
        return $this->hasMany('App\Hand');
    }

    public function quiz_jobs() {
        return $this->hasMany('App\QuizJob');
    }

    public function extensions() {
        return $this->hasMany('App\Extension');
    }

    public function extension() {
        return $this->hasOne('App\Extension')->latest();
    }



    public function get_user_cache($user_id) {
        return $this->cachedAnswers()->where('user_id', $user_id)->latest()->first();
    }

    public function generate_linked_assignment($cid) {
        $linked = $this->replicate();
        $linked->parent_assignment_id = $this->id;
        $linked->course_id = $cid;
        $linked->save();
        foreach($this->questions as $q) {
            $q->generate_linked_question($linked->id);
        }
        $linked->options = $this->set_assignment_options($linked->options, true, $linked->id);
        $linked->save();
        return $linked;
    }

    //Note that this function currently lives and is used here and in the AssignmentController.  Updates should
    //happen both places (or, better, get refactored to have one function).
    //Note that this function has options as an array whereas in the controller, it's an object.
    private function set_assignment_options($options, $linked, $assignment_id) {
        if(!$linked || !isset($options['quiz']))
            return $options;
        foreach($options['quiz']['pages'] as $kp => $page) {
            foreach($page['groups'] as $kg => $group) {
                foreach($group['question_ids'] as $kq => $qid) {
                    $qid = \DB::table('questions')
                        ->where('parent_question_id', $qid)->where('assignment_id',$assignment_id)->value('id');
                    $options['quiz']['pages'][$kp]['groups'][$kg]['question_ids'][$kq] = $qid;
                }
            }
        }
        return $options;
    }

    public function update_feedback_state($action, $include_linked = false) {
        try {
            if($action == 'release') {
                $initial = 3;
                $final = 1;
            }
            else if($action == 'defer') {
                $initial = 1;
                $final = 3;
            }
            else
                return ['status' => 'error', 'msg' => 'Invalid action supplied.', 'aid' => $this->id, 'cid' => $this->course_id];

            //If it's a quiz or the action is to release, apply to everything
            if($this->type == 2 || 'action' == 'release')
                $count=$this->unorderedResults()->where('status',$initial)->update(['status' => $final]);
            //Otherwise, only redefer feedback for results that belong to questions marked as deferred.
            else
                $count=$this->unorderedResults()->whereHas('question', function($q) {$q->where('deferred',1);})->where('status',$initial)->update(['status' => $final]);
            $actionMsg = $action == 'release' ? 'released' : 'redeferred';

            if($include_linked) {
                $linked_assignments = $this->linked_assignments;

                foreach($linked_assignments as $linked) {
                    $status[] = $linked->update_feedback_state($action, false);
                }
                $returnVal = $this->compile_status($status);
                $returnVal['count'] = count($linked_assignments);
                return $returnVal;
            }

            return ['status' => 'status', 'msg' => "Feedback $actionMsg for ".$count." results.", 'aid' => $this->id, 'cid' => $this->course_id];
        }
        catch(\Exception $e) {
            report($e);
            return ['status' => 'error','msg' => 'Failure during feedback redeferral: '.$e->getMessage()];
        }
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

    public function eval_allowed() {
        if(Auth::user()->instructor == 1)
            return ['allow' => true, 'msg' => null];
        $extension = $this->extensions()->where('user_id',Auth::id())->latest()->first();
        if($extension != null && $extension->lock == true)
            return ['allow' => false, 'msg' => $extension->lock_message];
        if($this->disabled || ($this->closes_at !== null && $this->closes_at->lt(Carbon::now()))) {
            if($extension != null && ($extension->expires_at == null || $extension->expires_at->gt (Carbon::now())))
                return ['allow' => true, 'msg' => null];
            return ['allow' => false, 'msg' => $this->disabled ? null : 'This assignment is closed.'];
        }

        return ['allow' => true, 'msg' => null];
    }

    //If missingOnly is true, only generate jobs for users who do not yet have one.
    //If missingOnly is false, delete all old jobs and generate new ones.
    public function generate_quizzes($missingOnly = false) {
        try {
            $students = $this->course->users;
            $options = $this->options['quiz'];
            if(!$missingOnly)
                QuizJob::where('assignment_id', $this->id)->delete(); //Doing this instead of updateOrCreate because of problems setting actual_start to null
            else
                $jobs = QuizJob::where('assignment_id', $this->id)->select('id','assignment_id','user_id')->get();
            $count = 0;

            foreach ($students as $student) {
                if($missingOnly && $jobs->contains('user_id',$student->id))
                    continue;
                $this->generate_quiz_job($student->id, $options);
                $count++;

            }
            return ['status' => 'status', 'msg' => 'Generated ' . $count . ' quiz jobs.', 'aid' => $this->id, 'cid' => $this->course_id];
        }
        catch(\Exception $e) {
            return ['status' => 'error', 'msg' => 'Error during quiz job generation: '.$e->getMessage(), 'aid' => $this->id, 'cid' => $this->course_id];
        }
    }

    public function generate_quiz_job_for_student($sid) {
        QuizJob::where('assignment_id', $this->id)->where('user_id',$sid)->delete(); //Doing this instead of updateOrCreate because of problems setting actual_start to null
        $options =  $this->options['quiz'];
        $this->generate_quiz_job($sid, $options);
    }

    private function generate_quiz_job($student_id, $options) {
        $question_list = $this->quiz_questions($options);

        $q = QuizJob::updateOrcreate(
            ['user_id' => $student_id,
                'assignment_id' => $this->id,
                'allowed_start' => Carbon::createFromFormat('Y-m-d H:i', $options['allowed_start']),//$options['allowed_start'],
                'allowed_end' => Carbon::createFromFormat('Y-m-d H:i', $options['allowed_end']),//$options['allowed_end'],
                'allowed_minutes' => $this->get_user_allowed_minutes($student_id, $options['allowed_length']),
                'status' => 0,
                'question_list' => $question_list,
            ]);
        $q->save();
    }

    private function get_user_allowed_minutes($sid, $minutes) {
        $user = User::find($sid);
        $multiplier = $user->courses->find($this->course_id)->pivot->multiplier;
        $multiplier = $multiplier == null ? 1 : $multiplier;
        return $multiplier*$minutes;
    }

    //Update start stop and allowed minutes for all students for the quiz.  Assumes existing quizzes.
    public function update_timings() {
        try {
            $options = $this->options['quiz'];
            $count = QuizJob::where('assignment_id', $this->id)->update([
                'allowed_start' => Carbon::createFromFormat('Y-m-d H:i', $options['allowed_start']),//$options['allowed_start'],
                'allowed_end' => Carbon::createFromFormat('Y-m-d H:i', $options['allowed_end']),//$options['allowed_end'],
                'allowed_minutes' => $options['allowed_length'],
            ]);
        }
        catch(\Exception $e) {
            report($e);
            return ['status' => 'error', 'msg' => 'Failed to update timings: '.$e->getMessage(), 'aid' => $this->id, 'cid' => $this->course_id];
        }
        return ['status' => 'success', 'msg' => 'Updated timings for '. $count .' quiz jobs.', 'aid' => $this->id, 'cid' => $this->course_id];
    }

    public function update_settings($new_options) {
        try {
            \Debugbar::info($new_options);
            $this->options = $new_options;
            $this->save();
            return ["status" => 'success', 'options' => $this->options];
        }
        catch(\Exception $e) {
            report($e);
            return ['status' => 'fail', 'message' => $e->getMessage()];
        }
    }

    private function quiz_questions($options) {
        $pages = $this->quiz_pages($options);
        return $pages;
    }

    private function quiz_pages($options) {
        $pages = $options['pages'];
        if(isset($options['shuffle_pages']) && $options['shuffle_pages'])
            shuffle($pages);
        $question_pages = [];
        $used_question_ids = [];
        foreach($pages as $index => $page) {
            $question_page = $this->quiz_groups($page, $used_question_ids);
            $question_pages[$index]['ids'] = $question_page; //Set the list of question ids for the page.
            $question_pages[$index]['status'] = 0;  //Set page to not started
            $used_question_ids = array_merge($used_question_ids, $question_page); //Keep track of questions already added to avoid duplication.
        }
        return $question_pages;
    }

    private function quiz_groups($page, $used_question_ids) {

        $groups = [];
        foreach($page['groups'] as $group) {
            $group_list = [];
            $list = $group['question_ids'];
            $list = array_diff($list, $used_question_ids);  //Remove any questions that have already been included on the quiz to avoid duplication.
            $list=array_values($list); //Reindex the array
            for($i=0; $i<$group['selection_number']; $i++) {

                $index = rand(0,count($list)-1);

                try {
                    array_push($group_list, $list[$index]);
                }
                catch(\Exception $e) {
                    throw new \Exception("Attempted to build a question group that has no unused questions available. Check your question selection criteria.");
                }

                unset($list[$index]);

                $list=array_values($list);

            }

            $used_question_ids = array_merge($used_question_ids,$group_list);
            $used_question_ids = array_values($used_question_ids);

            if (!isset($group['shuffle_within_group']) || !$group['shuffle_within_group'])
                $group_list = $this->quiz_order_questions($group_list, $group['question_ids']);
            array_push($groups,$group_list);
        }

        if (isset($page['shuffle_groups']) && $page['shuffle_groups'])
            shuffle($groups);

        //Consolidate all of the groups on the page into a single question list for the page
        $page_question_list = [];
        foreach($groups as $group)
            $page_question_list = array_merge($page_question_list, $group);

        return $page_question_list;
    }

    private function quiz_order_questions($new, $old) {
        $sorted = [];

        foreach($old as $o) {
            $search = array_search($o, $new);
            if($search !== false)
                array_push($sorted, $o);
        }

        return $sorted;
    }

    //Gets the list of questions currently available to the user on the quiz
    //Returns an array of the question ids.
    public function get_current_quiz_questions($assignment, $user, $instructorPreview = false) {
        $quiz = QuizJob::where('assignment_id',$assignment->id)->where('user_id',$user->id)->first();

        if($quiz->review_state == 1 || $instructorPreview) {  //Quiz review is allowed
            return $quiz->complete_question_list();
        }
        if($quiz->status == 0 || !$this->check_quiz_availability($quiz)['allow'])
            return [];

        foreach($quiz['question_list'] as $index => $page) {
            if($page['status'] < 2) //If the page is not yet complete, return the questions for it
                return $quiz->question_list[$index]['ids'];
        }
        return [];
    }

    public function quiz_page_progress($assignment, $user) {
        $quiz = QuizJob::where('assignment_id',$assignment->id)->where('user_id',$user->id)->first();
        if($quiz->status == 0 || !$this->check_quiz_availability($quiz)['allow'])
            return ['current' => 0, 'total' => count($quiz['question_list'])];
        foreach($quiz['question_list'] as $index => $page) {
            if($page['status'] < 2) //If the page is not yet complete, return the questions for it
                return ['current' => $index+1, 'total' => count($quiz['question_list'])];
        }
        return ['current' => 0, 'total' => count($quiz['question_list'])];
    }

    private function update_page_status($quiz) {
        $pages = $quiz->question_list;
        $dateTime = Carbon::now()->toDateTimeString();
        for($i=0; $i< count($quiz->question_list); $i++) {

            if($pages[$i]['status'] == 0) { //Starting
                $pages[$i]['status'] = 1;  //Change page to in progress
                $pages[$i]['start_time'] = $dateTime;
                $quiz->question_list = $pages;
                $quiz->status = 1;
                return $quiz;
            }
            if($pages[$i]['status'] == 1) {
                $pages[$i]['status'] = 2;  //Mark this page complete
                $pages[$i]['finish_time'] = $dateTime;
                if($i+1<count($quiz->question_list)) {//If there are more pages
                    $pages[$i + 1]['status'] = 1;  //Mark the next page started
                    $pages[$i + 1]['start_time'] = $dateTime;
                }
                else {
                    $quiz->status = 2;  //Mark the quiz as complete.
                    $quiz->elapsed_time = $quiz->actual_start->diffInSeconds(Carbon::now()) / 60;
                }
                $quiz->question_list = $pages;
                return $quiz;
            }
        }
        //You only end up here if the quiz got marked open, but no pages were open, then the user clicks next page.
        $quiz->status = 2;  //Mark the quiz as complete.
        return $quiz;
    }

    //Checks if user is allowed to access quiz questions based on start/completion and time constraints.
    private function check_quiz_availability($quiz) {
        if($quiz->status == 2) //User has completed
            return ['allow' => false, 'message' => 'You have completed this assessment.'];
        if($quiz->allowed_start->gt(Carbon::now()))
            return ['allow' => false, 'message' => 'The assessment is not open yet.'];
        if($quiz->allowed_end ->lt (Carbon::now()))
            return ['allow' => false, 'message' => 'The closing time for the assessment has passed.'];
        if($quiz->actual_start != null && $quiz->actual_start->diffInSeconds(Carbon::now()) > $quiz->allowed_minutes * 60)  //Allowed minutes exceeded
            return ['allow' => false, 'message' => 'Your allotted time to complete the assessment has expired.'];

        if($quiz->status == 0)
            return ['allow' => true, 'message' => 'You are eligible to begin the assessment.'];

        return ['allow' => true, 'message' => 'Your assessment is in progress.'];
    }

    public function check_quiz($user) {
        $quiz = QuizJob::where('assignment_id',$this->id)
            ->where('user_id',$user->id)
            ->select('status','allowed_start','actual_start','allowed_end','allowed_minutes')
            ->first();

        return $this->check_quiz_availability($quiz);
    }

    public function check_quiz_existence($user) {
        if(QuizJob::where('assignment_id',$this->id)->where('user_id',$user->id)->count() > 0)
            return true;
        return false;
    }

    public function quiz_controls($user) {
        $quiz = QuizJob::where('assignment_id',$this->id)
            ->where('user_id',$user->id)
            ->select('status','allowed_start','actual_start','allowed_end','allowed_minutes')
            ->first();

        if(!$quiz->actual_start == null)
            $quiz->elapsed_minutes = $quiz->actual_start->diffInSeconds(Carbon::now()) > $quiz->allowed_minutes * 60;
        else
            $quiz->elapsed_minutes = 0;

        $quiz->allow = $this->check_quiz_availability($quiz);

        $quiz->instructions = $this->options['quiz']['instructions'];

        return $quiz;
    }

    public function quiz_next_piece($uid) {

        $quiz = QuizJob::where('assignment_id',$this->id)
            ->where('user_id',$uid)
            ->first();
        $allowed = $this->check_quiz_availability($quiz);
        if(!$allowed['allow'])
            return back()->with('error', $allowed['message']);

        if($quiz->status == 0)
            $quiz->actual_start = Carbon::now();

        $quiz = $this->update_page_status($quiz);

        $quiz->save();

        return back();
    }


    // @override
    public function delete()
    {
        // TODO Delete assignment caches (use foreign keys)
        // TODO Move all the deletion of related models to foreign key constraint cascades

        // Delete all of the answers
        //This is VERY inffecicient, so just leaving the records in the database for now.
        /*
        foreach($this->answers as $answer)
            $answer->delete();
        */

        //Delete the assignment
        return parent::delete();
    }

    // Removes all the questions that are children of the assignment
    public function delete_questions()
    {
        // Delete all questions underneath
        //This is inefficient; just leave the records for now
        /*
        foreach($this->questions as $q)
            $q->delete();
        */
    }

    public function deactivate()
    {
        $this->active = false;
        $this->save();
    }

}
