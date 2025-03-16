<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use View;
use Auth;
use App\Forum;
use App\ForumSubscription;
use App\Course;
use DB;
use App\ForumResponseVote;
use Redirect;
use App\ForumAnswer;
use App\ForumView;
use App\ForumTopicSubscription;
//use Mews\Purifier\Facades\Purifier;
use Stevebauman\Purify\Facades\Purify as Purifier;
use App\User;
use Illuminate\Support\Facades\Input;
use App\Events\NewForumAnswer;
use App\Events\UpdatedForumAnswer;
use App\Events\NewForumTopic;
use Throwable;

class ForumController extends Controller
{
    public function __construct()
    {
        $this->middleware('forum', ['only' => ['edit_forum','view_forum','delete_forum']]);
    }

    public function forum_landing($course_id) {
        $course = Course::find($course_id);
        $instructor = Auth::user()->instructor;

        $forums = $course->forums()
            ->select(['id', 'course_id', 'type', 'title', 'preview', 'tags', 'created_at', 'updated_at']);

        if($instructor) {
            $instuctor_list = $course->instructors();

            $forums->withCount(['forum_answers', 'viewers',
                'viewers as viewed' => function ($query) use ($instuctor_list) {
                    $query->whereIn('user_id', $instuctor_list);
                }
            ]);

            $forums->with(['latest_view' => function($q) use ($instuctor_list) {
                $q->select(['forum_id','updated_at'])->whereIn('user_id', $instuctor_list);
            }]);
        }
        else {
            $forums->withCount(['forum_answers', 'viewers',
                'viewers as viewed' => function ($query) {
                    $query->where('user_id', Auth::id());
                }
            ]);

            $forums->with(['latest_view' => function($q) {
                $q->select(['forum_id','updated_at'])->where('user_id',Auth::id());
            }]);
        }

        $forums->with(['forum_answers' => function ($q) {
            return $q->select(['forum_id','created_at']);
        }]);

        $forums = $forums->latest()->get();
        $view = View::make('forum.ForumLanding');
        $view->user = Auth::user();


        $view->instructor = $instructor;

        if($forums != null) {
            foreach ($forums as $forum) {
                $forum->newResponses = 0;
                $forum->test = 0;
                if($forum->viewed)
                    $forum->new_activity = $forum->updated_at > $forum->latest_view->updated_at;
                else
                    $forum->new_activity = true;
                //Get responses since the last view.
                if($forum->viewed) {
                    if($forum->forum_answers != null) {
                        $forum->test = $forum->forum_answers->where('created_at', '>=', $forum->latest_view->updated_at)->count();
                    }
                    /*if($instructor)
                        $myView = ForumView::where('forum_id', $forum->id)->whereIn('user_id', $instuctor_list)->orderBy('updated_at','desc')->first();
                    else
                        $myView = ForumView::where('forum_id', $forum->id)->where('user_id', Auth::id())->first();
                    $forum->newResponses = $forum->forum_answers()->where('created_at', '>=', $myView->updated_at)->select('created_at')->count();
                    */
                }
                else {
                    //$forum->newResponses = $forum->forum_answers_count;
                    $forum->newResponses = $forum->forum_answers->count();
                }
            }
        }

        $stats = $this->forum_user_stats($course_id, Auth::id());

        //Determine whether the user is subscribed to the forums
        $sub=ForumSubscription::where('course_id', '=', $course->id)
            ->where('user_id', '=', Auth::id())
            ->first();

        if($sub == null) {
            $subscribed = false;
            $autosubscribed = false;
        }
        else {
            $subscribed = true;
            $autosubscribed = boolval($sub->auto_subscribe);
        }

        $data = array(
            "forums" => $forums,
            "course" => $course,
            "subscribed" => $subscribed,
            "autosubscribed" => $autosubscribed,
            "stats" => $stats,
            "sortedBy" => "created_at",
        );
        $view->instructor = Auth::user()->instructor;
        $view->course = $course;
        $view->data = json_encode($data);
        return $view;
    }

    public static function forum_details($course_id) {
        $course = Course::find($course_id);
        $details = new \stdClass();
        $details->count = $course->forums()->count();
        $details->unread = $details->count - $course->forum_views()->where('user_id', Auth::id())->count();
        $details->last = $course->forums()
            ->select(['id', 'title','created_at'])
            ->orderBy('created_at','desc')
            ->first();
        try{
            $details->lastResponse = ForumAnswer::where('course_id',$course_id)->latest()->first()->forum()->select(['id','title'])->first();
            $lastResponseTime = ForumAnswer::where('course_id',$course_id)->latest()->select(['created_at'])->first()->created_at;
            $details->lastResponse->created_at = $lastResponseTime->toDateTimeString();
        }
        catch(Throwable $e) {
            $details->lastResponse = null;
        }

        $forums = $course->forums()
            ->select(['id'])
            ->withCount([
                'viewers as viewed' => function($query) {
                    $query->where('user_id',Auth::id());
                }
            ])
            ->get();

        $details->newResponses = 0;

        //TODO Eager load forum views.
        if($forums != null) {
            foreach ($forums as $f) {
                //Get responses since the last view.
                if($f->viewed) {
                    $myView = ForumView::where('forum_id', $f->id)->where('user_id', Auth::id())->first();
                    $details->newResponses += $f->forum_answers()->where('created_at', '>=', $myView->updated_at)->select('created_at')->count();
                }
                else {
                    $details->newResponses += $f->forum_answers()->count();
                }
            }
        }

        return $details;
    }

    public function new_stats($course_id) {
        $view = View::make('forum.new_stats');

        $course=Course::find($course_id);

        $users = $course->users()
            ->withCount(['forums' => function($q) use($course_id) { $q->where('course_id',$course_id);}])
            ->withCount(['forum_views' => function($q) use($course_id) { $q->where('course_id',$course_id);}])
            ->withCount(['forum_answers' => function($q) use($course_id) { $q->where('course_id',$course_id);}])
            //->withCount(['forum_votes' => function($q) use($course_id) { $q->where('course_id',$course_id);}])
            ->withCount(['forum_votes' => function($q) use($course_id) {
                $q->whereHas('answer', function ($q2) use($course_id) {
                    $q2->where('course_id', $course_id);
                });
            }])
            ->withCount(['forum_endorsed_answers' => function($q) use($course_id) { $q->where('course_id',$course_id);}])
            ->withCount(['forum_helpful_answers' => function($q) use($course_id) { $q->where('course_id',$course_id);}])
            ->with(['forum_helpful_votes' => function($q) use($course_id) { $q->where('course_id',$course_id);}])
            ->get();

        foreach($users as $user) {

            $user->forum_helpful_votes_count = $user->forum_helpful_votes->sum('forum_votes_count');
            //dd($user->forum_helpful_votes);
        }

        $data=[
            'course' => $course,
            'users' => $users,
        ];

        $view->user = Auth::user();
        $view->course = $course;
        $view->data = json_encode($data);
        return $view;
        //TODO fix table sorting in view.
    }

    public function stats($course_id) {
        $view = View::make('forum.Stats');

        $course = Course::find($course_id);
        $users = $course->users;
        foreach($users as $user) {
            $stats = $this->forum_user_stats($course_id, $user->id);
            foreach($stats as $stat => $val)
                $user->$stat = $val;
            $user->seat = $user->pivot->seat;
        }

        $data = array(
            "course" => $course,
            "users" => $users,
            "sorts" => ["firstname" => true, "lastname" => true, "seat" => true, "posts" =>true, "responses" =>true, "yourVotes" =>true, "helpfulAnswers" =>true, "helpfulVotes" =>true, "endorsed" =>true, "views" =>true],
        );
        $view->data = json_encode($data);

        $view->user = Auth::user();
        $view->course = $course;
        return $view;
    }

    private function forum_user_stats($course_id, $user_id) {

        $user = User::withCount(['forums' => function($q) use($course_id) { $q->where('course_id',$course_id);}])
            ->withCount(['forum_views' => function($q) use($course_id) { $q->where('course_id',$course_id);}])
            ->withCount(['forum_answers' => function($q) use($course_id) { $q->where('course_id',$course_id);}])
            ->withCount(['forum_votes' => function($q) use($course_id) {
                $q->whereHas('answer', function ($q2) use($course_id) {
                    $q2->where('course_id', $course_id);
                });
                }])
            ->withCount(['forum_endorsed_answers' => function($q) use($course_id) { $q->where('course_id',$course_id);}])
            ->withCount(['forum_helpful_answers' => function($q) use($course_id) { $q->where('course_id',$course_id);}])
            //->with(['forum_helpful_answers' => function($q) use($course_id) { $q->where('course_id',$course_id);}])
            ->with(['forum_helpful_votes' => function($q) use($course_id) { $q->where('course_id',$course_id);}])
            ->find($user_id);

        $stats = array(
            "posts" => $user->forums_count,
            "responses" => $user->forum_answers_count,
            "helpfulAnswers" => $user->forum_helpful_answers_count,
            "helpfulVotes" => $user->forum_helpful_votes->sum('forum_votes_count'),
            "yourVotes" => $user->forum_votes_count,
            "endorsed" => $user->forum_endorsed_answers_count,
            "views" => $user->forum_views_count,
        );
        return $stats;
    }

    private function forum_user_stats_old($course_id, $user_id) {
        //TODO user query builder to clean up
        //TODO user eager loading!

        //$test = User::with('forums.forum_answers')//withCount(['forums.forum_answers' => function($q) use($course_id) { $q->where('course_id',$course_id);}])
          //  ->find($user_id);
        $test = null;
        $course = Course::find($course_id);
        $forums = $course->forums;
        $posts=0;
        $responses=0;
        $helpfulAnswers=0;
        $helpfulVotes=0;
        $yourVotes=0;
        $endorsed=0;
        $views = count(DB::table('forum_views')->where('course_id',$course_id)->where('user_id',$user_id)->get());
        if($forums != null) {
            foreach ($forums as $forum) {
                $forum_answers = DB::table('forum_answers')->where('forum_id', $forum->id)->get();
                if ($forum->user_id == $user_id)
                    $posts++;
                $viewers = $forum->viewers;
                foreach ($forum_answers as $answer) {
                    $votes = ForumResponseVote::where('response_id', $answer->id)->get();

                    if ($answer->user_id == $user_id) {
                        $responses++;
                        if ($answer->endorsed)
                            $endorsed++;
                        $votes = count($votes);
                        if ($votes > 0) {
                            $helpfulAnswers++;
                            $helpfulVotes = $helpfulVotes + $votes;
                        }
                    } else
                        foreach ($votes as $vote)
                            if ($vote->user_id == $user_id)
                                $yourVotes++;
                }
            }
        }

        $stats = array(
            "posts" => $posts,
            "responses" => $responses,
            "helpfulAnswers" => $helpfulAnswers,
            "helpfulVotes" => $helpfulVotes,
            "yourVotes" => $yourVotes,
            "endorsed" => $endorsed,
            "views" => $views,
            "test" => $test,
        );
        return $stats;
    }

    public function create_forum($class_id) {
        $view = View::make('forum.CreateForum');
        $view->course = Course::find($class_id);
        $view->user = Auth::user();
        $anon = (Auth::user()->instructor ? false : true);
        $data = array(
            'forum_id' => -1,
            'forum_type' => 1,
            'course'  => $view->course,
            'anonymous' => $anon,
            'student_answers' => null,
            'instructor_answer' => 0
        );

        $view->data = json_encode($data);
        $view->instructor = Auth::user()->instructor;
        return $view;
    }

    public function edit_forum($cid, $forum_id) {
        $view = View::make('forum.CreateForum');
        $forum = Forum::find($forum_id);

        $user_id = Auth::id();

        if ($user_id != $forum->user_id && Auth::user()->instructor != 1)
            return "You are not authorized to modify this forum.";

        $view->course = Course::find($forum->course_id);
        $data = array(
            'forum_id' => $forum->id,
            'forum_type' => $forum->type,
            'course'  => $view->course,
            'anonymous' => $forum->anonymous,
            'forum_question' => $forum->question,
            'forum_title' => $forum->title,
            'tags' => $forum->tags
        );

        $view->data = json_encode($data);
        $view->instructor = Auth::user()->instructor;
        $view->user = Auth::user();
        return $view;
    }

    public function delete_forum($cid, $forum_id) {
        $forum = Forum::find($forum_id);

        $user_id = Auth::id();

        if ($user_id != $forum->user_id && Auth::user()->instructor != 1)
            return "You are not authorized to modify this forum.";

        $fans = $forum->forum_answers()->get();
        foreach ($fans as $fan) {
            $fan->delete();
        }
        $forum->delete();

        if(Auth::user()->instructor)
            return Redirect::to('instructor/course/'.$cid.'/forum/landing')->with('status','Topic deleted.');
        else
            return Redirect::to('course/'.$cid.'/forum/landing')->with('status','Topic deleted.');
    }

    public function delete_forum_response($cid, $rid) {
        $response = ForumAnswer::find($rid);

        $user_id = Auth::id();

        if ($user_id != $response->user_id && Auth::user()->instructor != 1)
            return "You are not authorized to modify this forum.";

        $forum_id = $response->forum_id;
        $response->delete();

        $socketData = array(
            "data_type" => "forum",
            "action"  => "delete_response",
            "forum_id" => $response->forum_id,
            "forum_answer_id" => $response->id,
        );

        //TODO add sockets

        return Redirect::back()->with('status','Response deleted.');
    }

    public function view_forum($cid, $fid) {
        //TODO clean up queries
        if (!Forum::find($fid))
            return "Topic not found";
        $view = View::make('forum.ViewForum');
        $view->user = Auth::user();

        $forum = Forum::find($fid);
        $course = Course::find($forum->course_id);

        //Determine whether the current user has viewed the topic.  If not, add to the viewed_by list.
        $forum_view = ForumView::where('forum_id',$forum->id)->where('user_id',Auth::id())->first();
        if($forum_view == null) {
            $forum_view = new ForumView();
            $forum_view->user_id = Auth::id();
            $forum_view->forum_id = $forum->id;
            $forum_view->course_id = $course->id;
            $forum_view->save();
        }
        else
            $forum_view->touch();  //Update the updated timestamp on the view

        //$viewers = $forum->forum_views();

        //Determine whether the author's name should be displayed.
        $user = User::find($forum->user_id);
        if ($forum->anonymous)
            $author = "Anonymous";
        else
            $author = $user->firstname . " " . $user->lastname;

        //Determine whether the current user authored the post.
        if($user->id == Auth::id())
            $owner = 1;
        else
            $owner = 0;

        //Determine whether the current user is subscribed to the topic.
        $subscribers = ForumTopicSubscription::where('forum_id', $forum->id)->get();
        $subscribed = false;
        foreach($subscribers as $subscriber)
            if($subscriber->user_id == Auth::id())
                $subscribed = true;

        $instructor = Auth::user()->instructor;
        $view->instructor = $instructor;

        $data = array();
        $data["forum"] = $forum;
        $data["course"] = $course;
        $data["author"] = $author;
        $data["owner"] = $owner;
        $data["responses"] = DB::table('forum_answers')->where('forum_id', $fid)->get();
        $data["anonymous"] = ($instructor ? false : true);
        $data["instructor"] = $instructor;
        $data["subscribed"] = $subscribed;

        if($instructor)
            $data["forum_identity"] = $user->firstname . " " . $user->lastname . " (" . $user->email . ").";

        foreach($data["responses"] as $key => $response) {
            $data["responses"][$key] = $this->prepare_response($response);
        }

        $view->class = $course;
        $view->course = $course;

        $view->data = json_encode($data);

        return $view;
    }

    public function save_forum() {
        $data = Input::get();

        if($data['forum_id'] == -1) {
            $forum = new Forum();
            $forum->user_id = Auth::id();
            $new = true;
        }
        else {
            $forum = Forum::find($data['forum_id']);
            $new = false;
            if (Auth::id() != $forum->user_id && Auth::user()->instructor != 1)  //Make sure it's the original author or an instructor modifying
                return "You are not authorized to modify this forum.";
        }

        $forum->course_id = $data['course']['id'];

        $forum->type = $data['forum_type'];
        $forum->title = strip_tags(Purifier::clean($data['title']));  //Sanitize with HTMLPurifier
        $forum->question = Purifier::clean($data['question']);

        //Save a brief preview of the question for efficiency when loading forum landing
        $prevLength = 200;
        $preview = strip_tags($forum->question); //Remove formatting for preview.
        if (strlen($preview) > $prevLength)
            $preview = substr($preview, 0, $prevLength) . "...";
        $forum->preview = $preview;

        $forum->tags = strip_tags(Purifier::clean($data['tags']));
        $forum->anonymous = $data['anonymous'];

        $forum->save();


        if($new) {  //The author should be listed as having viewed the forum.
            $forum_view = new ForumView();
            $forum_view->user_id = Auth::id();
            $forum_view->forum_id = $forum->id;
            $forum_view->course_id = $forum->course_id;
            $forum_view->save();
        }

        //TODO reintroduce mailing to subscribers
        $this->add_auto_subscribers($forum->course_id, $forum->id);

        broadcast(new NewForumTopic($forum->course_id,$forum->id,$forum->title, $forum->created_at));

        return "Saved!";
    }

    public function save_forum_answer() {
        $data = Input::get();

        if($data['forum_answer_id'] == -1) {
            $response = new ForumAnswer();
            $response->user_id = Auth::id();
        }
        else {
            $response = ForumAnswer::find($data['forum_answer_id']);
            if (Auth::id() != $response->user_id && Auth::user()->instructor != 1)  //Make sure it's the original author or an instructor modifying
                return "You are not authorized to modify this response.";
        }


        $response->forum_id = $data['forum_id'];
        $response->course_id = Forum::find($response->forum_id)->course->id;
        $response->answer = Purifier::clean($data['answer']); //Sanitize inputs with HTMLPurifier
        $response->anonymous = $data['anonymous'];

        $response->save();

        //TODO add email
        //Send email to subscribers if this is a new response.

        if($data['update'])
            broadcast(new UpdatedForumAnswer($response->forum->course_id,$response->forum_id,$response->id));
        else
            broadcast(new NewForumAnswer($response->forum->course_id,$response->forum_id,$response->id, $response->forum->title, $data['postKey'], $response->created_at));

        return "Saved!";
    }

    //TODO middleware for all forum actions
    public function endorse() {
        $data = Input::get();

        $response = ForumAnswer::find($data['forum_answer_id']);

        $response->endorsed = !$response->endorsed;

        $response->save();

        \Debugbar::info($response);

        broadcast(new UpdatedForumAnswer($response->forum->course_id,$response->forum_id,$response->id,$this->prepare_response($response)));

        return "Toggling endorsement";
    }


    public function forum_subscription() {
        $data = Input::get();
        $user_id = Auth::id();

        $sub=ForumSubscription::where('course_id', '=', $data['course_id'])
            ->where('user_id', '=', $user_id)
            ->first();

        $subscribe = $data['subscribe'] === 'true' ? true : false;
        $autosubscribe = $data['autosubscribe'] === 'true' ? true : false;

        if($subscribe === false) {
            if($sub!==null) {
                $sub->delete();
                return "Dropped subscription";
            }
            return "Cannot drop; already dropped";
        }
        else {
            if($sub===null) {
                $sub= new ForumSubscription();
                $sub->user_id = $user_id;
                $sub->course_id = $data['course_id'];
            }
            $sub->auto_subscribe = $autosubscribe;
            $sub->save();
            return "Subscription added";
        }

    }

    private function add_auto_subscribers($course_id, $forum_id) {
        $subs = ForumSubscription::where('course_id', $course_id)->get();
        foreach($subs as $sub) {
            if($sub->auto_subscribe==1) {
                $this->add_topic_subscription($forum_id, $sub->user_id);
            }
        }
    }

    private function add_topic_subscription($forum_id, $user_id) {
        $sub = new ForumTopicSubscription();
        $sub->user_id = $user_id;
        $sub->forum_id = $forum_id;
        $sub->save();
        return "adding subscription";
    }

    public function topic_subscription() {
        $data = Input::get();
        $user_id = Auth::id();
        $forum_id = $data['forum_id'];

        $subscribers = ForumTopicSubscription::where('forum_id', $forum_id)->get();

        //Check all subscribers for current user; unsubscribe them if they're in the list.  If they're not in the list, subscribe them.
        foreach($subscribers as $subscriber) {
            if($subscriber->user_id == $user_id) {
                $subscriber->delete();
                return "subscription removed";
            }
        }

        $sub = new ForumTopicSubscription();
        $sub->user_id = $user_id;
        $sub->forum_id = $forum_id;
        $sub->save();
        return "adding subscription";

    }

    public function response_vote() {
        $data = Input::get();

        $response = ForumAnswer::find($data['forum_answer_id']);

        $user_id = Auth::id();

        $votes = ForumResponseVote::where('response_id',$data['forum_answer_id'])->get();

        $voted = 1;
        foreach($votes as $vote) {
            if($vote->user_id == $user_id) {
                $vote->delete();
                $msg = "removed vote";
                $voted = 0;
            }
        }
        if($voted == 1) {
            $vote = new ForumResponseVote();
            $vote->response_id = $data['forum_answer_id'];
            $vote->user_id = $user_id;
            $vote->save();
            $msg = "added vote";
        }

        $response->save();

        broadcast(new UpdatedForumAnswer($response->forum->course_id,$response->forum_id,$response->id,$this->prepare_response($response)));

        return $msg;

    }

    //Return only the relevant information from the response to the user.
    private function prepare_response($response) {

        $prepared = new \stdClass();
        if(is_string($response->created_at))
            $prepared->created_at = $response->created_at;
        else
            $prepared->created_at = $response->created_at->toDateTimeString();
        $prepared->anonymous = $response->anonymous;
        $prepared->answer = $response->answer;
        $prepared->id = $response->id;
        $prepared->endorsed = $response->endorsed;

        $prepared->forum_answer_id = $response->id;

        //Determine number of votes and whether the current user has already voted.
        $prepared->votes = ForumResponseVote::where('response_id',$response->id)->count();
        $prepared->voted = ForumResponseVote::where('response_id',$response->id)->where('user_id',Auth::id())->exists();

        if($response->user_id == Auth::id())
            $owner = 1;
        else
            $owner = 0;

        $prepared->owner = $owner;

        //Determine whether name of respondent should be passed for each answer.
        if ($response->anonymous) {
            $prepared->respondent = "Anonymous";
            if($owner)
                $prepared->respondent = "You (anonymous to classmates).";
            if(Auth::user()->instructor) {
                $user = User::find($response->user_id);
                $prepared->identity = $user->firstname . " " . $user->lastname . " (" . $user->email . ").";
            }
        }
        else {
            $user = User::find($response->user_id);
            $prepared->respondent = $user->firstname . " " . $user->lastname;
            if($owner)
                $prepared->respondent = "You";
        }

        return $prepared;
    }

    public function response_details() {
        $data = Input::get();

        $response = ForumAnswer::find($data['forum_answer_id']);
        ForumView::where('forum_id',$response->forum_id)->where('user_id',Auth::id())->first()->touch();  //Update that user has viewed this forum to know that the response has been seen.

        return json_encode($this->prepare_response($response));
    }
}

/* from save_forum_answer
if($data['forum_answer_id'] == -1) {
    $forum = Forum::find($response->forum_id);
    $subscribers = ForumTopicSubscription::where('forum_id',$response->forum_id)->get();

    $recipients = array();
    foreach($subscribers as $subscriber) {
        if(!empty($subscriber))
            if($subscriber->user_id != Auth::id())//Don't email yourself when you post.
                array_push($recipients, User::find($subscriber->user_id)->email);
    }

    if(count($recipients)>0) { //Only email if there are actually subscribers.

        $vars = array(
            'title' => $forum->title,
            'course' => Course::find($forum->course_id)->name,
            'forum_id' => $forum->id,
            'respondent' => $response->respondent,
            'answer' => $response->answer,
        );
        $run = 1;
        $counter = 0;
        $all_recipients = $recipients;
        while($run == 1) {
            if(count($all_recipients)>15) {
                $recipients = array_slice($all_recipients,0,15);
                $all_recipients = array_slice($all_recipients,15,count($all_recipients));
            }
            else {
                $recipients = $all_recipients;
                $run=0;
            }
            $delay = $counter * 180;
            Mail::later($delay,'emails.topic_update', $vars, function($message) use($vars, $recipients) {
                $message->from('labpal@calpoly.edu', 'LabPal')
                    ->bcc($recipients)
                    ->subject('New Response in LabPal Forum ' . $vars['title']);
            });
            $counter++;
        }

    }
} */

/* from save_forum()
        //Send email to subscribers if this is a new topic.
        if($data['forum_id'] == -1) {
            $course = Course::find($data['course']['id']);
            $subscribers = ForumSubscription::where('course_id',$data['course']['id'])->get();

            $recipients = array();
            foreach($subscribers as $subscriber) {
                if(!empty($subscriber))
                    if($subscriber->user_id != Auth::id())//Don't email yourself when you post.
                        array_push($recipients, User::find($subscriber->user_id)->email);
            }

            if(count($recipients)>0) { //Only email if there are actually subscribers.

                //Determine author name.
                if ($forum->anonymous)
                    $author = "Anonymous";
                else {
                    $user = User::find($forum->user_id);
                    $author = $user->firstname . " " . $user->lastname;
                }

                $vars = array(
                    'title' => $forum->title,
                    'course' => Course::find($forum->course_id)->name,
                    'forum_id' => $forum->id,
                    'author' => $author,
                    'answer' => $forum->question,
                );

                $run = 1;
                $counter = 0;
                $all_recipients = $recipients;
                while($run == 1) {
                    if(count($all_recipients)>15) {
                        $recipients = array_slice($all_recipients,0,15);
                        $all_recipients = array_slice($all_recipients,15,count($all_recipients));
                    }
                    else {
                        $recipients = $all_recipients;
                        $run=0;
                    }
                    $delay = $counter * 180;
                    Mail::later($delay,'emails.new_topic', $vars, function($message) use($vars, $recipients) {
                        $message->from('labpal@calpoly.edu', 'LabPal')
                            ->bcc($recipients)
                            ->subject('New LabPal Discussion Forum Topic ' . $vars['title']);
                    });
                    $counter++;
                }
            }
        } */
