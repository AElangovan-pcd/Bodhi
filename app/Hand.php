<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Hand extends Model
{
    public function user() {
        return $this->belongsTo('App\User');
    }

    public function course()
    {
        return $this->belongsTo('App\Course');
    }

    public function get_seat() {
        return $this->user->courses->firstWhere('id',$this->course->id)->pivot->seat;
    }
}
