<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Course;
use View;
use Illuminate\Support\Facades\Input;
use App\Info;
use App\InfoQuiz;
use App\InfoQuizQuestion;
use App\InfoQuizAnswer;
use Carbon\Carbon;
use App\Schedule;
use Log;
use Response;
use Auth;
use File;
use Redirect;

class InfoController extends Controller
{
    public function landing($course_id) {
        $course = Course::where('id',$course_id)
            ->with('linked_courses')
            ->with(['schedules' => function($query) {
            return $query->where('type','info')->orderBy('time');
        }])->first();
        //$course = Course::find($course_id);
        $view = View::make('instructor.info.infoLanding');
        $view->course = $course;
        $infos = Info::where('course_id',$course_id)->orderBy('order')
            ->with(['info_quizzes' => function($query) {
                return $query->orderBy('order')
                    ->with(['info_quiz_questions' => function($query2) {
                    $query2->orderBy('order');
                }
                ]);
            }])->get();
        $folders = $course->folders()->where('visible',true)
            ->with(['course_files' => function($query) {
                return $query->where('visible',true)->select(['id','name','folder_id','extension'])->orderBy('order');
            }])->select(['id','name'])->orderBy('order')->get();
        $linked_folders = $course->linked_folders;

        //Merge parent files with files
        $folders = $linked_folders->merge($folders);

        $active = $course->assignments()->where('active',1)->orderBy('order')->get();
        $inactive = $course->assignments()->where('active',0)->orderBy('order')->get();
        $assignments = $active->merge($inactive);

        $data = array(
            "course"      => $course,
            "infos"       => $infos,
            "folders"     => $folders,
            "assignments" => $assignments,
        );

        $view->data = json_encode($data);
        return $view;
    }

    public function push_to_linked_courses($cid, $iid) {
        $course = Course::select('id')->with('linked_courses:id,parent_course_id')->find($cid);
        $info = Info::find($iid);
        $cnt = 0;
        foreach($course->linked_courses as $lc) {
            $info->copy_to_course($lc->id);
            $cnt++;
        }
        return back()->with('status', "Pushed info block to ".$cnt." child courses. These info blocks now belong to the child courses and cannot be modified from the parent course.");
    }

    public function export_json($cid) {
        $infos = Info::where('course_id',$cid)->orderBy('order')
            ->with(['info_quizzes' => function($query) {
                return $query->orderBy('order')
                    ->with(['info_quiz_questions' => function($query2) {
                        $query2->orderBy('order');
                    }
                    ]);
            }])->get();
        $json = json_encode($infos, JSON_PRETTY_PRINT);
        $course = Course::find($cid);
        return Response::make($json, '200', array(
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="'.$course->name.'_information.json"'
        ));
    }

    public function import_json($cid, Request $request) {
        $file = $request->file('info_import');
        $infos = json_decode(file_get_contents($file->getRealPath()));

        //Get the number of infos to set order by appending to the end of existing infos
        $i = Info::where('course_id',$cid)->count();

        foreach($infos as $info) {
            //Set all of the ids to -1 to indicate that they are new and not replacements
            $info->id = -1;
            foreach($info->info_quizzes as $quiz) {
                $quiz->id = -1;
                foreach($quiz->info_quiz_questions as $question) {
                    $question->id = -1;
                }
            }
            //Save
            $this->save_info($info, $cid, $i);
            $i++;
        }

        //File::delete($file->getRealPath());

        return Redirect::back()->with('status','Information Uploaded');
    }

    public function info_quiz_results($cid, $qid) {
        $quiz = InfoQuiz::where('id',$qid)->with('info_quiz_questions.info_quiz_answers')->first();
        if($quiz->info->course_id != $cid)
            abort(403, "Unauthorized action");

        $view = View::make('instructor.info.infoResults');
        $course = Course::find($cid);
        $view->course = $course;

        $students = $course->users;

        $studs = [];
        $quests = [];

        foreach($students as $key => $s) {
            $studs[$s->id] = $key;
        }
        foreach($quiz->info_quiz_questions as $key => $q) {
            $quests[$q->id] = $key;
        }

        $i=0;
        foreach($students as $stu) {
            $rows[$i]["firstname"]   = $stu->firstname;
            $rows[$i]["lastname"] = $stu->lastname;
            $rows[$i]["seat"] = $stu->pivot->seat;
            $rows[$i]["email"] = $stu->email;
            $rows[$i]["answers"] = array_fill(0,count($quiz->info_quiz_questions),"");
            $i++;
        }

        foreach($quiz->info_quiz_questions as $question) {
            foreach($question->info_quiz_answers as $ans) {
                $rows[$studs[$ans->user_id]]["answers"][$quests[$question->id]] = $ans;
            }
        }


        $questions = $quiz->info_quiz_questions()
            ->with(['info_quiz_answers'])->get();

        $data = array(
            "course" => $course,
            "quiz"  => $quiz,
            "students" => $students,
            "questions" => $questions,
            "rows"  => $rows,
        );
        $view->data = json_encode($data);
        return $view;
    }

    public function save_infos($course_id) {
        $data = json_decode(Input::get('data'));
        $ids = [];
        $iids = [];
        $i=0;
        foreach($data->infos as $info) {
            $saved = $this->save_info($info, $course_id, $i);
            array_push($ids, $saved);
            array_push($iids, $saved['info_id']);
            $i++;
        }
        $this->delete_infos($course_id, $iids);
        return json_encode(['status' => 'success', 'ids' => $ids]);
    }

    public function save_info($info, $course_id, $order) {
        if($info->id == -1) {
            $newInfo = new Info();
            $newInfo->course_id = $course_id;
        }
        else
            $newInfo = Info::find($info->id);

        $newInfo->active = $info->active;
        $newInfo->visible = $info->visible;
        $newInfo->title = $info->title;
        $newInfo->text = $info->text;  //TODO Add purifier
        $newInfo->options = $info->options;
        $newInfo->order = $order;

        $newInfo->save();

        $ids = [];
        $qids = [];
        $i=0;
        foreach($info->info_quizzes as $quiz) {
            $saved = $this->save_info_quiz($quiz, $newInfo->id, $i);
            array_push($ids, $saved);
            array_push($qids, $saved['quiz_id']);
            $i++;
        }
        $this->delete_info_quizzes($info->id, $qids);

        return array(
            'info_id' => $newInfo->id,
            'quiz_ids' => $ids,
            );
    }

    public function save_info_quiz($quiz, $info_id, $order) {
        if($quiz->id == -1) {
            $newQuiz = new InfoQuiz();
            $newQuiz->info_id = $info_id;
        }
        else
            $newQuiz = InfoQuiz::find($quiz->id);

        $newQuiz->closed = $quiz->closed;
        $newQuiz->description = $quiz->description;
        $newQuiz->state = $quiz->state;
        $newQuiz->visible = $quiz->visible;
        $newQuiz->order = $order;

        $newQuiz->save();

        $ids = [];
        $qids = [];
        $i=0;
        foreach($quiz->info_quiz_questions as $question) {
            $saved = $this->save_info_quiz_question($question, $newQuiz->id, $i);
            array_push($ids, $saved);
            array_push($qids, $saved['question_id']);
            $i++;
        }
        $this->delete_info_quiz_questions($quiz->id, $qids);

        return array(
            'quiz_id' => $newQuiz->id,
            'question_ids' => $ids,
            );
    }

    public function save_info_quiz_question($question, $quiz_id, $order) {
        if($question->id == -1) {
            $newQuestion = new InfoQuizQuestion();
            $newQuestion->info_quiz_id = $quiz_id;
        }
        else
            $newQuestion = InfoQuizQuestion::find($question->id);

        $newQuestion->description = $question->description;
        $newQuestion->type = $question->type;
        $newQuestion->choices = $question->choices;
        $newQuestion->options = $question->options;
        $newQuestion->answer = $question->answer;
        $newQuestion->points = $question->points;
        $newQuestion->order = $order;

        $newQuestion->save();

        return array(
            'question_id' => $newQuestion->id,
        );
    }

    public function delete_infos($course_id, $iids) {
        $infos = Info::where('course_id', $course_id)->get();
        foreach($infos as $info) {
            if(!in_array($info->id, $iids))
                $info->delete();
        }
    }

    public function delete_info_quizzes($info_id, $qids) {
        $quizzes = InfoQuiz::where('info_id', $info_id)->get();
        foreach($quizzes as $quiz) {
            if(!in_array($quiz->id, $qids))
                $quiz->delete();
        }
    }

    public function delete_info_quiz_questions($quiz_id, $qids) {
        $questions = InfoQuizQuestion::where('info_quiz_id', $quiz_id)->get();
        foreach($questions as $question) {
            if(!in_array($question->id, $qids))
                $question->delete();
        }
    }

    public function submit_quiz_answers($cid, Request $request) {
        $quiz = $request->input('quiz');
        foreach($quiz['info_quiz_questions'] as $question) {
            $this->save_answer($question);
        }
        return Response::json([
            'status' => 'success',
        ],200);
    }

    private function save_answer($question) {
        $answer = InfoQuizAnswer::where('user_id', Auth::id())->where('info_quiz_question_id', $question['id'])->orderBy('CREATED_AT', 'desc')->first();
        if($answer == null) {
            $answer = new InfoQuizAnswer();
            $answer->user_id = Auth::id();
            $answer->info_quiz_question_id = $question['id'];
        }
        if($question['type'] == 1 || $question['type'] == 2)
            $this->save_MC_answer($question, $answer);
    }

    private function save_MC_answer($question, $answer) {
        if(!isset($question['info_quiz_answers']))
            return;
        $answer->answer = $question['info_quiz_answers'][0]['answer'];
        $full_question = InfoQuizQuestion::find($question['id']);
        if($answer->answer == $full_question['answer'])
            $answer->earned = $full_question['points'];
        else
            $answer->earned = 0;

        $answer->save();
    }

    public function regrade_info_quiz($cid, $qid) {
        $quiz = InfoQuiz::where('id',$qid)->with('info_quiz_questions.info_quiz_answers')->first();
        if($quiz->course_id != $cid && Auth::user()->admin != 1)
            abort(403, "Unauthorized action");

        foreach($quiz->info_quiz_questions as $question) {
            foreach($question->info_quiz_answers as $answer) {
                if($question['type'] == 1 || $question['type'] == 2) {
                    if($answer->answer == $question->answer)
                        $answer->earned = $question->points;
                    else
                        $answer->earned = 0;
                    $answer->save();
                }
            }
        }

        return Redirect::back()->with('status','Quiz regraded.');
    }

    public function save_schedules($cid, Request $request) {
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
        $scheds = Schedule::where('course_id', $cid)->where('type','info')->get();
        return json_encode($scheds);
    }

    public static function run_task($task) {
        try {
            $info = Info::find($task->details['info_id']);
            if($info == null) {
                Log::debug('Null Info ID for Task ' . $task->id . ' for course ' . $task->course_id . '. Type: ' . $task->type . '. Info ID: ' . $task->details['info_id']);
                $task->completed = 1;
                $task->save();
                return;
            }
            if ($task->details['property'] == "Active") {
                if ($task->details['state'] == 1) {
                    //Make any already active infos inactive before setting a new one active.
                    $actives = Info::where('course_id', $task->course_id)->where('active', 1)->get();
                    foreach ($actives as $active) {
                        $active->active = 0;
                        $active->save();
                    }
                }
                //Set the requested info to the requested state
                $info->active = $task->details['state'];
                $info->save();
            } else if ($task->details['property'] == "Visible") {
                $info->visible = $task->details['state'];
                $info->save();
            }
            $task->completed = 1;
            $task->save();

            Log::debug('Ran task ' . $task->id . ' for course ' . $task->course_id . '. Type: ' . $task->type . '.');
        }
        catch(Throwable $e) {
            Log::debug('Error running ' . $task->id . ' for course ' . $task->course_id . '. Type: ' . $task->type . '. ' . $e->getMessage());
        }
    }
}
