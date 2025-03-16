<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class ForumAnswer extends Model
{

    //use \Znck\Eloquent\Traits\BelongsToThrough;

    protected $touches = ['forum'];

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function forum()
    {
        return $this->belongsTo('App\Forum');
    }

    public function votes() {
        return $this->hasMany('App\ForumResponseVote','response_id');
    }

    public function forum_votes() {
        return $this->hasMany('App\ForumResponseVote','response_id');
    }

    /*public function course() {
        return $this->belongsToThrough('App\Course', 'App\Forum');
    }*/

    public function tags() {
        return explode(' | ', $this->tags);
    }

    // @override
    public function delete()
    {
        // Delete everything underneath
        foreach($this->votes() as $f)
        {
            $f->delete();
        }

        //Delete the assignment
        return parent::delete();
    }
}
