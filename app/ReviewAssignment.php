<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ReviewAssignment extends Model
{
    protected $casts = ['options' => 'array','instructions'=> 'array'];

    public function course() {
        return $this->belongsTo('App\Course');
    }

    public function creator() {
        return $this->belongsTo('App\User');
    }

    public function questions() {
        return $this->hasMany('App\ReviewQuestion')->orderBy('order');
    }

    public function submissions() {
        return $this->hasMany('App\ReviewSubmission');
    }

    public function jobs() {
        return $this->hasMany('App\ReviewJob');
    }

    public function schedules() {
        return $this->hasMany('App\ReviewSchedule');
    }

    // @override
    public function delete()
    {
        // Delete all questions underneath
        foreach($this->questions as $x)
        {
            $x->delete();
        }

        foreach($this->submissions as $x)
        {
            $x->delete();
        }

        foreach($this->jobs as $x)
        {
            $x->delete();
        }

        foreach($this->schedules as $x)
        {
            $x->delete();
        }

        Storage::deleteDirectory('review/'.$this->course->id.'/'.$this->id);

        //Delete the assignment
        return parent::delete();
    }
}
