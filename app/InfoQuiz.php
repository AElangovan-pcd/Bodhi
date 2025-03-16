<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InfoQuiz extends Model
{
    protected $casts = ['options' => 'array'];

    public function info() {
        return $this->belongsTo('App\Info');
    }

    public function info_quiz_questions() {
        return $this->hasMany('App\InfoQuizQuestion');
    }

    public function copy_to_new_info($iid) {
        $info_quiz = $this->replicate();
        $info_quiz->info_id = $iid;
        $info_quiz->save();
        foreach($this->info_quiz_questions as $q) {
            $q->copy_to_new_quiz($info_quiz->id);
        }
    }
}
