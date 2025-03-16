<?php

namespace App\Http\Controllers;

use App\GradeGroup;
use App\GradeItem;
use App\GradeScore;
use Illuminate\Http\Request;
use App\Course;
use View;
use Response;
use Auth;

class GradeController extends Controller
{
    public function landing($cid) {
        $course = Course::where('id',$cid)
            ->with('students')
            ->with('grade_groups.items.scores')
            ->first();
        $view = View::make('instructor.grades.gradeLanding');

        $students = [];
        $student_ids = [];

        foreach($course->students as $student) {
            $students[$student->id] = $student;
            $student_ids[strtolower($student->email)] = $student->id;
        }


        $view->course = $course;

        $data = array(
            'course' => $course,
            'students' => $students,
            'student_ids' => $student_ids,
        );

        $view->data = json_encode($data);

        return $view;
    }

    public function student_view($cid) {
        $course = Course::find($cid);
        $grade_groups = GradeGroup::where('course_id',$cid)
            ->where('visible',1)
            ->with(['items' => function($query) {
                return $query->where('visible',1)->with(['scores' => function($query) {
                    return $query->where('user_id',Auth::id());
                }])->with('stats_scores');
            }])
            ->orderBy('order')
            ->get();

        foreach($grade_groups as $g => $grade_group ) {
            foreach($grade_group->items as $i => $item) {
                if(!isset($item->options["stats"]) || !$item->options["stats"]) {
                    unset($grade_groups[$g]->items[$i]->stats_scores);
                }
            }
        }

        $view = View::make('student.grades');
        $view->course = $course;
        $view->user = Auth::user();

        $data = array(
            'grade_groups' => $grade_groups,
        );

        $view->data = json_encode($data);

        return $view;
    }

    public function save_grades($cid, Request $request) {
        $grade_groups = json_decode($request->input("grade_groups"));

        $ids = [];
        $j=0;
        if($grade_groups == null)
            $grade_groups = [];
        foreach($grade_groups as $group) {
            $saved = $this->save_group($group, $cid);
            $group->id = $saved;
            array_push($ids,$saved);
            if(isset($group->sortedGrades)) {
                $this->update_grade_orders($group);
            }
            $j++;
        }
        foreach($grade_groups as $group) {
            if(($group->deleted ?? false) && $group->id > 0) {
                GradeItem::where('grade_group_id', $group->id)->delete();
                GradeGroup::find($group->id)->delete();
            }
        }

        //$this->delete_group($cid, $ids);

        $course = Course::where('id',$cid)
            ->with('students')
            ->with('grade_groups.items.scores')
            ->first();

        return Response::json([
            'status' => 'success',
            'course' => $course,
        ],200);
    }

    private function save_group($group, $cid) {
        if($group->id == -1)
            $g = new GradeGroup();
        else
            $g = GradeGroup::find($group->id);
        $g->course_id = $cid;
        $g->title = $group->title;
        $g->order = $group->order;
        $g->visible = $group->visible;
        try {
            $g->comments = $group->comments;
        }
        catch(\Exception $e) {
            $g->comments = "";
            \Log::error($e->getMessage());
        }
        $g->save();

        $ids = [];
        $j=0;

        if(!isset($group->items))
            $group->items = [];
        foreach($group->items as $item) {

            if(($item->deleted ?? false) && $item->id > 0) {
                GradeItem::where('id', $item->id)->delete();

            }
            else {
                $saved = $this->save_item($item, $g->id);
                array_push($ids, $saved);
            }
            $j++;
        }

        //$this->delete_items($g->id,$ids);


        return $g->id;
    }

    private function save_item($item, $group_id) {
        if($item->id == -1)
            $i = new GradeItem();
        else
            $i = GradeItem::find($item->id);

        $i->grade_group_id = $group_id;
        $i->title = $item->title;
        $i->order = $item->order;
        $i->visible = $item->visible;
        $i->options = $item->options ?? null;
        try {
            $i->possible = $item->possible;
        }
        catch(\Exception $e) {
            \Log::debug(json_encode($item));
            $i->possible = 100;
        }
        $i->save();

        foreach($item->scores as $score) {
            $this->save_scores($score, $i->id);
        }
        return $i->id;
    }

    private function save_scores($score, $item_id) {
        if($score->id == -1)
            $s = new GradeScore();
        else
            $s = GradeScore::find($score->id);
        $s->grade_item_id = $item_id;
        $s->user_id = $score->user_id;
        $s->earned = $score->earned;
        $s->viewed = $score->viewed;
        $s->save();

        return $s->id;
    }

    public function delete_group($cid, $ids) {
        $groups = GradeGroup::where('course_id', $cid)->get();
        foreach($groups as $group) {
            if(!in_array($group->id,$ids))
                $group->delete();
        }
    }

    /*public function delete_items($group_id,$ids) {
        $items = GradeItem::where('grade_group_id',$group_id)->get();
        foreach($items as $item) {
            if(!in_array($item->id,$ids))
                $item->delete();
        }
    }*/


    private function update_grade_orders($group) {
        $i = 0;
        foreach($group->sortedGrades as $gid) {
            $item = GradeItem::find($gid);
            \Debugbar::debug($gid, $item);
            $item->grade_group_id = $group->id;
            $item->order = $i;
            $item->save();
            $i++;
        }
    }
}
