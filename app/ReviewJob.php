<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReviewJob extends Model
{
    public function user() {
        return $this->belongsTo('App\User');
    }

    public function assignment() {
        return $this->belongsTo('App\ReviewAssignment','review_assignment_id');
    }

    public function submission() {
        return $this->belongsTo('App\ReviewSubmission','review_submission_id');
    }

    public function answers()
    {
        return $this->hasMany('App\ReviewAnswer');
    }
}
