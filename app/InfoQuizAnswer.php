<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InfoQuizAnswer extends Model
{
    protected $casts = ['answer' => 'array'];

    public function info_quiz_question() {
        return $this->belongsTo('App\InfoQuizQuestion');
    }

    public function copy_to_new_question($iid) {
        $info = $this->replicate();
        $info->info_quiz_question_id = $iid;
        $info->save();
    }
}
