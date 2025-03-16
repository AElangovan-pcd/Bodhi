<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    public function user() {
        return $this->belongsTo('App\User');
    }

    public function student() {
        return $this->belongsTo('App\User');
    }

    public function course()
    {
        return $this->belongsTo('App\Course');
    }

    public function poll_answers() {
        return $this->hasMany('App\PollAnswer');
    }

    public function answer() {
        return $this->hasOne('App\PollAnswer')->latest();
    }

    public function choices()
    {
        return explode(' | ', $this->choices);
    }

    // @override
    public function delete()
    {
        // Delete all questions underneath
        foreach($this->poll_answers as $p)
        {
            $p->delete();
        }

        //Delete the assignment
        return parent::delete();
    }
}
