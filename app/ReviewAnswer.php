<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReviewAnswer extends Model
{

    protected $fillable = ['review_job_id','review_question_id'];

    public function assignment()
    {
        return $this->belongsTo('App\ReviewAssignment');
    }

    public function job()
    {
        return $this->belongsTo('App\ReviewJob');
    }

    public function question()
    {
        return $this->belongsTo('App\ReviewQuestion');
    }
}
