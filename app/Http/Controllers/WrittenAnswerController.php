<?php

namespace App\Http\Controllers;

use App\Answer;
use App\Events\QuestionAnswered;
use Illuminate\Http\Request;
use App\Question;
use Illuminate\Support\Facades\Input;
use Auth;
use App\User;
use Exception;
use App\Assignment;
use View;
use App\ProfResponse;
use App\Score;
use App\Events\WrittenAnswerSubmitted;
use App\Events\WrittenAnswerGraded;
use Stevebauman\Purify\Facades\Purify as Purifier;
use App\Result;
use Response;

class WrittenAnswerController extends Controller
{


    public function save_responses() {
        $q = json_decode(Input::get('question'));

        foreach($q->responses as $r) {
            if($r->id == -1 && $r->response != "")
                ProfResponse::create(['question_id' => $q->id, 'response' => $r->response, 'order' => $r->order]);
            elseif(isset($r->to_delete) && $r->to_delete === true)
                ProfResponse::destroy($r->id);
            elseif($r->response != "")
                ProfResponse::where('id',$r->id)->update(['response' => $r->response, 'order' => $r->order]);
        }
        $responses = ProfResponse::where('question_id',$q->id)->orderBy('order')->get();
        return Response::json([
            'status' => 'success',
            'responses' => $responses,
        ],200);
    }

    public function save_options() {
        $q = json_decode(Input::get('question'));
        \Debugbar::info($q->options);

        Question::where('id',$q->id)->update(['options'=>json_encode($q->options)]);

        return Response::json([
            'status' => 'success',
            'question' => $q,
        ],200);
    }

    public function submit_written_for_user($cid, $aid, User $user) {
        $this->submit_written_answer($cid, $aid, $user, true);
    }

    public function submit_written_answer($cid, $aid, $user = null, $preview = false) {
        if($user == null)
            $user = Auth::user();

        $question = Question::with('assignment')->findorfail(Input::get('question_id'));
        $submission = Purifier::clean(Input::get('submission'));

        if(!isset($question->options['resubmission'])) {
            $options = $question->options;
            $options['resubmission'] = false;
            $question->options = $options;
        }

        if($question->assignment->type == 2 && !$preview) {
            $allow = $question->assignment->check_quiz($user);
            if(!$allow['allow'])
                return array("feedback" => $allow['message'], "valid" => false, "question" => $question->id);
        }

        $eval_allowed = $question->assignment->eval_allowed();
        if(!$eval_allowed['allow'] === true)
            return ["valid" => false, "feedback" => "Submissions for this assignment have been disabled. This submission was not stored.". $eval_allowed['msg']];

        $result = Result::where('user_id',$user->id)->where('question_id', $question->id)->latest()->select(['id','status'])->first();

        if($result != null && $result->status != 2 && $question->options['resubmission'] != true) {
            return ["valid" => false, "feedback" => "You have already submitted for this question. This submission was not stored. Reload the page to see saved submission."];
        }

        if($result != null && $question->options['resubmission'] == true) {
            if($result->status == 1)
                return ["valid" => false, "feedback" => "Resubmission not allowed after question has been graded. Reload the page to see feedback."];
            if($result->status == 3)
                return ["valid" => false, "feedback" => "Resubmission not allowed after question has been graded. Graded feedback has not yet been made available."];
        }

        $answer = Answer::updateOrCreate(['user_id'=>$user->id,'question_id'=>$question->id],
            ['submission' => $submission]
        );

        $result = Result::updateOrcreate(
            ['question_id' => $question->id,
                'user_id' => $user->id
            ],
            [
                'attempts' => \DB::raw('attempts + 1'),
                'status' => 0,
            ]
        );

        broadcast(new QuestionAnswered(
            $question->assignment->course_id,
            $user,
            $question->assignment->id,
            $question->id,
            $result,
            [$answer]));

        $feedback = "Submitted.  Waiting on graded response.";

        $feedback = $this->check_saved($feedback, $answer, $result);

        return ["feedback" => $feedback, "valid" => true];
    }


    private function check_saved($feedback, $answer, $result) {
        if($answer == null || $result == null) {
            $feedback = "Error saving. Submission not recorded.";
        }
        return $feedback;
    }

    public function preview_written_answer() {
        $submission = Purifier::clean(Input::get('submission'));
        return $submission;
    }

    public function submit_feedback($cid, $aid) {
        $results = json_decode(Input::get('results'));
        \Debugbar::info($results);
        $return = [];
        foreach($results as $result) {
            $oldResult = Result::find($result->id);
            $oldResult->update([
                'earned' => $result->earned,
                'feedback' => $result->feedback,
                'status' => $result->set_status,
            ]);
            $return[] = $oldResult;

            if($oldResult->status != 3) {  //Don't broadcast deferred feedback
                broadcast(
                    new WrittenAnswerGraded(
                        $cid,
                        $oldResult->user_id,
                        $oldResult->question_id,
                        $oldResult
                    )
                );
            }

            if (isset($result->save_response) && $result->save_response && $result->feedback !== "") {
                $new_resp = New ProfResponse();
                $new_resp->response = $result->feedback;
                $new_resp->question_id = $result->question_id;
                $new_resp->save();
            }
        }
        //TODO Broadcast
/*
        $response_data = array(
            "response" => $submission->response,
            "score" => $score_tosave->earned,
        );

        broadcast(new WrittenAnswerGraded($cid,$submission->user_id,$submission->question_id,"grades",$response_data));
*/
        $responses = ProfResponse::where('question_id',$results[0]->question_id)->orderBy('order')->get();

        return Response::json([
            'status' => 'success',
            'results' => $return,
            'responses' => $responses
        ],200);
    }
}
