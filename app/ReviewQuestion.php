<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReviewQuestion extends Model
{
    protected $casts = ['choices' => 'array'];

    public function assignment()
    {
        return $this->belongsTo('App\ReviewAssignment');
    }

    public function answers()
    {
        return $this->hasMany('App\ReviewAnswer');
    }
}
