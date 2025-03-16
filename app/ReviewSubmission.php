<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReviewSubmission extends Model
{
    protected $fillable = array('review_assignment_id');

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function assignment() {
        return $this->belongsTo('App\ReviewAssignment','review_assignment_id');
    }

    public function jobs() {
        return $this->hasMany('App\ReviewJob');
    }

}
