<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Result extends Model
{

    //Status codes:
    // 0 - ungraded
    // 1 - graded and available
    // 2 - retry
    // 3 - deferred

    protected $fillable = ['user_id','question_id','attempts','earned','feedback','status','created_at','updated_at'];
    public function user() {
        return $this->belongsTo('App\User');
    }

    public function question() {
        return $this->belongsTo('App\Question');
    }
}
