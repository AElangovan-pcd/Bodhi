<?php

namespace App\Http\Controllers;

use App\ReviewAssignment;
use App\ReviewQuestion;
use App\ReviewSubmission;
use App\ReviewJob;
use App\ReviewAnswer;
use App\ReviewRevisionSubmission;
use App\ReviewSchedule;
use App\ReviewFeedback;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Course;
use View;
use Illuminate\Support\Facades\Input;
use Auth;
use Redirect;
use Storage;
use DB;
//use Mews\Purifier\Facades\Purifier;
use Stevebauman\Purify\Facades\Purify as Purifier;
use Log;
use Chumper\Zipper\Zipper;
use Response;
use File;

class ReviewController extends Controller
{
    const status = array(
        'Inactive',
        'Viewable',
        'Accepting Submissions',
        'Submissions Closed',
        'Calibrations Open',
        'Calibrations Closed',
        'Accepting Reviews',
        'Reviews Closed',
        'Reviews Available',
        'Accepting Revisions',
        'Revisions Closed',
        'Feedback Available'
    );

    public function test_scheduler() {
        Log::debug('test scheduler inside review controller');
    }

    public function check_schedules() {
        //TODO change to background job
        $tasks = ReviewSchedule::where('time', '<=', Carbon::now())->get();
        foreach($tasks as $task) {
            $assignment = ReviewAssignment::find($task->review_assignment_id);
            $assignment->state = $task->state;
            $assignment->save();
            if($task->state == 3) {
                $this->generate_review_matrix($assignment->id, $task->reviewNum);
                Log::debug('Processed review matrix from task for rid'.$task->review_assignment_id);
            }
            $task->delete();
            Log::debug('Processed review task to state '.$task->state.'for rid '.$task->review_assignment_id);
        }
    }

    public static function review_action($review) {
        $action = "";
        \Debugbar::info($review->state);
        if($review->state==2) {  //Accepting submissions
            $sub = ReviewSubmission::where('review_assignment_id',$review->id)->where('user_id',Auth::id())->first();
            if($sub == null)
                $action .= "* Awaiting Submission *";
        }
        if($review->state==6) {  //Accepting Reviews
            $jobs = ReviewJob::where('review_assignment_id',$review->id)->where('user_id',Auth::id())->where('complete',false)->get();
            if(count($jobs) > 0)
                $action .= "* Incomplete Reviewing Tasks *";
        }
        if($review->state>=8) {  //Reviews Available
            $sub = ReviewSubmission::where('review_assignment_id',$review->id)->where('user_id',Auth::id())
                ->with(['jobs'=> function($query) {
                    return $query->where('viewed',false);
                }
            ])->first();
            if($sub != null) {
                if (count($sub->jobs) > 0)
                    $action .= "* Unviewed Reviews Available *";
            }
        }
        if($review->state==9) {  //Accepting Revisions
            $rev = ReviewRevisionSubmission::where('review_assignment_id',$review->id)->where('user_id',Auth::id())->first();
            \Debugbar::info($rev);
            if($rev == null)
                $action .= "* Awaiting Revision Submission *";
        }
        if($review->state==11) {  //Feedback Available
            $fb = ReviewFeedback::where('review_assignment_id',$review->id)->where('user_id',Auth::id())->where('viewed',false)->first();
            if($fb != null)
                $action .= "* Unviewed Instructor Feedback Available *";
        }
        return $action;
    }

    public function landing($course_id) {
        $course = Course::find($course_id);
        $reviews = $course->reviews;
        $active = $course->reviews()->whereRaw('state > 0')
            ->with(['submissions','jobs','schedules'])
            ->get();
        foreach($active as $a) {
            $a->students = $course->users()
                ->with(
                    [
                        'review_submissions' => function($query) use ($a) {
                            $query->where('review_assignment_id',$a->id)->with(['jobs' => function($q2) {
                                $q2->with('user');
                            }]);
                        },
                        'review_jobs' => function($query) use ($a) {
                            $query->where('review_assignment_id',$a->id)->with(['submission' => function($q2) {
                                $q2->with('user');
                            }]);
                        },
                        'review_revision_submissions' => function($query) use ($a) {
                            $query->where('review_assignment_id', $a->id);
                        },
                    ])->get();
        }

        $inactive = $course->reviews()->where('state','0')->get();
        $view = View::make('instructor.review.reviewLanding');
        $view->course = $course;

        $data = array(
            "course"      => $course,
            'reviews'      => $reviews,
            'active'       => $active,
            'inactive'  => $inactive,
            'status'    => self::status,
        );

        $view->data = json_encode($data);
        return $view;
    }

    public function monitor_review($course_id, $rid) {
        $course = Course::find($course_id);
        $assignment = ReviewAssignment::where('id',$rid)->with(['submissions','jobs','schedules'])->first();
        $assignment->students = $course->users()
            ->with(
                [
                    'review_submissions' => function($query) use ($assignment) {
                        $query->where('review_assignment_id',$assignment->id)->with(['jobs' => function($q2) {
                            $q2->with('user');
                        }]);
                    },
                    'review_jobs' => function($query) use ($assignment) {
                        $query->where('review_assignment_id',$assignment->id)->with(['submission' => function($q2) {
                            $q2->with('user');
                        }]);
                    },
                    'review_revision_submissions' => function($query) use ($assignment) {
                        $query->where('review_assignment_id', $assignment->id);
                    },
                    'review_feedbacks' => function($query) use ($assignment) {
                        $query->where('review_assignment_id', $assignment->id);
                    },
                ])->get();

        $view = View::make('instructor.review.monitor');
        $view->course = $course;

        $data = array(
            "course"      => $course,
            'assignment'   => $assignment,
            'status'    => self::status,
        );

        $view->data = json_encode($data);
        return $view;
    }

    public function create_review($course_id)
    {
        $view = View::make('instructor.review.createReview');
        $view->course = Course::find($course_id);
        $questions = array();
        $data = array(
            'course'  => $view->course,
            'id' => -1,
            'questions' => $questions,
            'instructions' => array_fill(0,12,''),
            'info'  => "",
            'options' => ['response' => false, 'types' => false, 'typesList' => array(), 'typesReviewStyle' => 0],
            'status'    => self::status,
        );
        $view->data = json_encode($data);
        return $view;
    }

    public function edit_review($course_id, $rid) {
        $review = ReviewAssignment::find($rid);
        $view = View::make('instructor.review.createReview');
        $view->course = Course::find($course_id);
        foreach($review->questions as $q) {
            $q->sortedChoices = $q->choices;
        }
        $data = array(
            'course'  => $view->course,
            'id' => $review->id,
            'questions' => $review->questions,
            'instructions' => $review->instructions,
            'info'  => $review->info,
            'name'  => $review->name,
            'options' => $review->options,
            'status'    => self::status,
        );
        $view->data = json_encode($data);
        return $view;
    }

    public function save_review($course_id) {
        $data = json_decode(Input::get('data'));
        if($data->id==-1) {
            $assignment = new ReviewAssignment();
            $assignment->creator_id = Auth::id();
            $assignment->course_id = $course_id;
        }
        else
            $assignment = ReviewAssignment::find($data->id);

        $assignment->name = $data->name;
        $assignment->instructions = $data->instructions;
        $assignment->info = $data->info;
        //$assignment->response = $data->response;
        $assignment->options = $data->options;
        $assignment->save();

        $qids = [];
        $i=0;  //Counter for the order of the questions
        foreach($data->questions as $question) {
            array_push($qids,$this->save_question($question, $assignment->id, $i));
            $i++;
        }

        //Delete old questions that are no longer in the assignment
        $this->delete_questions($assignment, $qids);

        return json_encode(['status' => 'success', 'id' => $assignment->id, 'qids' => $qids]);
    }

    public function duplicate_review($cid, $rid) {
        $old = ReviewAssignment::find($rid);
        $new = new ReviewAssignment();
        $new->creator_id = $old->creator_id;
        $new->course_id = $cid;
        $new->name = $old->name.' (duplicate)';
        $new->instructions = $old->instructions;
        $new->info = $old->info;
        $new->options = $old->options;
        $new->type = $old->type;
        $new->save();

        $questions = $old->questions;
        foreach($questions as $question) {
            $question->id=-1;
            $question->sortedChoices = $question->choices;
            $this->save_question($question, $new->id, $question->order);
        }

        return Redirect::back()->with('status','Assignment Duplicated');
    }

    public function delete_review($cid,$rid) {
        $review = ReviewAssignment::find($rid);
        $review->delete();
        return Redirect::back()->with('status','Assignment Deleted');
    }

    private function save_question($question, $aid, $order) {
        if($question->id==-1) {
            $newQ = new ReviewQuestion();
            $newQ->review_assignment_id = $aid;
        }
        else
            $newQ = ReviewQuestion::find($question->id);

        $newQ->type = $question->type;
        $newQ->name = $question->name;
        $newQ->description = $question->description;  //TODO Add purifier
        $newQ->required = $question->required;
        $newQ->choices = $question->sortedChoices;
        $newQ->order = $order;
        $newQ->save();

        return $newQ->id;

    }

    public function delete_questions($assignment, $qids) {
        $questions = $assignment->questions;
        foreach($questions as $q) {
            if(!in_array($q->id,$qids))
                $q->delete();
        }
    }

    public function save_schedule($cid, $rid, Request $request) {
        $review = ReviewAssignment::find($rid);
        $schedules = $request->input('schedules');
        foreach($schedules as $sch) {
            if($sch['id'] > 0) {
                $task = ReviewSchedule::find($sch['id']);
                if(array_key_exists('deleted', $sch) && $task != null) {
                    $task->delete();
                    continue;
                }
            }
            else {
                if(array_key_exists('deleted', $sch))
                    continue;
                $task = new ReviewSchedule();
            }

            $task->review_assignment_id = $rid;
            $task->state = $sch['state'];
            $task->time = new Carbon($sch['time']);
            $task->reviewNum = $sch['reviewNum'];
            $task->save();

        }
        $scheds = ReviewSchedule::where('review_assignment_id', $rid)->get();
        return json_encode($scheds);
    }

    public function activate_review($cid, $rid) {
        $review = ReviewAssignment::find($rid);
        $review->state = 1;
        $review->save();
        return Redirect::back()->with('status','Assignment Activated');
    }

    public function change_state($cid, $rid, $state) {
        $review = ReviewAssignment::find($rid);
        $review->state = $state;
        $review->save();
        return Redirect::back()->with('status','Status changed for assignment '.$review->name);
    }

    public function generate_reviewers($cid, $rid, Request $request) {
        $num = $request->input('reviewNum');

        $this->generate_review_matrix($rid, $num);

        return Redirect::back()->with('status','Generated reviewer matrix.');
    }

    private function generate_review_matrix($rid, $num) {
        $review = ReviewAssignment::find($rid);

        if ($review->options['types'] == false || ($review->options['types'] == true && $review->options['typesReviewStyle'] == 0))
            $this->generate_reviewers_all($rid, $num);
        elseif($review->options['typesReviewStyle']==1)
            $this->generate_reviewers_within($rid, $num);
        elseif($review->options['typesReviewStyle']==2)
            $this->generate_reviewers_across($rid, $num);
    }

    //Any user who submitted may review any other submission regardless of type.  Default behavior if assignment sub-types is not selected.
    //To avoid multiple reviewers reviewing two submissions in the same order, the shift size starts at the number of reviewers, then decreases each time.
    //This requires more submissions than if the shift were a simple iteration.
    //10/24/19 Changed to use simple iteration
    private function generate_reviewers_all($rid, $num) {
        Log::debug('generating amongst all');
        $review = ReviewAssignment::find($rid);
        $review->reviewNum = $num;
        $review->save();

        //Delete all the old reviewing jobs
        ReviewJob::where('review_assignment_id',$rid)->delete();

        $submissions = ReviewSubmission::where('review_assignment_id',$rid)->select('user_id','id')->get()->toArray();

        shuffle($submissions);  //Randomize the array of submissions authors

        $jobList = [];
        $k=$num;
        $revs = $submissions;
        /*for($i=0; $i<$num; $i++) {  //Code for more complicated iteration
            for($j=0;$j<=$k;$j++) {
                array_push($revs, array_shift($revs));
                $k--;
            }
            array_push($jobList, $revs);
        }*/
        for($i=0; $i<$num; $i++) {  //Simple iteration
            array_push($revs, array_shift($revs));
            array_push($jobList, $revs);
        }

        foreach($jobList as $jobs) {
            $jnum = 0;
            foreach($jobs as $job) {
                $j = new ReviewJob();
                $j->review_assignment_id = $rid;
                $j->user_id = $job['user_id'];
                $j->review_submission_id = $submissions[$jnum]['id'];
                $j->save();
                $jnum++;
            }
        }
    }

    //When assignment sub-types option is selected, reviewers will only review others who submitted manuscripts of the same type.
    //To avoid circling back around to oneself with small numbers of submissions, this uses a simple iteration algorithm.  With 3+ reviewers, this means that multiple reviewers will review some of the same submissions in the same order.
    private function generate_reviewers_within($rid, $num) {
        Log::debug('generating within');
        $review = ReviewAssignment::find($rid);
        $review->reviewNum = $num;
        $review->save();

        //Delete all the old reviewing jobs
        ReviewJob::where('review_assignment_id',$rid)->delete();

        //Iterate through each of the assignment types and make the assignments, one type at a time.
        for($m=0; $m<count($review->options['typesList']); $m++) {
            $submissions = ReviewSubmission::where('review_assignment_id', $rid)->where('type', $m)->select('user_id', 'id')->get()->toArray();

            shuffle($submissions);  //Randomize the array of submissions authors

            $jobList = [];
            $k = $num;
            $revs = $submissions;
            /*for ($i = 0; $i < $num; $i++) {
                for ($j = 0; $j <= $k; $j++) {
                    array_push($revs, array_shift($revs));
                    $k--;
                }
                array_push($jobList, $revs);
            }*/

            for($i=0; $i<$num; $i++) {
                array_push($revs, array_shift($revs));
                array_push($jobList, $revs);
            }

            foreach ($jobList as $jobs) {
                $jnum = 0;
                foreach ($jobs as $job) {
                    $j = new ReviewJob();
                    $j->review_assignment_id = $rid;
                    $j->user_id = $job['user_id'];
                    $j->review_submission_id = $submissions[$jnum]['id'];
                    $j->save();
                    $jnum++;
                }
            }
        }
    }

    //When assignment sub-types option is selected, reviewers will only review others who submitted a different manuscript type than their own.
    //This will not work properly with 2 manuscript types.  There are many other scenarios in which it may not work properly as well.
    //To avoid circling back around to oneself with small numbers of submissions, this uses a simple iteration algorithm.  With 3+ reviewers, this means that multiple reviewers will review some of the same submissions in the same order.
    private function generate_reviewers_across($rid, $num) {
        Log::debug('generating across');
        $review = ReviewAssignment::find($rid);
        $review->reviewNum = $num;
        $review->save();

        //Delete all the old reviewing jobs
        ReviewJob::where('review_assignment_id',$rid)->delete();

        //Get all of the submissions by type
        for($i=0; $i<count($review->options['typesList']); $i++) {
            $submissions[$i] = ReviewSubmission::where('review_assignment_id',$rid)->where('type',$i)->select('user_id','id')->get()->toArray();
        }

        //Build the review list of all submitting participants, grouped by review type
        //Find out which type has the most submissions ($max) for the initial shift later
        $revs = $submissions[0];
        $max = count($submissions[0]);
        for($i=1; $i<count($submissions); $i++) {
            $revs = array_merge($revs, $submissions[$i]);
            if(count($submissions[$i]) > $max)
                $max = count($submissions[$i]);
        }

        $participants = array();
        //Shuffle within each of the submission types, then append them to an array for lining up reviewers.
        foreach($submissions as $subs) {
            shuffle($subs);
            $participants = array_merge($participants, $subs);
        }

        \Debugbar::info($participants);

        $jobList = [];

        //Do the initial shift to get the first reviewer.  The initial shift size is given by $max determined above.
        //The initial shift size should make sure the groups no longer line up with each other.
        for($i=0;$i<$max;$i++) {
            array_push($revs, array_shift($revs));
        }
        array_push($jobList, $revs);

        //Do any additional review assignments with subsequent iterative shifts of the reviewer list
        for($i=1; $i<$num; $i++) {
            array_push($revs, array_shift($revs));
            array_push($jobList, $revs);
        }

        //Put the jobs in the database
        foreach($jobList as $jobs) {
            $jnum = 0;
            foreach($jobs as $job) {
                $j = new ReviewJob();
                $j->review_assignment_id = $rid;
                $j->user_id = $job['user_id'];
                $j->review_submission_id = $participants[$jnum]['id'];
                $j->save();
                $jnum++;
            }
        }
    }

    public function seed_test_reviews($cid, $rid) {
        for($i=1; $i<12; $i++) {
            $submission = new ReviewSubmission();
            $submission->filename = 'MQ'.$i.'.pdf';
            $submission->extension = 'pdf';
            $submission->review_assignment_id = $rid;
            $submission->user_id = $i;
            $submission->type = $i % 3;
            $submission->save();
        }
    }

    public function view_review($cid, $rid) {
        $review = ReviewAssignment::where('id',$rid)->select(['id','name','instructions','state','options'])->first();
        if($review->state == 0)
            abort(403);
        $submission = ReviewSubmission::where('review_assignment_id',$rid)->where('user_id',Auth::id())->first();
        $revision = ReviewRevisionSubmission::where('review_assignment_id',$rid)->where('user_id',Auth::id())->first();
        if($review->state == 11)
            $feedback = ReviewFeedback::where('review_assignment_id',$rid)->where('user_id',Auth::id())->first();
        else
            $feedback = null;
        $jobs = ReviewJob::where('review_assignment_id',$rid)->where('user_id',Auth::id())->get();
        $view = View::make('student.review.viewReview');
        $view->course = Course::find($cid);
        $view->user = Auth::user();

        $data = array(
            'review' => $review,
            'status' => self::status,
            'submission'    => $submission,
            'jobs'  => $jobs,
            'revision' => $revision,
            'feedback' => $feedback,
        );

        $view->data = json_encode($data);
        return $view;
    }

    public function complete_review($cid, $rid, $jid) {
        $job = ReviewJob::find($jid);
        if($job->user_id != Auth::id() && Auth::user()->instructor != 1)
            abort(403,'You are not authorized to view this review job.');
        if($job->assignment->state != 6 && Auth::user()->instructor != 1)
            Redirect::back()->with('error','Reviews are not currently being accepted.');
        $view = View::make('student.review.completeReview');
        $view->course = Course::find($cid);
        $view->user = Auth::user();
        $review = ReviewAssignment::find($rid)->with(['questions'=> function($query) use ($jid){
            $query->with(['answers' => function($q2) use ($jid) {
                $q2->where('review_job_id',$jid)->latest();
            }]);
        }])->where('id',$rid)->first();
        $view->review = $review;

        $data = array(
            'review' => $review,
            'job_id' => $job->id,
        );

        $view->data = json_encode($data);
        return $view;
    }

    public function view_results($cid, $rid) {
        $submission = ReviewSubmission::where('review_assignment_id',$rid)->where('user_id',Auth::id())->first();
        if($submission->assignment->state < 8 && Auth::user()->instructor != 1)
            Redirect::back()->with('error','Reviews are not yet available.');
        $jobs = $submission->jobs()->select(['complete','id'])->with(['answers'])->get();
        foreach($jobs as $job) {
            $job->viewed = true;
            $job->save();
        }
        $questions = $submission->assignment->questions;

        $view = View::make('student.review.viewResults');
        $view->course = Course::find($cid);
        $view->user = Auth::user();
        $view->review = ReviewAssignment::find($rid);//$submission->assignment;

        $data = array(
            'review' => $view->review,
            'jobs'  => $jobs,
            'questions' => $questions
        );

        $view->data = json_encode($data);
        return $view;
    }

    public function view_results_stats($cid, $rid) {
        $assignment = ReviewAssignment::find($rid);
        $questions = $assignment->questions;

        foreach($questions as $key => $question) {
            for($i=0; $i<count($question->choices); $i++) {
                $choices = $question->choices;
                $choices[$i]["count"] = ReviewAnswer::where('review_question_id',$question->id)->where('selected',$i)->count();
                $question->choices = $choices;
            }
            if($question->type==2) {
                $wordCount = 0;
                $answers = ReviewAnswer::where('review_question_id',$question->id)->get();
                foreach($answers as $answer) {
                    $wordCount += str_word_count(strip_tags($answer->text));
                }
                $question->wordCount = $wordCount/count($answers);
            }
        }
        \Debugbar::info($questions);
        $data = array(
            'questions' => $questions,
        );

        $view = View::make('instructor.review.stats');
        $view->data = json_encode($data);
        $view->course = $assignment->course;
        $view->assignment = $assignment;
        return $view;
    }

    public function view_student_results($cid, $rid, $sid) {
        $submission = ReviewSubmission::where('review_assignment_id',$rid)->where('user_id',$sid)->first();
        $jobs = $submission->jobs()->select(['complete','id'])->with(['answers'])->get();
        $questions = $submission->assignment->questions;

        $view = View::make('student.review.viewResults');
        $view->course = Course::find($cid);
        $view->user = Auth::user();
        $view->review = ReviewAssignment::find($rid);//$submission->assignment;
        $view->student_name = $submission->user->firstname . " " . $submission->user->lastname;

        $data = array(
            'review' => $view->review,
            'jobs'  => $jobs,
            'submission' => $submission,
            'questions' => $questions
        );

        $view->data = json_encode($data);
        return $view;
    }

    public function save_complete($cid,$rid,$jid, Request $request) {
        $job = ReviewJob::find($jid);
        if($job->user_id != Auth::id() && Auth::user()->instructor != 1)
            abort(403,'You are not authorized to view this review job.');
        if($job->assignment->state != 6 && Auth::user()->instructor != 1)
            return "Closed";
        $questions = $request->input('questions');


        foreach($questions as $question) {
            $ans = ReviewAnswer::firstOrNew(['review_job_id' => $jid, 'review_question_id' => $question['id']]);
            $ans->review_job_id = $jid;
            if($question['type'] == 0 || $question['type'] == 1 ) {
                $ans->selected = $question['answers'][0]['selected'];
            }
            else
                $ans->text = Purifier::clean($question['answers'][0]['text']);
            $ans->save();
        }
        $job->complete = true;
        $job->save();
        return "Saved!";
    }

    public function submit_upload($cid, $rid, Request $request) {
        \Debugbar::info($request->get('type'));
        $review = ReviewAssignment::where('id',$rid)->select(['id','name','instructions','state'])->first();
        if($review->state != 2)
            return Redirect::back()->with('error', 'Submissions are not open for this assignment.');
        $file = $request->file('assignment_import');
        $ext = $file->getClientOriginalExtension();
        $path = $file->storeAs('review/'.$cid.'/'.$rid,$request->user()->id.'.'.$ext);
        $name = $file->getClientOriginalName();
        $submission = ReviewSubmission::firstOrNew(['review_assignment_id' => $rid, 'user_id' => $request->user()->id]);
        $submission->filename = $name;
        $submission->extension = $ext;
        $submission->review_assignment_id = $rid;
        $submission->user_id = Auth::id();
        $submission->type = $request->get('type');
        $submission->save();
        return Redirect::back()->with('status','File uploaded. Click the link in the box for your submission below to view the uploaded file.');
    }

    public function submit_upload_for_student($cid, $rid, Request $request) {
        if(Auth::user()->instructor != 1)
            return Redirect::back()->with('error', 'You do not have permission for this operation.');
        $review = ReviewAssignment::where('id',$rid)->select(['id','name','instructions','state'])->first();
        $file = $request->file('assignment_import');
        $ext = $file->getClientOriginalExtension();
        $path = $file->storeAs('review/'.$cid.'/'.$rid,$request->get('user_id').'.'.$ext);
        $name = $file->getClientOriginalName();
        $submission = ReviewSubmission::firstOrNew(['review_assignment_id' => $rid, 'user_id' => $request->get('user_id')]);
        $submission->filename = $name;
        $submission->extension = $ext;
        $submission->review_assignment_id = $rid;
        $submission->user_id = $request->get('user_id');
        $submission->type = $request->get('type');
        $submission->save();
        return Redirect::back()->with('status','File uploaded for student. You should view the submission to verify it uploaded correctly.');
    }

    public function delete_student_submission($cid, $rid, $sid) {
        if(Auth::user()->instructor != 1)
            return Redirect::back()->with('error', 'You do not have permission for this operation.');
        $submission = ReviewSubmission::find($sid);
        $user = $submission->user;
        Storage::delete('review/' . $cid . '/' . $rid . '/'. $submission->user_id.'.'.$submission->extension);
        $submission->delete();
        return Redirect::back()->with('status','Submission for '.$user->firstname.' '.$user->lastname.' has been deleted.');
    }

    public function submit_revision_upload($cid, $rid, Request $request) {
        $review = ReviewAssignment::where('id',$rid)->select(['id','name','instructions','state'])->first();
        if($review->state != 9)
            return Redirect::back()->with('error', 'Revision submissions are not open for this assignment.');
        $file = $request->file('revision_import');
        $ext = $file->getClientOriginalExtension();
        $path = $file->storeAs('review/'.$cid.'/'.$rid.'/revision/',$request->user()->id.'.'.$ext);
        $name = $file->getClientOriginalName();
        $submission = ReviewRevisionSubmission::firstOrNew(['review_assignment_id' => $rid, 'user_id' => $request->user()->id]);
        $submission->filename = $name;
        $submission->extension = $ext;
        $submission->review_assignment_id = $rid;
        $submission->user_id = Auth::id();
        $submission->save();
        return Redirect::back()->with('status','File uploaded. Click the link in the box for your submission below to view the uploaded file.');
    }

    public function submit_response_upload($cid, $rid, Request $request) {
            $review = ReviewAssignment::where('id', $rid)->select(['id', 'name', 'instructions', 'state'])->first();
            if ($review->state != 9)
                return Redirect::back()->with('error', 'Revision submissions are not open for this assignment.');
            $file = $request->file('response_import');
            $ext = $file->getClientOriginalExtension();
            $path = $file->storeAs('review/'.$cid.'/'.$rid.'/response/',$request->user()->id.'.'.$ext);
            $name = $file->getClientOriginalName();
            $submission = ReviewRevisionSubmission::firstOrNew(['review_assignment_id' => $rid, 'user_id' => $request->user()->id]);
            $submission->response_filename = $name;
            $submission->response_extension = $ext;
            $submission->review_assignment_id = $rid;
            $submission->user_id = Auth::id();
            $submission->save();
            return Redirect::back()->with('status', 'File uploaded. Click the link in the box for your submission below to view the uploaded file.');
    }

    public function upload_feedback($cid, $rid, Request $request) {
        //Store the zip file
        $file = $request->file('feedback_import');
        $ext = $file->getClientOriginalExtension();
        $name = $file->getClientOriginalName();
        $path = Storage::disk('local')->putFile('tmp/'.$cid.'-'.$rid.'-feedback',$file);

        //Unzip the zip file
        $zip = new Zipper;
        //dd(storage_path() .'/app/'. $path);
        $zip->make(storage_path() .'/app/'. $path);

        $list = $zip->listFiles();
        //dd($list);
        $zip->extractTo(storage_path() .'/app/tmp/'.$cid.'-'.$rid.'-feedback', array('._'), Zipper::BLACKLIST);
        try {
            //Create the ReviewFeedback models for each of the files
            foreach ($list as $fn) {
                $fb_file = Storage::disk('local')->get('tmp/'.$cid.'-'.$rid.'-feedback/'.$fn);
                Storage::put('review/'.$cid.'/'.$rid.'/feedback/'.$fn,$fb_file);
                $matches = [];
                $s = preg_match('/__(\d+)\.(...)/', $fn, $matches);
                $id = $matches[1];
                $fb = ReviewFeedback::where('review_assignment_id', $rid)->where('user_id', $id)->latest()->first();
                if ($fb == null)
                    $fb = new ReviewFeedback;
                $fb->review_assignment_id = $rid;
                $fb->user_id = $id;
                $fb->filename = $fn;
                $fb->extension = $matches[2];
                $fb->save();
            }

        }
        catch (\Throwable $e) {
            return Redirect::back()->with('error','Error with feedback file. '.$e->getMessage());
        }

        //Delete the zip file.
        $zip->close();
        Storage::disk('local')->deleteDirectory('tmp/'.$cid.'-'.$rid.'-feedback');

        return Redirect::back()->with('status','Feedback files uploaded.');
    }

    public function export_json($cid, $rid) {
        $review = ReviewAssignment::where('id',$rid)->with('questions')->first();
        $json = json_encode($review, JSON_PRETTY_PRINT);
        return Response::make($json, '200', array(
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="'.$review->name.'_peer_review.json"'
        ));
    }

    public function import_json($cid, Request $request) {
        $file = $request->file('assignment_import');

        $old = json_decode(file_get_contents($file->getRealPath()));

        $new = new ReviewAssignment();
        $new->creator_id = $old->creator_id;
        $new->course_id = $cid;
        $new->name = $old->name;
        $new->instructions = $old->instructions;
        $new->info = $old->info;
        $new->options = $old->options;
        $new->type = $old->type;
        $new->save();

        $questions = $old->questions;
        foreach($questions as $question) {
            $question->id=-1;
            $question->sortedChoices = $question->choices;
            $this->save_question($question, $new->id, $question->order);
        }

        //File::delete($file->getRealPath());

        return Redirect::back()->with('status','Assignment Uploaded');

    }

    public function download_submission($cid, $rid) {
        $submission = ReviewSubmission::where('review_assignment_id',$rid)->where('user_id',Auth::id())->first();
        return Storage::download('review/'.$cid.'/'.$rid.'/'.Auth::id().'.'.$submission->extension,$submission->filename);
    }

    public function download_revision($cid, $rid) {
        $submission = ReviewRevisionSubmission::where('review_assignment_id',$rid)->where('user_id',Auth::id())->first();
        return Storage::download('review/'.$cid.'/'.$rid.'/revision/'.Auth::id().'.'.$submission->extension,$submission->filename);
    }

    public function download_response($cid, $rid) {
        $submission = ReviewRevisionSubmission::where('review_assignment_id',$rid)->where('user_id',Auth::id())->first();
        return Storage::download('review/'.$cid.'/'.$rid.'/response/'.Auth::id().'.'.$submission->response_extension,$submission->response_filename);
    }

    public function download_feedback($cid, $rid) {
        $feedback = ReviewFeedback::where('review_assignment_id',$rid)->where('user_id',Auth::id())->first();
        if($feedback->assignment->state != 11 && Auth::user()->instructor != 1)
            return Redirect::back()->with('error','Feedback is not currently available.');
        $feedback->viewed = true;
        $feedback->save();
        return Storage::download('review/'.$cid.'/'.$rid.'/feedback/'.$feedback->filename,'Feedback'.'.'.$feedback->extension);
    }

    public function download_job_submission($cid, $rid, $jid) {
        $job = ReviewJob::find($jid);
        if($job->user_id != Auth::id() && Auth::user()->instructor != 1)
            abort(403,'You are not authorized to download this submission.');

        $submission = $job->submission;
        return Storage::download('review/'.$cid.'/'.$rid.'/'.$job->submission->user_id.'.'.$submission->extension,$submission->filename);
    }

    public function download_job_response($cid, $rid, $jid) {
        $job = ReviewJob::find($jid);
        if($job->user_id != Auth::id() && Auth::user()->instructor != 1)
            abort(403,'You are not authorized to download this submission.');
        $submission = ReviewRevisionSubmission::where('review_assignment_id',$job->review_assignment_id)->where('user_id',$job->submission->user_id)->first();
        if($submission == null)
            return Redirect::back()->with('error','No response uploaded for this review.');
        return Storage::download('review/'.$cid.'/'.$rid.'/response/'.$submission->user_id.'.'.$submission->response_extension,$submission->response_filename);
    }

    public function download_user_submission($cid, $rid, $sid) {
        $submission = ReviewSubmission::find($sid);
        return Storage::download('review/'.$cid.'/'.$rid.'/'.$submission->user_id.'.'.$submission->extension,$submission->filename);
    }

    public function download_all_submissions($cid, $rid) {
        $submissions = ReviewSubmission::where('review_assignment_id',$rid)->get();
        $zipper = new Zipper;
        $zip = $zipper->make($cid.'-'.$rid.'-submissions.zip');

        foreach($submissions as $submission) {
            $file=Storage::get('review/'.$cid.'/'.$rid.'/'.$submission->user_id.'.'.$submission->extension);
            $fileName = $submission->user->lastname.'_'.$submission->user->firstname.'__'.$submission->user->id.'.'.$submission->extension;
            $zip->addString($fileName,$file);
        }
        $zip->close();
        return response()->download($cid.'-'.$rid.'-submissions.zip','submissions.zip')->deleteFileAfterSend(true);
    }

    public function download_all_revisions($cid, $rid) {
        $submissions = ReviewRevisionSubmission::where('review_assignment_id',$rid)->get();
        $zipper = new Zipper;
        $zip = $zipper->make($cid.'-'.$rid.'-revisions.zip');
        foreach($submissions as $submission) {
            if($submission->filename != null) {
                $file = Storage::get('review/' . $cid . '/' . $rid . '/revision/' . $submission->user_id . '.' . $submission->extension);
                $fileName = $submission->user->lastname . '_' . $submission->user->firstname . '__' . $submission->user->id . '.' . $submission->extension;
                $zip->addString($fileName, $file);
            }
        }
        $zip->close();
        return response()->download($cid.'-'.$rid.'-revisions.zip','revisions.zip')->deleteFileAfterSend(true);
    }

    public function download_all_responses($cid, $rid) {
        $submissions = ReviewRevisionSubmission::where('review_assignment_id',$rid)->get();
        $zipper = new Zipper;
        $zip = $zipper->make($cid.'-'.$rid.'-responses.zip');
        foreach($submissions as $submission) {
            if($submission->response_filename != null) {
                $file=Storage::get('review/'.$cid.'/'.$rid.'/response/'.$submission->user_id.'.'.$submission->response_extension);
                $fileName = $submission->user->lastname . '_' . $submission->user->firstname . '__' . $submission->user->id . '.' . $submission->response_extension;
                $zip->addString($fileName,$file);
            }
        }
        $zip->close();
        return response()->download($cid.'-'.$rid.'-responses.zip','responses.zip')->deleteFileAfterSend(true);
    }

    public function download_user_revision($cid, $rid, $sid) {
        $submission = ReviewRevisionSubmission::find($sid);
        return Storage::download('review/'.$cid.'/'.$rid.'/revision/'.$submission->user_id.'.'.$submission->extension,$submission->filename);
    }

    public function download_user_response($cid, $rid, $sid) {
        $submission = ReviewRevisionSubmission::find($sid);
        return Storage::download('review/'.$cid.'/'.$rid.'/response/'.$submission->user_id.'.'.$submission->response_extension,$submission->response_filename);
    }

    public function download_user_feedback($cid, $rid, $fid) {
        try {
            $feedback = ReviewFeedback::find($fid);
            return Storage::download('review/' . $cid . '/' . $rid . '/feedback/' . $feedback->filename, $feedback->filename);
        }
        catch(\Throwable $e) {
            return Redirect::back()->with('error',$e->getMessage());
        }
    }
}
