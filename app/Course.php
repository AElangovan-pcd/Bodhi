<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Classroom;

class Course extends Model
{
    protected $fillable = ['parent_course_id', 'archived'];

    protected $casts = ['assistant_privs' => 'array'];

    public function users() {
        return $this->belongsToMany('App\User')
            ->withPivot('seat')
            ->orderBy('lastname');
    }

    public function students() {
        return $this->belongsToMany('App\User')
            ->where('instructor',0)
            ->withPivot('seat')
            ->orderBy('lastname');
    }

    public function owner() {
        return $this->belongsTo('App\User','owner');
    }

    public function linked_courses() {
        return $this->hasMany('App\Course','parent_course_id');
    }

    public function linked_parent_course() {
        return $this->hasOne('App\Course', 'id', 'parent_course_id');
    }

    public function link_requests_outgoing() {
        return $this->hasMany('App\LinkRequest','child_id');
    }

    public function link_requests_incoming() {
        return $this->hasMany('App\LinkRequest', 'parent_id');
    }

    public function assistants() {
        return explode("|",$this->assistants);
    }

    public function instructors() {
        return $this->belongsToMany('App\User')
            ->where('instructor',1)
            ->pluck('user_id');
    }

    public function hands(){
        return $this->hasMany('App\Hand');
    }

    public function seats(){
        return $this->hasMany('App\Seat');
    }

    public function assignments() {
        return $this->hasMany('App\Assignment')->orderBy('order');
    }

    public function inactive_assignments() {
        return $this->assignments()->where('active',0);
    }

    public function active_assignments() {
        return $this->assignments()->where('active',1);
    }

    public function polls(){
        return $this->hasMany('App\Poll');
    }

    public function forums() {
        return $this->hasMany('App\Forum');
    }

    public function forum_views() {
        return $this->hasMany('App\ForumView');
    }

    public function forum_answers() {
        return $this->hasManyThrough('App\ForumAnswer','App\Forum');
    }

    public function reviews() {
        return $this->hasMany('App\ReviewAssignment');
    }

    public function student_reviews() {
        return $this->reviews()
            ->whereRaw('state > 0')
            ->select('id','course_id','name','state');
    }

    public function infos() {
        return $this->hasMany('App\Info');
    }

    public function schedules() {
        return $this->hasMany('App\Schedule');
    }

    public function folders() {
        return $this->hasMany('App\Folder');
    }

    public function linked_folders() {
        return $this->hasMany('App\Folder','course_id','parent_course_id')
            ->with(['course_files' => function($query) {
                return $query->where('visible',true)->select(['id','name','folder_id','extension'])->orderBy('order');
            }])->select(['id','course_id','name','order'])->orderBy('order');
    }

    //TODO Consider package https://github.com/staudenmeir/laravel-merged-relations for merging folders and linked_folders

    public function course_files() {
        return $this->hasMany('App\CourseFile');
    }

    public function file_list() {
        $folders = $this->folders()  //->where('visible',true)
            ->with(['course_files' => function($query) {
                return $query->where('visible',true)->select(['id','name','folder_id','extension'])->orderBy('order');
            }])->select(['id','name'])->orderBy('order')->get();
        $folders = $this->linked_folders->merge($folders);

        $fileList = $folders->pluck('course_files')->collapse()->map(function($item, $key) {
            return [$item->name => $item->id];
        })->collapse();

        return $fileList;
    }

    public function assignment_list() {
        return $this->active_assignments->map(function($item, $key) {
            return [$item->name => $item->id];
        })->collapse();
    }

    public function classroom() {
        return Classroom::find($this->classroom_id);
    }

    public function grade_groups() {
        return $this->hasMany('App\GradeGroup')->orderBy('order');
    }

    public function toggleLinkable() {
        $this->linkable = !$this->linkable;
        $this->save();
        return $this->linkable;
    }

    public function push_assignment_to_linked_courses($aid) {
        try {
            $assignment = Assignment::with('linked_assignments:id,course_id,parent_assignment_id')->find($aid);
            $cnt = 0;
            $cnt2 = 0;

            $already_linked_cids = $assignment->linked_assignments->pluck('course_id')->toArray();
            foreach ($this->linked_courses as $course) {
                if(in_array($course->id, $already_linked_cids))
                    $cnt2++;
                else {
                    $assignment->generate_linked_assignment($course->id);
                    $cnt++;
                }
            }
        }
        catch(\Exception $e) {
            report($e);
            return ['msgType' => 'error', 'msg' => $e->getMessage()];
        }
        $msg = 'Generated '.$cnt.' new linked assignments.';
        if($cnt2>0)
            $msg .= " " . $cnt2 . " linked course". ($cnt2 > 1 ? "s" : "") ." already ". ($cnt2 > 1 ? "have" : "has") ." linked assignments.";
        return ['msgType' => 'status', 'msg' => $msg];
    }

    public function addActiveToEnd($assignment) {
        $actives = $this->assignments->filter(function($a) {
            return $a->active;
        });

        $largest = 0;
        foreach ($actives as $a) {
            $largest = max($largest, $a->order);
        }

        $assignment->active = true;
        $assignment->order = $largest + 1;
        $assignment->save();
    }

    public static function coursesByOwnerLastName() {
        $owner_ids = [];
        $id_courses = [];
        $list = [];
        $alphas = [];
        $alpha_list = [];

        $classes = Course::all()->filter(function ($c) {
            return $c->active;
        })->sortBy(function($c) { return $c->name; });
        /* grab all owner ids based on course owners */

        foreach ($classes as $c) {
            $id_courses[$c->owner][] = $c;
            if (!in_array($c->owner, $owner_ids, true)) {
                $owner_ids[] = $c->owner;
            }
        }

        /* for every owner id, grab their name, what their last name starts with
        and all courses owned by them. Create an associateive array with all info
        that looks like:

        [letter last name starts with]:
        ----[owner id number]:
        --------[full name]
        --------[list of courses]
                ...
        */
        foreach ($owner_ids as $id) {
            $owner = User::find($id);
            $alpha_last_name = strtoupper(substr($owner->lastname, 0, 1));

            if (!in_array($alpha_last_name, $alphas)) {
                $alphas[] = $alpha_last_name;
            }

            $list[$id]["owner"] = $owner->fullNameLastFirst();
            $list[$id]["courses"] = $id_courses[$id];

            $alpha_list[$alpha_last_name][] = $list[$id];
        }
        foreach ($alpha_list as $key => $alist) {
            usort($alist, function($a, $b) {
                return strcmp($a["owner"], $b["owner"]);
            });
        }
        ksort($alpha_list); // sorts by key, key being the letter the last name starts with

        return Array("alphas" => $alphas, "list" => $alpha_list);
    }

    // @override
    public function delete()
    {
        //Temporary fix for inefficiency: just delete the course record, but leave everything else
        /*
        // Delete all assignments underneath
        foreach($this->assignments as $ass)
            $ass->delete();

        //Delete all polls underneath
        foreach($this->polls as $poll)
            $poll->delete();

        //Delete all forums underneath
        foreach($this->forums as $forum)
            $forum->delete();

        //Delete all seats
        foreach($this->seats as $seat)
            $seat->delete();

        // detach all course_users
        foreach($this->users as $usr)
            $usr->courses()->detach($this->id);

        // Delete all review assignments
        foreach($this->reviews as $rev)
            $rev->delete();

        // Delete all course infos
        foreach($this->infos as $inf)
            $inf->delete();

        //Delete all course schedules
        foreach($this->schedules as $sch)
            $sch->delete();

        // Delete all files and folders
        foreach($this->folders as $folder)
            $folder->delete();
*/
        //Delete the course
        return parent::delete();
    }
}
