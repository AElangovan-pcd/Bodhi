<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InfoQuizQuestion extends Model
{
    protected $casts = ['options' => 'array','choices'=> 'array', 'answer'=>'array'];

    public function info_quiz() {
        return $this->belongsTo('App\InfoQuiz');
    }

    public function info_quiz_answers() {
        return $this->hasMany('App\InfoQuizAnswer');
    }

    public function copy_to_new_quiz($iid) {
        $info = $this->replicate();
        $info->info_quiz_id = $iid;
        $info->save();
        foreach($this->info_quiz_answers as $q) {
            $a->copy_to_new_question($info->id);
        }
    }
}
