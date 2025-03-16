<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReviewFeedback extends Model
{
    protected $table = 'review_feedbacks';  //As of Laravel 5.8, irregular plurals are fixed.  This line makes the model backwards compatible.

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function assignment() {
        return $this->belongsTo('App\ReviewAssignment','review_assignment_id');
    }

}
