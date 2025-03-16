<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Score;
use App\Question;

class Answer extends Model
{

    protected $fillable = ['user_id','question_id','submission','created_at','updated_at'];

    public function getSubmissionAttribute($value) {
        if($this->question == null)
            return $value;
        if($this->question->type == Question::REACTION_QUESTION)
            return json_decode($value);
        return $value;
    }

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function assignment() {
        return $this->belongsTo('App\Assignment');
    }

    public function variable() {
        return $this->belongsTo('App\Variable');
    }

    public function question() {
        return $this->belongsTo('App\Question');
    }

    public function isColumn()
    {
        return $this->isColumn;
    }
}
