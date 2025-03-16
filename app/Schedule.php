<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $casts = ['details' => 'array'];

    public function course() {
        return $this->belongsTo('App\Course');
    }
}
