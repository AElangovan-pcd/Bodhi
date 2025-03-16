<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Info extends Model
{

    protected $casts = ['options' => 'array','text'=> 'array'];

    public function course() {
        return $this->belongsTo('App\Course');
    }

    public function info_quizzes() {
        return $this->hasMany('App\InfoQuiz');
    }

    public function copy_to_course($cid) {
        $info = $this->replicate();
        $info->course_id = $cid;
        $info->order = Info::select('course_id')->where('course_id',$cid)->count();
        $info->save();
        foreach($this->info_quizzes as $iq) {
            $iq->copy_to_new_info($info->id);
        }
    }
}
