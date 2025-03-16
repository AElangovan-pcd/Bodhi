<?php

namespace App\Http\Controllers;

use Request;
use App\Course;
use View;
use Auth;
use Illuminate\Support\Facades\Input;
use App\Poll;
use App\PollAnswer;
//use Mews\Purifier\Facades\Purifier;
use Stevebauman\Purify\Facades\Purify as Purifier;
use App\Events\NewPoll;
use App\Events\ClosePoll;
use App\Events\PollAnswered;
use Response;
use File;
use Redirect;

class PollController extends Controller
{
    public function landing($course_id) {
        $course = Course::find($course_id);
        $polls = $course->polls;
        $new_polls = $course->polls()->where('complete', '!=', 1)->where('active','!=',1)->orderBy('order')->get();
        $completed = $course->polls()->where('complete',1)->orderBy('updated_at')->get();
        $in_prog = $course->polls()->where('complete', '!=', 1)->where('active',1)->get();;

        $view = View::make('instructor.poll.PollLanding');
        $view->course = $course;

        $data = array(
            "new_polls"   => $new_polls,
            "completed"   => $completed,
            "in_progress" => $in_prog,
            "classes"     => Auth::user()->owned_courses(),
            "course"      => $course,
        );

        $view->data = json_encode($data);
        return $view;
    }

    public function all_poll_results($cid) {
        $view = View::make('instructor.poll.AllResults');
        $course = Course::with('polls.poll_answers')
            ->with('students')
            ->find($cid);

        $data = array(
            "course" => $course,
        );
        $view->course = $course;
        $view->data = json_encode($data);

        return $view;
    }

    /*public function all_poll_results_old($course_id) {
        $view = View::make('instructor.poll.AllResults');
        $course = Course::find($course_id);
        $view->class = $course;
        $view->course = $course;
        $polls = $course->polls;
        $students = Course::find($course_id)->students();
        $completed = array();
        $sorts = array("First Name" => true, "Last Name" => true, "Seat" => true); // for sorting the table

        foreach ($polls as $p) {
            if ($p->complete) {
                $completed[] = $p;
                $sorts[$p->name] = true;
            }
        }
        $rows = array();
        $i=0;
        foreach ($students as $stu) {
            $row = array();
            $row['firstname']=$stu->firstname;
            $row['lastname']=$stu->lastname;
            $row['seat']=$stu->getSeatForCourse($course->id);
            $row['email']=$stu->email;
            $j=0;
            $answered_count = 0;
            foreach ($completed as $p) {
                $row['score'][$j]= DB::table('poll_answers')->where("user_id",'=',$stu->id)
                    ->where("poll_id",'=',$p->id)->pluck('answer');
                if($row['score'][$j] == null)
                    $row['score'][$j] = "";
                else $answered_count++;
                $row['score_trimmed'][$j] = substr($row['score'][$j],0,10);
                if ($row['score_trimmed'][$j] == false)
                    $row['score_trimmed'][$j] = "";
                $j++;
            }
            $row['answered_count'] = $answered_count;
            $rows[]=$row;
        }

        $data = array(
            "course" => $course,
            "rows" => $rows,
            "polls" => $completed,
            "sorts" => $sorts,
        );
        $view->data = json_encode($data);

        return $view;
    } */

    public function create_poll($class_id)
    {
        $view = View::make('instructor.poll.CreatePoll');
        $view->course = Course::find($class_id);
        $data = array(
            'choices' => array(),
            'course'  => $view->course,
            'poll_id' => -1,
            'poll_type' => 0,
            'background' => 'data:image/gif;base64,R0lGODlhAQABAAAAACwAAAAAAQABAAA=',
        );
        $view->data = json_encode($data);
        return $view;
    }

    public function edit_poll($cid,$pid) {
        //TODO verify that poll belongs to course
        $poll = Poll::find($pid);
        $view = View::make('instructor.poll.CreatePoll');
        $view->course = Course::find($poll->course_id);
        $data = array(
            'choices'       => $poll->choices(),
            'course'        => $view->course,
            'poll_id'       => $poll->id,
            'poll_name'     => $poll->name,
            'poll_question' => $poll->question,
            'poll_type'		=> $poll->type,
            'background'    => $poll->image,
            'ready'         => true,
        );
        $view->data = json_encode($data);
        return $view;
    }

    public function save_poll() {
        $data = Input::get();

        if ($data['poll_id'] == -1)
            $poll = new Poll();
        else
            $poll = Poll::find($data['poll_id']);

        $poll->course_id = $data['course']['id'];
        $poll->name = strip_tags(Purifier::clean($data['name']));  //Sanitize with HTMLPurifier
        //$poll->question = Purifier::clean($data['question']);
        $poll->question = $data['question'];
        $poll->type = $data['pollType'];
        $alts = "";
        if($poll->type==0) {
            foreach ($data['alternatives'] as $key => $alt) {
                $alts .= $alt['text'] . " | ";
            }
            $poll->choices = trim($alts, " | ");
        }
        if($poll->type==2) {
            $poll->image = $data['image'];
        }
        $poll->active = false;
        $poll->save();

        return $poll->id;
    }

    public function update_poll_order($cid) {
        $new = Input::get("newOrder");
        \Debugbar::info($new);
        if($new != null) {
            for ($i = 1; $i <= count($new); $i++) {
                \Debugbar::info($new[$i-1]);
                Poll::where('id', $new[$i - 1])->update(['order' => $i]);
            }
        }
        return "Order updated.";
    }

    public function studentView($course_id) {
        $course = Course::find($course_id);
        $polls = $course->polls;
        $view = View::make('Polls');
        $data = array(
            "polls" => $polls);
        $view->class = $course;
        $view->data = json_encode($data);
        return $view;
    }

    public function poll_results($cid, $pid)
    {
        $poll             = Poll::with('poll_answers')->find($pid);
        $view             = View::make('instructor.poll.PollResults');
        $course           = Course::with('seats')
            ->with(['students' => function ($q) { $q->where('instructor',0);}])
            ->find($cid);
        $students         = $course->students;

        $answers = $poll->poll_answers;
        if($poll->type == 0) {
            $poll->choices = $poll->choices();
            $votes = array();
            foreach($poll->choices as $c)
                $votes[$c] = 0;
            foreach($answers as $a)
                $votes[$a->answer]++;
            $poll->votes = array_values($votes);
        }
        $data             = array();
        $data["poll"]     = $poll;
        $data["course"]   = $course;
        $data["initial_data"]  = PollAnswer::where('poll_id', $pid)->get();
        $data["sorts"]	  = ["firstname" => true, "lastname" => true, "seat" => true, "answer" =>true];
        $data["poll_type"] = $poll->type;
        $view->poll       = $poll;
        $view->class      = $course;

        foreach ($students as $stu) {
            $ans = $data["initial_data"]->where("user_id",'=',$stu->id)
                ->first();
            if(!$ans)
                $stu->answer = "";
            else
            $stu->answer = $ans['answer'];

            if (!$stu->answer)
                $stu->answer = "";
            //$stu->seat = $stu->seat_for_course($course->id);
        }
        $stuArr = [];
        foreach ($students as $s) {
            $stuArr[] = $s;
        }
        $data["students"] = $stuArr;
        $view->course = $course;
        $view->data = json_encode($data);
        return $view;
    }

    public function take_poll($cid)
    {
        $view = View::make('student.poll.TakePoll');

        $poll     = Poll::where('course_id', $cid)
            ->where('active', true)
            ->where('complete', false)
            ->orderBy('updated_at', 'desc')
            ->with(['answer' => function($q) { return $q->where('user_id', Auth::id());}])
            ->first();

        /*
        if (isset($poll)) {
            $answer = PollAnswer::where('poll_id', $poll->id)
                ->where('user_id', Auth::user()->id)
                ->first();
            if($poll->choices !=null)
                $poll->choices = $poll->choices();
            $data['poll'] = $poll;
            $data['pollType'] = $poll->type;
        }

        if ($poll !== null && $poll->complete == false) {
            if ($answer !== null) {
                $data['answered'] = true;
                $choices = $poll->choices;
                if($choices != null)
                    $data['choice_index'] = array_search($answer->answer, $choices);
                $data['SAval'] = $answer->answer;
            }
            else {
                $data['answer'] = false;
                $data['answered'] = false;
            }

        }
        */

        $view->course = Course::find($cid);
        $view->user = Auth::user();
        $view->pollPage = true;

        $data = ['poll' => $poll];

        $view->data = json_encode($data);
        return $view;
    }

    public function get_poll($cid) {
        $poll     = Poll::where('course_id', $cid)
            ->where('active', true)
            ->where('complete', false)
            ->orderBy('updated_at', 'desc')
            ->with(['answer' => function($q) { return $q->where('user_id', Auth::id());}])
            ->first();
        /*if (isset($poll)) {
            $answer = PollAnswer::where('poll_id', $poll->id)
                ->where('user_id', Auth::user()->id)
                ->first();
            if($poll->choices !=null)
                $poll->choices = $poll->choices();
            $data['poll'] = $poll;
            $data['pollType'] = $poll->type;
        }

        if ($poll !== null && $poll->complete == false) {
            if ($answer !== null) {
                $data['answered'] = true;
                $choices = $poll->choices;
                if($choices !=null)
                    $data['choice_index'] = array_search($answer->answer, $choices);
                $data['SAval'] = $answer->answer;
            }
            else {
                $data['answer'] = false;
                $data['answered'] = false;
            }
        } */
        return ['poll' => $poll];
    }

    public function update_answers($id)
    {
        $poll = Poll::findorfail(Input::get('poll_id'));
        $answers = PollAnswer::where('poll_id', '=', $id)->get();
        return json_encode($answers);
    }

    public function get_polls()
    {
        $course_id = $_POST['course_id'];
        $student_id = $_POST['student_id'];
        $polls = Course::findorfail($course_id)->polls;
        $data = array();
        if (count($polls) > 0){
            foreach ($polls as $k => $poll) {
                if ($poll->active) {
                    $data[$k]["date"] = date_format($poll->created_at, "M d, h:i a");
                    $data[$k]["poll_id"] = $poll->id;
                }
            }
        }
        else $data = "No polls at this time.";
        return json_encode($data);
    }

    public function answer_poll()
    {
        $data = Input::get('data');
        $poll = Poll::findorfail($data['poll']['id']);
        if ($poll->type==0)
            $answer = $data['choice_index'];
        elseif($poll->type==1)
            $answer = strip_tags(Purifier::clean($data['short_answer']));
        elseif($poll->type==2)
            $answer = $data['drawing'];
        $p_answer = new PollAnswer();

        if ($poll->type==0)
            $p_answer->answer = $poll->choices()[$answer];
        else  //For both short answer and drawings.
            $p_answer->answer = $answer;
        $p_answer->user_id = Auth::id();
        $p_answer->poll()->associate($poll);
        $p_answer->save();

        //$this->checkComplete($poll);

        broadcast(new PollAnswered($poll->course_id,$p_answer));

        return "Submitted";

    }

    public function delete_poll()
    {
        $poll = Poll::findorfail(Input::get('id'));
        $pans = $poll->poll_answers()->get();
        foreach ($pans as $pan) {
            $pan->delete();
        }
        $poll->delete();
        return "success";
    }

    public function activate_poll($cid)
    {
        $active = Poll::where('course_id', $cid)
            ->where('active', true)
            ->where('complete', false)
            ->orderBy('updated_at', 'desc')
            ->first();
        if (isset($active)) {
            return "fail";
        }

        $poll = Poll::findorfail(Input::get('id'));
        $poll->active = 1;
        $poll->save();

        broadcast(new NewPoll($poll->course_id,$poll->id));

        return "success";
    }

    public function deactivate_poll($id)
    {
        $poll = Poll::findorfail($id);
        $poll->active = 0;
        $poll->save();
        return Redirect::back();
    }

    public function check_active($course_id) {  //Check if there is a poll running for this course.
        $course   = Course::findorfail($course_id);
        $id       = -1; $name = "";
        $active   = false;
        $complete = false;
        $poll     = Poll::where('course_id', $course_id)
            ->where('active', true)
            ->where('complete', false)
            ->orderBy('updated_at', 'desc')
            ->first();
        if (isset($poll)) {
            $answer = PollAnswer::where('poll_id', $poll->id)
                ->where('user_id', Auth::user()->id)
                ->first();
        }

        if ($poll !== null && $poll->complete == false) {
            if ($answer !== null)
                $complete = true;

            $active = true;
            $id = $poll->id;
            $name = $poll->name;
        }

        $data = array(
            'active' => $active,
            'complete' => $complete,
            'id' => $id,
            'name' => $name
        );
        return json_encode($data);
    }

    public function complete_poll() {
        $poll = Poll::findorfail(Input::get('id'));
        $poll->complete = true;
        $poll->save();

        broadcast(new ClosePoll($poll->course_id,$poll->id));

        return "success";
    }

    public function restart_poll($cid) {
        $active = Poll::where('course_id', $cid)
            ->where('active', true)
            ->where('complete', false)
            ->orderBy('updated_at', 'desc')
            ->first();
        if (isset($active)) {
            return "fail";
        }

        $poll = Poll::findorfail(Input::get('id'));
        $poll->complete = false;
        $poll->save();

        broadcast(new NewPoll($poll->course_id,$poll->id));

        return "success";
    }

    public function duplicate_poll() {  //Duplicate poll within the course.
        $poll = new Poll;
        $id = Input::get("id");
        $dup = Poll::find($id);
        $poll->name = $dup->name;
        $poll->question = $dup->question;
        $poll->choices = $dup->choices;
        $poll->course_id = $dup->course_id;
        $poll->image = $dup->image;
        $poll->type = $dup->type;
        $poll->active = false;
        $poll->complete = false;
        $poll->save();
        return json_encode(array("id" => $poll->id, "poll" => $poll));
    }

    public function copy_poll() {  //Copy poll to another course.
        $poll = new Poll;
        $dup = Input::get("poll");
        $poll->name = $dup['name'];
        $poll->question = $dup['question'];
        $poll->choices = $dup['choices'];
        $poll->course_id = Input::get("new_course_id");
        $poll->image = $dup['image'];
        $poll->type = $dup['type'];
        $poll->active = false;
        $poll->complete = false;
        $poll->save();
        return json_encode(array("id" => $poll->id));
    }

    private function checkComplete($poll) {
        // check if all students have answered the poll question
        $course = Course::find($poll->course_id);
        $num_stus = count($course->students());

        if ($num_stus == count($poll->poll_answers())) {
            $poll->complete = true;
            $poll->save();
        }
    }

    public function lower_hand($assignment_id, $student_id) {
        $course_id = Assignment::findorfail($assignment_id)->course->id;
        $hand = Hand::where('course_id', '=', $course_id)
            ->where('user_id', '=', $student_id)->first();

        if ($hand != null) {
            $hand->delete();
            return "...";
        }

        return "Your hand was not up.";
    }

    public function hands_for_course($course_id){
        $view = View::make('professor.SubViews.HandsForAssignment');

        $course = Course::findorfail($course_id);

        $view->hands = $course->hands->sortBy(function($hand) {
            return $hand->created_at;
        });
        return $view;
    }

    public function hands_JSON($course_id) {
        $hands = Assignment::findorfail($course_id)->course->hands;
        $hands_array = array();

        foreach ($hands as $hand) {
            $hands_array[$hand->user->seat] = $hand->id;
        }

        return Response::json($hands_array);
    }

    public function dismiss_hand($student_id, $course_id) {
        $hand = Hand::where('course_id', '=', $course_id)
            ->where('user_id', '=', $student_id)->first();

        if ($hand != null)
            $hand->delete();
    }

    public function place_in_line() {
        $student_id = Input::get('student_id');
        $course_id = Input::get('course_id');
        // $assignment_id = Input::get('assignment_id');

        $my_hand = Hand::where('course_id', '=', $course_id)->where('user_id', '=', $student_id)->first();

        if ($my_hand == null)
            // return 'Your hand is down';
            return "0";

        $course = Course::findorfail($course_id);

        $hands = $course->hands->sortBy(function($hand) {
            return $hand->created_at;
        });

        $position = 0;

        foreach($hands as $i => $hand) {
            if ($hand == $my_hand)
                $position = $i + 1;
        }

        if ($position == 0)
            throw new Exception('Hand not found.');

        if ($position % 10 == 1)
            return $position."st";
        else if ($position % 10 == 2)
            return $position."nd";
        else if ($position % 10 == 3)
            return $position."rd";
        else
            return $position."th";
    }

    public function export_xml($cid) {
        //TODO change to be selectable based on input, not necessarily all polls in the course
        $course = Course::find($cid);
        $polls = $course->polls;
        $xml = '<polls>';
        foreach($polls as $p) {
            $xml .= "\t".'<poll>'.PHP_EOL;
            $xml .= "\t\t".'<name>'.$p->name.'</name>'.PHP_EOL;
            $xml .= "\t\t".'<type>'.$p->type.'</type>'.PHP_EOL;
            if($p->question != null)
                $xml .= "\t\t".'<question>'.$p->question.'</question>'.PHP_EOL;
            if($p->choices != null)
                $xml .= "\t\t".'<choices>'.$p->choices.'</choices>'.PHP_EOL;
            if($p->image != null)
                $xml .= "\t\t".'<image>'.$p->image.'</image>'.PHP_EOL;
            $xml .= "\t".'</poll>'.PHP_EOL;
        }
        $xml .= '</polls>';
        return Response::make($xml, '200', array(
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="'.$course->name.'_polls.xml"'
        ));
    }

    public function import_xml($cid) {
        $file = Request::file('assignment_import');
        $name = $file->getClientOriginalName();
        //$file = $file->move(getcwd(), $name);  //TODO change this not to use the public directory

        try {
            //$contents = file_get_contents(getcwd() . "/" . $name);
            $contents = file_get_contents($file->getRealPath());
            $tags = array('name', 'question', 'choices', 'image');
            foreach ($tags as $tag) {
                $contents = str_replace('<' . $tag . '>', '<' . $tag . '><![CDATA[', $contents);
                $contents = str_replace('</' . $tag . '>', ']]></' . $tag . '>', $contents);
            }
            $contents = str_replace('<br>', '<br/>', $contents);
            $xml = simplexml_load_string($contents);
            $num=0;
            foreach($xml->poll as $p) {
                $num++;
                if($p->name == NULL) {
                    $status = "error";
                    $importMsg = "Poll " . $num . " needs a name.";
                    //return $this->delete_file($name, $status, $importMsg, $file);
                    return Redirect::back()->with($status, $importMsg);
                }
                $poll = new Poll();
                $poll->course_id = $cid;
                $poll->name = $p->name;
                $poll->type = $p->type;
                if($p->question != null)
                    $poll->question = $p->question;
                if($p->image != null)
                    $poll->image = $p->image;
                if($p->choices != null)
                    $poll->choices = $p->choices;
                $poll->save();
            }
        }
        catch (Exception $e) {
            if($poll != null)
                $poll->delete();
            //return $this->delete_file($name, "error", $e->getMessage().' '.$e->getLine(), $file); // return back with error message
            $status = "error";
            $importMsg = $e->getMessage().' '.$e->getLine();
            return Redirect::back()->with($status, $importMsg);
        }

        $status = "success";
        $importMsg = "Polls imported!";
        return Redirect::back()->with($status, $importMsg);
        //return $this->delete_file($name,"success", "Polls imported!", $file);
    }

    /*
    private function delete_file($name, $status, $importMsg, $file) {
        File::delete(getcwd().'/'.$name);
        File::delete($file);
        return Redirect::back()->with($status, $importMsg);
    }*/

}
